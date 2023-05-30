<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ConsentRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MedicalRecord;
use Illuminate\Support\Facades\DB;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\DoctorPatient;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Crypt;
use Redirect;

class DoctorRecordController extends Controller
{
    /**
     * Valid file extensions.
     *
     * @var array
     */
    private static $validExtensions = ['txt', 'csv', 'pdf', 'jpg', 'jpeg', 'png', 'docx', 'org'];

    /**
     * Get the patients with medical records associated with a doctor.
     *
     * @param  int  $doctorId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPatientsWithMedicalRecords($doctorId)
    {
        $patients =  Patient::leftJoin('doctor_patient', 'patients.patient_id', '=', 'doctor_patient.patient_id')
            ->join('doctors', 'doctor_patient.doctor_id', '=', 'doctors.doctor_id')
            ->join('users', 'patients.user_id', '=', 'users.id')
            ->where('doctors.doctor_id', $doctorId)
            ->select('patients.patient_id', 'users.name', 'patients.user_id')
            ->get();
        return $patients;
    }

    /**
     * Display the medical records of the doctor's patients.
     *
     * @return \Illuminate\View\View
     */
    public function showRecordDoctor()
    {
        $doctorId = Auth::user()->doctor->doctor_id;
        $doctor = Doctor::find($doctorId);

        $patients = $this->getPatientsWithMedicalRecords($doctorId);

        return view('doctor.doctor_show_record', ['patients' => $patients]);
    }


    /**
     * Display the medical records of a specific patient.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function showRecordOfPatient(Request $request)
    {

        $id = $request->patient_id;
        $files = DB::table('medical_records')
            ->where('user_id', $id)
            ->get();
        $patient = Patient::where('user_id', $id)->first();
        session(['patient_id' => $id,]);
        return view('detail_record', ['files' => $files, 'patient' => $patient]);
    }


    public function addRequestRecordOfPatient(Request $request)
    {
        $userId = $request->id;
        $patient = Patient::where('user_id', $userId)->first();
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $name = $request->file->getClientOriginalName();
            $extension = $request->file->getClientOriginalExtension();
            if (!in_array($extension, self::$validExtensions)) {
                return redirect()->back()->with('error', 'File\'s extension unauthorized.');
            }
            $doctor = Auth::user()->doctor;
            $existingRequest = ConsentRequest::where('doctor_id', $doctor->doctor_id)
                ->where('patient_id', $patient->patient_id)
                ->first();

            if ($existingRequest) {
                return redirect()->route('doctor.dossierFile', ['patient_id' => $userId])->with('error', 'You already sent a file request to this patient.');
            }

            // Créer une nouvelle demande de consentement
            $consentRequest = new ConsentRequest();
            $consentRequest->doctor_id = $doctor->doctor_id;
            $consentRequest->patient_id = $patient->patient_id;
            $consentRequest->status = 'pending';
            $consentRequest->file = $file;
            $consentRequest->file_ext = $extension;
            $consentRequest->file_name = $name;
            $consentRequest->file_path = 'public/tmp/' . $name . '.bin';
            $consentRequest->save();

            $fileContent = file_get_contents($file->path());
            // Générer une clé de chiffrement symétrique
            $encryptionKey = random_bytes(32);
            // Générer un IV aléatoire
            $iv = random_bytes(16);
            // Chiffrer le contenu du fichier avec la clé de chiffrement symétrique et l'IV
            $encryptedContent = openssl_encrypt($fileContent, 'AES-256-CBC', $encryptionKey, OPENSSL_RAW_DATA, $iv);
            openssl_public_encrypt($encryptionKey, $encryptedKey, $doctor->user->public_key);
            Storage::put('public/tmp/' . $name . '.bin', $encryptedContent);
            Storage::put('public/tmp/' . $name . '.iv', $iv);
            Storage::put('public/tmp/' . $name . $doctor->user->email . '.key', $encryptedKey);

            // Rediriger ou afficher un message de succès
            return redirect()->route('doctor.dossierFile', ['patient_id' => $userId])->with('success', 'The request to add a file has been sent.');
        }
    }
    // /**
    //  * Add a medical record for a patient.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @return \Illuminate\Http\RedirectResponse
    //  */
    // public function addRecordOfPatient(Request $request)
    // {
    //     $userId = $request->id;
    //     if ($request->hasFile('file')) {
    //         // $request->filled('file');
    //         $file = $request->file('file');
    //         $name = $request->file->getClientOriginalName();
    //         $extension = $request->file->getClientOriginalExtension();
    //         if (!in_array($extension, self::$validExtensions)) {
    //             return redirect()->back()->with('error', 'File\'s extension unauthorized.');
    //         }

