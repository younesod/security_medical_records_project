<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
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
                // Charger la vue "patients" avec la liste des patients du médecin
                return view('doctor_patients', ['doctorPatients'=>$doctor]);
            }
        }

        // Rediriger vers une autre page si l'utilisateur n'est pas un médecin
        return redirect()->back()->with('error', 'You do not have permission to access this page');
    }
}
