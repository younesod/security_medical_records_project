<?php

namespace App\Http\Controllers;

use App\Models\ConsentRequest;
use App\Models\DoctorPatient;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConsentRequestController extends Controller
{
    public function addPatient(Request $request)
    {
        $patientId = $request->post('patient_id');
        $patient = Patient::find($patientId);
        if (!$patient) {
            return redirect()->back()->with('error', 'Patient not found.');
        }
        $doctorId = Auth::user()->doctor->doctor_id;


        $existingRequest = ConsentRequest::where('doctor_id', $doctorId)
            ->where('patient_id', $patientId)
            ->first();

        if ($existingRequest) {
            return redirect()->back()->with('error', 'You already sent a request to this patient or You already have this patient in your list');
        }

        // Créer une nouvelle demande de consentement
        $consentRequest = new ConsentRequest();
        $consentRequest->doctor_id = $doctorId;
        $consentRequest->patient_id = $patientId;
        $consentRequest->status = 'pending';
        $consentRequest->save();

        // Rediriger ou afficher un message de succès
        return redirect()->back()->with('success', 'Your request has been sent and is waiting for confirmation');
    }
    public function showRequestsDoctor()
    {
        $patientId = Auth::user()->patient->patient_id;
        $requests = ConsentRequest::where('patient_id', $patientId)->get();
        return view('patient.consent_requests', ['requests' => $requests])->with('success', 'Request sent');
    }

    public function processConsentRequest(Request $request)
    {
        // Récupérer l'action, l'ID du patient et l'ID du médecin depuis la requête
        $action = $request->input('action');
        $patientId = Auth::user()->patient->patient_id;
        $doctorId = $request->input('doctor_id');
        // Récupérer la demande de consentement correspondante
        $consentRequest = ConsentRequest::where('patient_id', $patientId)
            ->where('doctor_id', $doctorId)
            ->firstOrFail();

        // Traiter l'action soumise dans le formulaire de consentement
        if ($action === 'accepted') {

            // Le patient a accepté la demande de consentement
            $consentRequest->status='accepted';
            $consentRequest->delete();

            $doctorPatient = new DoctorPatient();
            $doctorPatient->doctor_id = $doctorId;
            $doctorPatient->patient_id = $patientId;
            $doctorPatient->save();
            return redirect()->back()->with('success', 'You have accepted the doctor\'s invitation');
        } elseif ($action === 'rejected') {
            // Le patient a refusé la demande de consentement
            $consentRequest->status = 'rejected';
            $consentRequest->delete();

            // Effectuer d'autres actions nécessaires en cas de refus

            return redirect()->back()->with('success', 'You refused the doctor\'s invitation.');
        }

        // Si aucune action valide n'a été soumise, rediriger vers une autre page ou afficher un message d'erreur approprié
        return redirect()->back()->with('error', 'There was an error in the confirmation of your choice');
    }
}
