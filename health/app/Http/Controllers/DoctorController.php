<?php

namespace App\Http\Controllers;

use App\Models\ConsentRequest;
use App\Models\Doctor;
use App\Models\DoctorPatient;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorController extends Controller
{
    public function patients()
    {
        // Récupérer l'utilisateur authentifié (le médecin)
        $user = Auth::user();

        // Vérifier si l'utilisateur est un médecin
        if ($user->role === 'doctor') {
            // Récupérer le modèle "Doctor" associé à l'utilisateur
            $doctor = Doctor::where('user_id', $user->id)->first();

            if ($doctor) {
                $patients = Patient::all();
                // Charger la vue "patients" avec la liste des patients du médecin
                return view('doctor.doctor_patients', ['doctorPatients' => $doctor, 'patients' => $patients]);
            }
        }

        // Rediriger vers une autre page si l'utilisateur n'est pas un médecin
        return redirect()->back()->with('error', 'You do not have permission to access this page');
    }

    public function removePatient(Request $request)
    {
        $patientId = $request->post('patient_id');
        $doctorId = Auth::user()->doctor->doctor_id;

        $doctor = Doctor::find($doctorId);

        $consentRequest = ConsentRequest::where('doctor_id', $doctorId)
            ->where('patient_id', $patientId)
            ->first();
        if ($doctor) {
            $doctor->patients()->detach($patientId);
            if ($consentRequest) {
                $consentRequest->delete();
            }
            return redirect()->back()->with('success', 'Patient removed successfully!');
        } else {
            return redirect()->back()->with('error', 'Doctor not found.');
        }
    }
}