    //         $existingRecord = MedicalRecord::where('user_id', $userId)->where('name', $name)->first();
    //         $userPatient = User::where('id', $userId)->first();
    //         if ($existingRecord) {
    //             // Si un enregistrement existe déjà, nous le mettons à jour avec le nouveau nom de fichier
    //             $existingRecord->name = $name;
    //             $existingRecord->file = $request->file;
    //             //Encrypt the received file with the ciphered symmetric key
    //             $fileContent = file_get_contents($file->path());
    //             $encryptedKey = Storage::get('public/medical_records/' . $name . $userPatient->email . '.key');
    //             $iv = Storage::get('public/medical_records/' . $name . '.iv');
    //             $pathPrivateKey = file_get_contents(Auth::user()->private_key);
    //             openssl_private_decrypt($encryptedKey, $decryptedKey, $pathPrivateKey);
    //             $encryptedContent = openssl_encrypt($fileContent, 'AES-256-CBC', $decryptedKey, OPENSSL_RAW_DATA, $iv);
    //             Storage::put('public/medical_records/' . $name . '.bin', $encryptedContent);
    //             $existingRecord->save();
    //             return redirect()->route('doctor.dossierFile')->with(['patient_id' => $userId])->with('success', 'The file has been modified.');
    //         } else {
    //             // Si aucun enregistrement n'existe, nous en créons un nouveau
    //             $record = new MedicalRecord();
    //             $record->name = $name;
    //             $record->user_id = $userId;
    //             $record->file = $request->file;
    //             // $record->file_path = $request->file->storeas('public/medical_records', $record->name);
    //             // Récupérer le contenu du fichier
    //             $fileContent = file_get_contents($file->path());
    //             // Générer une clé de chiffrement symétrique
    //             $encryptionKey = random_bytes(32);
    //             // Générer un IV aléatoire
    //             $iv = random_bytes(16);
    //             // Chiffrer le contenu du fichier avec la clé de chiffrement symétrique et l'IV
    //             $encryptedContent = openssl_encrypt($fileContent, 'AES-256-CBC', $encryptionKey, OPENSSL_RAW_DATA, $iv);
    //             $user_id = session('patient_id');
    //             $patient = Patient::where('user_id', $user_id)->first();
    //             $doctorPatient = DoctorPatient::where('patient_id', $patient->patient_id)->get();
    //             if ($doctorPatient) {
    //                 foreach ($doctorPatient as $doctor) {
    //                     $doctorData = Doctor::find($doctor->doctor_id);
    //                     openssl_public_encrypt($encryptionKey, $encryptedKey, $doctorData->user->public_key);
    //                     Storage::put('public/medical_records/' . $name . $doctorData->user->email . '.key', $encryptedKey);
    //                 }
    //             }
    //             //The same thing but for the patient
    //             openssl_public_encrypt($encryptionKey, $encryptedKey, $userPatient->public_key);
    //             Storage::put('public/medical_records/' . $name . '.bin', $encryptedContent);
    //             Storage::put('public/medical_records/' . $name . '.iv', $iv);
    //             Storage::put('public/medical_records/' . $name . $userPatient->email . '.key', $encryptedKey);
    //             $record->file_path = 'public/medical_records/' . $name . '.bin';
    //             $record->file_ext = $extension;
    //             $record->save();


