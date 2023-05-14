<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use DebugBar\DebugBar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        //Récuperer tous les docteurs
        // $doctors = Doctor::all();
        $doctor=Doctor::where('doctor_id',$request->post('doctor_id'));


        // Récupérer l'ID du docteur sélectionné
        $doctorId = (int)$request->post('doctor_id');
        $unsignedDoctorId = abs($doctorId);
        if($doctorId!=null){
            // Récupérer le patient courant
            $patient = Auth::user()->patient;
            // Mettre à jour le docteur du patient
            $patient->doctor_id = $unsignedDoctorId;
            $patient->save();
            //////ERROR DOES NOT ADD IN THE DATABASE
        }

        return redirect()->back()->with('success', 'Doctor added for the patient successfully!');
    }
}
