<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use DebugBar\DebugBar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Patient;
use Illuminate\Support\Facades\DB;

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
        return view('patients_show_doctors', ['doctors' => $doctors]);
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
        return redirect()->back()->with('error', 'Ce docteur est déjà associé à ce patient.');
    }

            // Récupérer le modèle "Doctor"
            $realDoctor = Doctor::where('doctor_id', $doctorId)->first();

            if ($realDoctor) {
                // Attacher le patient et le docteur
                $realDoctor->patients()->attach($patientId, ['doctor_id' => $doctorId]);
                $patient->save();
            }
        }
    }

    return redirect()->back()->with('success', 'Doctor added for the patient successfully!');
}
}