    //             //  return redirect()->route('doctor.dossierFile')->with('success', 'The file has been uploaded.');
    //             return redirect()->route('doctor.dossierFile', ['patient_id' => $userId])->with('success', 'The file has been uploaded.');
    //         }
    //     } else {
    //         // return redirect()->route('doctor.dossierFile')->with('error', 'You need to add a file.');
    //         return redirect()->route('doctor.dossierFile', ['patient_id' => $userId])->with('error', 'You need to add a file.');
    //     }
    // }

    // /**
    //  * Delete a medical record of a patient.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @return \Illuminate\Http\RedirectResponse
    //  */
    // public function deleteRecordOfPatient(Request $request)
    // {
    //     $fileId = $request->fileId;
    //     $file = MedicalRecord::findOrFail($fileId);
    //     $name = $file->name;
    //     $patientId = $request->patientId;
    //     $patient = Patient::where('user_id', $patientId)->first();
    //     $doctorPatient = DoctorPatient::where('patient_id', $patient->patient_id)->get();
    //     if ($doctorPatient) {
    //         foreach ($doctorPatient as $doctor) {
    //             $doctorData = Doctor::find($doctor->doctor_id);
    //             Storage::delete('public/medical_records/' . $name . $doctorData->user->email . '.key');
    //         }
    //     }
    //     Storage::delete('public/medical_records/' . $name . '.bin');
    //     Storage::delete('public/medical_records/' . $name . '.iv');
    //     Storage::delete('public/medical_records/' . $name . $patient->user->email . '.key');
    //     DB::table('medical_records')->where('id', '=', $fileId)->delete();
    //     return redirect()->route('doctor.dossierFile', ['patient_id' => $patientId])->with('success', 'The file has been uploaded.');
    // }

    public function addRequestDeleteFileOfPatient(Request $request)
    {
        $fileId = $request->fileId;
        $patientId = $request->patientId;
        $patient = Patient::where('user_id', $patientId)->first();
        $doctor = Auth::user()->doctor;
        $consentRequest = new ConsentRequest();
        $consentRequest->doctor_id = $doctor->doctor_id;
        $consentRequest->patient_id = $patient->patient_id;
        $consentRequest->status = 'pending';
        $consentRequest->file_delete=$fileId;
        $consentRequest->save();

        return redirect()->route('doctor.dossierFile', ['patient_id' => $patientId])->with('success', 'The request for delete has been sent.');
    }

    /**
     * Download a medical record file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
     */
    public function download(Request $request)
    {
        $id = $request->fileId;
        $medicalRecord = MedicalRecord::findOrFail($id);

        $filePath = $medicalRecord->file_path;
        $name = $medicalRecord->name;
        $user = Auth::user();
        if ($filePath && Storage::exists($filePath)) {
            $encryptedContent = Storage::get('public/medical_records/' . $name . '.bin');
            $iv = Storage::get('public/medical_records/' . $name . '.iv');
            $encryptedKey = Storage::get('public/medical_records/' . $name . $user->email . '.key');
            $filePrivateKey = file_get_contents($user->private_key);
            openssl_private_decrypt($encryptedKey, $decryptedKey, $filePrivateKey);
            $decryptedContent = openssl_decrypt($encryptedContent, 'AES-256-CBC', $decryptedKey, OPENSSL_RAW_DATA, $iv);

            // Créer un fichier temporaire avec le contenu décrypté
            $tempFilePath = sys_get_temp_dir() . '/' . $name;
            file_put_contents($tempFilePath, $decryptedContent);

            // Télécharger le fichier dans son format d'origine
            return response()->download($tempFilePath)->deleteFileAfterSend(true);
        }

        return redirect()->back()->with('error', 'File not found.');
    }
}
