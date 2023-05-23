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
            ->select('patients.patient_id', 'users.name','patients.user_id')
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

    public function showRecordOfPatient($id){
            
            $files = DB::table('medical_records')
            ->where('user_id', $id)
            ->get();

        return view('detail_record', ['files' => $files]);
        }
        
    public function addRecordOfPatient(Request $request)
    {

        $request->filled('file');
            
            $name = $request->file->getClientOriginalName();
            $patientId = $request->id;
            $existingRecord = MedicalRecord::where('user_id', $patientId )->where('name',$name)->first();

    
            if ($existingRecord) {
                // Si un enregistrement existe déjà, nous le mettons à jour avec le nouveau nom de fichier
                $existingRecord->name = $name;
                $existingRecord->file = $request->file;
                $existingRecord->save();
                return redirect()->back()->with('success', 'Le fichier a bien été modifié.');
            } else {
                // Si aucun enregistrement n'existe, nous en créons un nouveau
                $record = new MedicalRecord();
                $record->name = $name;
                $record->user_id = $patientId;
                $record->file = $request->file;
                $record->file_path = $request->file->storeas('public/medical_records', $record->name);
                $record->save();
                return redirect()->back()->with('success', 'Le fichier a bien été ajouté.');
            }
        
         return redirect()->back()->with('error', 'Veuillez renseigner un nom de fichier.');
        
    }
    public function deleteRecordOfPatient(Request $request){
        $fileId = $request->fileId;
         DB::table('medical_records')->where('id', '=', $fileId)->delete();
         return redirect()->back()->with('success', 'Le fichier a bien été supprimé.');

    }
    // public function download($id){
    //     $medicalRecord = MedicalRecord::findOrFail($id);
    
    //     $filePath = $medicalRecord->file_path;
    
    //     if ($filePath && Storage::exists($filePath)) {
    //         return Storage::download($filePath);
    //     }
    
    //     return redirect()->back()->with('error', 'Fichier non trouvé');
    // }

    public function download($id)
    {
        $medicalRecord = MedicalRecord::findOrFail($id);

        $filePath = $medicalRecord->file_path;
        $name = $medicalRecord->name;
        $user=Auth::user();
        if ($filePath && Storage::exists($filePath)) {
            $encryptedContent = Storage::get('public/medical_records/' . $name . '.bin');
            $iv = Storage::get('public/medical_records/' . $name . '.iv');
            $encryptedKey = Storage::get('public/medical_records/' . $name .$user->email. '.key');
            openssl_private_decrypt($encryptedKey, $decryptedKey, $user->private_key);
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



