<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\DoctorPatient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MedicalRecord;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use phpseclib3\Crypt\RSA;


class PatientRecordController extends Controller
{
    /**
     * Valid file extensions.
     *
     * @var array
     */
    private static $validExtensions = ['txt', 'csv', 'pdf', 'jpg', 'jpeg', 'png', 'docx', 'org'];

    /**
     * Display all medical records of the logged-in patient.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function allRecord()
    {
        $patient = Auth::user()->patient;
        $patientId = $patient->user_id;
        $record = MedicalRecord::where('user_id', $patientId)->get();
        return view('patient.recordPatient', ['record' => $record]);
    }
    /**
     * 
     * Create a new medical record file or update an existing file.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function CreateFile(Request $request)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $name = $request->file->getClientOriginalName();
            $extension = $request->file->getClientOriginalExtension();
            if (!in_array($extension, self::$validExtensions)) {
                return redirect()->back()->with('error', 'File\'s extension unauthorized.');
            }

            $patient = Auth::user()->patient;
            $patientId = $patient->user_id;

            $existingRecord = MedicalRecord::where('user_id', $patientId)->where('name', $name)->first();
            if ($existingRecord) {
                // Si un enregistrement existe déjà, nous le mettons à jour avec le nouveau nom de fichier
                $existingRecord->file = $request->file;
                $existingRecord->name = $existingRecord->file->getClientOriginalName();
                $existingRecord->file_ext = $extension;
                //Encrypt the received file with the ciphered symmetric key
                $fileContent = file_get_contents($file->path());
                $encryptedKey = Storage::get('public/medical_records/' . $name . Auth::user()->email . '.key');
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
                $record->file = $request->file;
                $record->name = $record->file->getClientOriginalName();
                $record->user_id = $patientId;


                // Récupérer le contenu du fichier
                $fileContent = file_get_contents($file->path());
                // Générer une clé de chiffrement symétrique
                $encryptionKey = random_bytes(32);
                // Générer un IV aléatoire
                $iv = random_bytes(16);
                // Chiffrer le contenu du fichier avec la clé de chiffrement symétrique et l'IV
                $encryptedContent = openssl_encrypt($fileContent, 'AES-256-CBC', $encryptionKey, OPENSSL_RAW_DATA, $iv);

                $doctorPatient = DoctorPatient::where('patient_id', Auth::user()->patient->patient_id)->get();
                if ($doctorPatient) {
                    foreach ($doctorPatient as $doctor) {
                        $doctorData = Doctor::find($doctor->doctor_id);
                        openssl_public_encrypt($encryptionKey, $encryptedKey, $doctorData->user->public_key);
                        Storage::put('public/medical_records/' . $name . $doctorData->user->email . '.key', $encryptedKey);
                    }
                }
                // Chiffrer la clé de chiffrement symétrique avec la clé publique
                openssl_public_encrypt($encryptionKey, $encryptedKey, Auth::user()->public_key);
                // Stocker le fichier chiffré, l'IV et la clé chiffrée
                Storage::put('public/medical_records/' . $name . '.bin', $encryptedContent);
                Storage::put('public/medical_records/' . $name . '.iv', $iv);
                Storage::put('public/medical_records/' . $name . Auth::user()->email . '.key', $encryptedKey);


                $record->file_path = 'public/medical_records/' . $name . '.bin';

                $record->file_ext = $extension;
                $record->save();
                return redirect()->back()->with('success', 'The file has been uploaded.');
            }
        } else {

            return redirect()->back()->with('error', 'You need to add a file.');
        }
    }

    /**
     * Delete a medical record file.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function DeleteFile(Request $request)
    {
        $fileId = $request->fileId;
        $file = MedicalRecord::find($fileId);
        $files = MedicalRecord::where('user_id', Auth::user()->id)->get();
        $name = $file->name;
        //Supprimer les fichiers bin, iv et key
        $doctorPatient = DoctorPatient::where('patient_id', Auth::user()->patient->patient_id)->get();
        if ($doctorPatient) {
            foreach ($doctorPatient as $doctor) {
                $doctorData = Doctor::find($doctor->doctor_id);
                Storage::delete('public/medical_records/' . $name . $doctorData->user->email . '.key');
            }
        }
        Storage::delete('public/medical_records/' . $name . '.bin');
        Storage::delete('public/medical_records/' . $name . '.iv');
        Storage::delete('public/medical_records/' . $name . Auth::user()->email . '.key');
        DB::table('medical_records')->where('id', '=', $fileId)->delete();
        return redirect()->back()->with('success', 'The file has been deleted.');
    }

    /**
     * Download a medical record file.
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
     */
    public function download($id)
    {
        $medicalRecord = MedicalRecord::findOrFail($id);

        $filePath = $medicalRecord->file_path;
        $name = $medicalRecord->name;

        if ($filePath && Storage::exists($filePath)) {
            $encryptedContent = Storage::get('public/medical_records/' . $name . '.bin');
            $iv = Storage::get('public/medical_records/' . $name . '.iv');
            $encryptedKey = Storage::get('public/medical_records/' . $name . Auth::user()->email . '.key');
            $pathPrivateKey = file_get_contents(Auth::user()->private_key);
            openssl_private_decrypt($encryptedKey, $decryptedKey, $pathPrivateKey);
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
