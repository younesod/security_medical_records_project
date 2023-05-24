<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MedicalRecord;
use Illuminate\Support\Facades\DB;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\DoctorPatient;
use Illuminate\Support\Facades\Storage;

class DoctorRecordController extends Controller
{
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

    public function showRecordDoctor()
    {
        $doctorId = Auth::user()->doctor->doctor_id;
        $doctor = Doctor::find($doctorId);

        $patients = $this->getPatientsWithMedicalRecords($doctorId);

        return view('doctor_show_record', ['patients' => $patients]);
    }

    public function showRecordOfPatient($id)
    {

        $files = DB::table('medical_records')
            ->where('user_id', $id)
            ->get();
        $patient= Patient::where('user_id',$id)->first();

        return view('detail_record', ['files' => $files,'patient'=>$patient]);
    }

    public function addRecordOfPatient(Request $request)
    {
        if ($request->hasFile('file')) {
            $request->filled('file');
            $file = $request->file('file');
            $name = $request->file->getClientOriginalName();
            $extension = $request->file->getClientOriginalExtension();
            $user_id = $request->id;
            $existingRecord = MedicalRecord::where('user_id', $user_id)->where('name', $name)->first();


            if ($existingRecord) {
                // Si un enregistrement existe déjà, nous le mettons à jour avec le nouveau nom de fichier
                $existingRecord->name = $name;
                $existingRecord->file = $request->file;
                //Encrypt the received file with the ciphered symmetric key
                $fileContent = file_get_contents($file->path());
                $encryptedKey = Storage::get('public/medical_records/' . $name . '.key');
                $iv = Storage::get('public/medical_records/' . $name . '.iv');
                $pathPrivateKey = file_get_contents(Auth::user()->private_key);
                openssl_private_decrypt($encryptedKey, $decryptedKey, $pathPrivateKey);
                $encryptedContent = openssl_encrypt($fileContent, 'AES-256-CBC', $decryptedKey, OPENSSL_RAW_DATA, $iv);
                Storage::put('public/medical_records/' . $name . '.bin', $encryptedContent);
                $existingRecord->save();
                return redirect()->back()->with('success', 'The file has been modified.');
            } else {
                // Si aucun enregistrement n'existe, nous en créons un nouveau
                $record = new MedicalRecord();
                $record->name = $name;
                $record->user_id = $user_id;
                $record->file = $request->file;
                // $record->file_path = $request->file->storeas('public/medical_records', $record->name);

                // Récupérer le contenu du fichier
                $fileContent = file_get_contents($file->path());
                // Générer une clé de chiffrement symétrique
                $encryptionKey = random_bytes(32);
                // Générer un IV aléatoire
                $iv = random_bytes(16);
                // Chiffrer le contenu du fichier avec la clé de chiffrement symétrique et l'IV
                $encryptedContent = openssl_encrypt($fileContent, 'AES-256-CBC', $encryptionKey, OPENSSL_RAW_DATA, $iv);
                $patient = Patient::where('user_id', $user_id)->first();
                $doctorPatient = DoctorPatient::where('patient_id', $patient->patient_id)->get();
                if ($doctorPatient) {
                    foreach ($doctorPatient as $doctor) {
                        $doctorData = Doctor::find($doctor->doctor_id);
                        openssl_public_encrypt($encryptionKey, $encryptedKey, $doctorData->user->public_key);
                        Storage::put('public/medical_records/' . $name . $doctorData->user->email . '.key', $encryptedKey);
                    }
                }
                //The same thing but for the patient
                $patient = Patient::where('user_id', $user_id)->first();
                openssl_public_encrypt($encryptionKey, $encryptedKey, $patient->user->public_key);
                Storage::put('public/medical_records/' . $name . '.bin', $encryptedContent);
                Storage::put('public/medical_records/' . $name . '.iv', $iv);
                Storage::put('public/medical_records/' . $name . '.key', $encryptedKey);
                $record->file_path = 'public/medical_records/' . $name . '.bin';
                $record->file_ext = $extension;
                $record->save();
                return redirect()->back()->with('success', 'The file has been uploaded.');
            }
        } else {
            return redirect()->back()->with('error', 'You need to add a file.');
        }
    }
    public function deleteRecordOfPatient(Request $request)
    {
        $fileId = $request->fileId;
        $file = MedicalRecord::find($fileId);
        $name = $file->name;
        $patientId = $request->patientId;
        $doctorPatient = DoctorPatient::where('patient_id', $patientId)->get();
        if ($doctorPatient) {
            foreach ($doctorPatient as $doctor) {
                $doctorData = Doctor::find($doctor->doctor_id);
                Storage::delete('public/medical_records/' . $name . $doctorData->user->email . '.key');
            }
        }
        Storage::delete('public/medical_records/' . $name . '.bin');
        Storage::delete('public/medical_records/' . $name . '.iv');
        Storage::delete('public/medical_records/' . $name . '.key');
        DB::table('medical_records')->where('id', '=', $fileId)->delete();
        return redirect()->back()->with('success', 'The file has been deleted.');
    }

    public function download($id)
    {
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
