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
        $doctors = Doctor::all();
        return view('patient.patients_show_doctors', ['doctors' => $doctors]);
    }

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
        // Récupérer l'ID du docteur sélectionné
        $doctorId = $request->post('doctor_id');

        if ($doctorId != null) {
            // Récupérer le patient courant
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

                // Récupérer le modèle "Doctor"
                $realDoctor = Doctor::find($doctorId);

                if ($realDoctor) {
                    // Attacher le patient et le docteur
                    $realDoctor->patients()->attach($patientId, ['doctor_id' => $doctorId]);
                    $patient->save();

                    //recuperer la clé de chiffrement symmétrique -> decrypter -> crypter avec la clé public du médecin associé
                    //faire pour tout les fichiers
                    $file=MedicalRecord::where('user_id',Auth::user()->id)->first();
                    $encryptedKey = Storage::get('public/medical_records/' . $file->name . '.key');
                    $decryptedKey='';
                    openssl_private_decrypt($encryptedKey, $decryptedKey, Auth::user()->private_key);
                    openssl_public_encrypt($decryptedKey, $encryptedKeyDoctor, $realDoctor->user->public_key);
                    Storage::put('public/medical_records/' . $file->name.$realDoctor->user->email . '.key', $encryptedKeyDoctor);
                }
            }
        }

        return redirect()->back()->with('success', 'Doctor added to patient successfully!');
    }
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
            return redirect()->back()->with('success', 'Doctor removed from patient successfully!');
        } else {
            return redirect()->back()->with('error', 'Doctor-patient relation not found.');
        }
    }
    public function showPatient(){
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
    public function addPatient(Request $request){
    
        // Récupérer l'ID du patient sélectionné
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
            return redirect()->back()->with('error', 'vous avez deja ajouter ce patient.');
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
    
    public function deletePatient(Request $request){
        $id = $request->post('patient_id');
        $doctorId = Auth::user()->doctor->doctor_id;
        
    
        DB::table('doctor_patient')
            ->where('doctor_id', $doctorId)
            ->where('patient_id', $id)
            ->delete();
    
        return redirect()->back()->with('success', 'Patient removed successfully from your list.');
    
    }
}
