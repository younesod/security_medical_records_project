<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
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
    private static $validExtensions = ['txt', 'csv', 'pdf', 'jpg', 'jpeg', 'png', 'docx', 'org'];
    public function allRecord()
    {
        $patient = Auth::user()->patient;
        $patientId = $patient->user_id;
        $record = MedicalRecord::where('user_id', $patientId)->get();
        return view('recordPatient', ['record' => $record]);
    }
    public function CreateFile(Request $request)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $name = $request->file->getClientOriginalName();
            $extension = $request->file->getClientOriginalExtension();
            if (!in_array($extension, PatientRecordController::$validExtensions)) {
                return redirect()->back()->with('error', 'Extension de fichier non autorisée.');
            }

            $patient = Auth::user()->patient;
            $patientId = $patient->user_id;

            $existingRecord = MedicalRecord::where('user_id', $patientId)->where('name', $name)->first();

            if ($existingRecord) {
                // Si un enregistrement existe déjà, nous le mettons à jour avec le nouveau nom de fichier
                $existingRecord->file = $request->file;
                $existingRecord->name = $existingRecord->file->getClientOriginalName();
                $existingRecord->file_ext = $extension;
                $existingRecord->save();
                return redirect()->back()->with('success', 'Le fichier a bien été modifié.');
            } else {
                // Si aucun enregistrement n'existe, nous en créons un nouveau

                $record = new MedicalRecord();
                $record->file = $request->file;
                $record->name = $record->file->getClientOriginalName();
                $record->user_id = $patientId;

                $filePath = 'public/medical_records/' . $name;
                // $encryptedContent = Crypt::encrypt($file->getContent());
                // Storage::put($filePath, $encryptedContent);


                // Récupérer le contenu du fichier
                $fileContent = file_get_contents($file->path());
                // Générer une clé de chiffrement symétrique
                $encryptionKey = random_bytes(32);
                // Générer un IV aléatoire
                $iv = random_bytes(16);
                // Chiffrer le contenu du fichier avec la clé de chiffrement symétrique et l'IV
                $encryptedContent = openssl_encrypt($fileContent, 'AES-256-CBC', $encryptionKey, OPENSSL_RAW_DATA, $iv);

                // Chiffrer la clé de chiffrement symétrique avec la clé publique
                //créer des chiffrments symétrique avec autant de ligne qu'il y a dans doctor patient associé au patient
                $another_user=User::find(3);
                openssl_public_encrypt($encryptionKey, $encryptedKey1, Auth::user()->public_key);
                openssl_public_encrypt($encryptionKey, $encryptedKey2, $another_user->public_key);

                // Stocker le fichier chiffré, l'IV et la clé chiffrée
                Storage::put('public/medical_records/' . $name . '.bin', $encryptedContent);
                Storage::put('public/medical_records/' . $name . '.iv', $iv);
                Storage::put('public/medical_records/' . $name . '.key', $encryptedKey1);
                Storage::put('public/medical_records/' . $name .'another'.'.key', $encryptedKey2);

                // $record->file_path = $request->file->storeAs('public/medical_records', $record->name);
                // $fileContent= Storage::get($record->file_path);
                // $rsa =RSA::loadPrivateKey(Auth::user()->private_key);
                // $ciphertext=$rsa->getPublicKey()->encrypt($fileContent);
                // Storage::put($record->file_path, $ciphertext);
                $record->file_path = 'public/medical_records/' . $name . '.bin';
                // $record->file_path = $filePath;
                $record->file_ext = $extension;
                $record->save();
                return redirect()->back()->with('success', 'Le fichier a bien été ajouté.');
            }
        } else {

            return redirect()->back()->with('error', 'Veuillez renseigner un nom de fichier.');
        }
    }


    public function DeleteFile(Request $request)
    {
        $fileId = $request->fileId;
        $file =MedicalRecord::find($fileId);
        $name=$file->name;
        //Supprimer les fichiers bin, iv et key
        Storage::delete('public/medical_records/' . $name . '.bin');
        Storage::delete('public/medical_records/' . $name . '.iv');
        Storage::delete('public/medical_records/' . $name . '.key');
        DB::table('medical_records')->where('id', '=', $fileId)->delete();
        return redirect()->back()->with('success', 'Le fichier a bien été supprimé.');
    }
    public function download($id)
    {
        $medicalRecord = MedicalRecord::findOrFail($id);

        $filePath = $medicalRecord->file_path;
        $name = $medicalRecord->name;
        $another_user=User::find(3);

        if ($filePath && Storage::exists($filePath)) {
            $encryptedContent = Storage::get('public/medical_records/' . $name . '.bin');
            $iv = Storage::get('public/medical_records/' . $name . '.iv');
            $encryptedKey = Storage::get('public/medical_records/' . $name . '.key');
            // $encryptedKey2=Storage::get('public/medical_records/' . $name .'another'.'.key');
            openssl_private_decrypt($encryptedKey, $decryptedKey, Auth::user()->private_key);
            $decryptedContent = openssl_decrypt($encryptedContent, 'AES-256-CBC', $decryptedKey, OPENSSL_RAW_DATA, $iv);


            // Créer un fichier temporaire avec le contenu décrypté
            $tempFilePath = sys_get_temp_dir() . '/' . $name;
            file_put_contents($tempFilePath, $decryptedContent);

            // Télécharger le fichier dans son format d'origine
            return response()->download($tempFilePath)->deleteFileAfterSend(true);
        }

        return redirect()->back()->with('error', 'Fichier non trouvé');
    }
}
