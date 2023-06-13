<?php

namespace App\Http\Controllers;

use App\Models\ConsentRequest;
use App\Models\Doctor;
use App\Models\DoctorPatient;
use App\Models\MedicalRecord;
use DebugBar\DebugBar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Patient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PatientDoctorController extends Controller
{
    /**
     * Display a list of all doctors.
     *
     * @return \Illuminate\View\View
     */
    public function allDoctors()
    {
        // Récupérer le patient courant
        $patient = Auth::user()->patient;
        // Récupérer les docteurs associés au patient
        $doctorsPatient = $patient->doctors;
        $doctors = Doctor::all();
        return view('patient.patients_show_doctors', ['doctors' => $doctors, 'doctorsPatient' => $doctorsPatient]);
    }

    /**
     * Display the doctors associated with the patient.
     *
     * @return \Illuminate\View\View
     */
    public function doctorsPatient()
    {
        // Récupérer le patient courant
        $patient = Auth::user()->patient;

        // Récupérer les docteurs associés au patient
        $doctors = $patient->doctors;

        return view('patient.patient_doctors', ['doctorsPatient' => $doctors]);
    }


    /**
     * Assign a doctor to the current patient.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addDoctor(Request $request)
    {
        $doctorId = $request->post('doctor_id');

        if ($doctorId != null) {
            // Retrieve current patient
            if (Auth::check()) {
                $patient = Auth::user()->patient;
                $patientId = $patient->patient_id;

                $existingLink = DB::table('doctor_patient')
                    ->where('doctor_id', $doctorId)
                    ->where('patient_id', $patientId)
                    ->first();

                if ($existingLink) {
                    return redirect()->back()->with('error', 'This doctor is already associate with you.');
                }

                $realDoctor = Doctor::find($doctorId);
                if ($realDoctor) {
                    // Attaching patient and doctor
                    $realDoctor->patients()->attach($patientId, ['doctor_id' => $doctorId]);
                    $patient->save();

                    $files = MedicalRecord::where('user_id', Auth::user()->id)->get();
                    foreach ($files as $file) {
                        $encryptedKey = Storage::get('public/medical_records/' . $file->name . '.key');
                        $decryptedKey = '';
                        $filePrivateKey = file_get_contents(Auth::user()->private_key);
                        openssl_private_decrypt($encryptedKey, $decryptedKey, $filePrivateKey);
                        openssl_public_encrypt($decryptedKey, $encryptedKeyDoctor, $realDoctor->user->public_key);
                        Storage::put('public/medical_records/' . $file->name . $realDoctor->user->email . '.key', $encryptedKeyDoctor);
                    }
                }
            }
        }

        return redirect()->back()->with('success', 'Doctor added to patient successfully!');
    }

    /**
     * Remove a doctor from the current patient.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeDoctor(Request $request)
    {
        $doctorId = $request->post('doctor_id');
        $patientId = Auth::user()->patient->patient_id;

        $doctorPatient = DoctorPatient::where('doctor_id', $doctorId)
            ->where('patient_id', $patientId)
            ->first();
        $consentRequest = ConsentRequest::where('doctor_id', $doctorId)
            ->where('patient_id', $patientId)
            ->first();
        if ($doctorPatient) {
            DoctorPatient::where('doctor_id', $doctorId)
                ->where('patient_id', $patientId)
                ->delete();
            if ($consentRequest) {
                $consentRequest->delete();
            }
            $files = MedicalRecord::where('user_id', Auth::user()->id)->get();
            $doctor = Doctor::find($doctorId);
            foreach ($files as $file) {
                Storage::delete('public/medical_records/' . $file->name . $doctor->user->email . '.key');
            }
            return redirect()->back()->with('success', 'Doctor removed from patient successfully!');
        } else {
            return redirect()->back()->with('error', 'Doctor-patient relation not found.');
        }
    }

    /**
     * Display the patient's information.
     *
     * @return \Illuminate\View\View
     */
    public function showPatient()
    {
        $idDoctor = Auth::user()->doctor->doctor_id;
        $patients = DB::table('doctor_patient')
            ->join('patients', 'doctor_patient.patient_id', '=', 'patients.patient_id')
            ->join('users', 'patients.user_id', '=', 'users.id')
            ->where('doctor_patient.doctor_id', '=', $idDoctor)
            ->select('users.name', 'patients.patient_id')
            ->get();
        $AllPatients = Patient::all();

        return view('show_patient', ['patients' => $patients, 'AllPatients' => $AllPatients]);
    }

    /**
     * Add a patient to the current doctor's list.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addPatient(Request $request)
    {
        $patientId = $request->post('patient_id');


        if ($patientId != null) {
            if (Auth::check()) {
                $doctor = Auth::user()->doctor;
                $doctorId = $doctor->doctor_id;

                $existingLink = DB::table('doctor_patient')
                    ->where('doctor_id', $doctorId)
                    ->where('patient_id', $patientId)
                    ->first();


                if ($existingLink) {
                    return redirect()->back()->with('error', 'You already added this patient.');
                }

                $realPatient = Patient::where('patient_id', $patientId)->first();
                if ($realPatient) {
                    $doctor->patients()->attach($patientId);
                    $realPatient->save();
                }
            }
        }
        return redirect()->back()->with('success', ' added the patient successfully!');
    }

    /**
     * Remove a patient from the current doctor's list.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deletePatient(Request $request)
    {
        $id = $request->post('patient_id');
        $doctorId = Auth::user()->doctor->doctor_id;


        DB::table('doctor_patient')
            ->where('doctor_id', $doctorId)
            ->where('patient_id', $id)
            ->delete();

        return redirect()->back()->with('success', 'Patient removed successfully from your list.');
    }
}
