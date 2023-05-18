<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\medicalRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PatientRecord extends Controller
{
    public function allRecord(){
        $patient = Auth::user()->patient;
        $patientId = $patient->user_id;
        $record = medicalRecord::where('user_id', $patientId)->get();
        return view('recordPatient', ['record' => $record]);
        
    }
    public function CreateFile(Request $request){
       
        if($request->hasFile('file')){
       
            $name = $request->file->getClientOriginalName();
           

            $patient = Auth::user()->patient;
            $patientId = $patient->user_id;
        

            $existingRecord = medicalRecord::where('user_id', $patientId )->where('name',$name)->first();
    
            if ($existingRecord) {
                // Si un enregistrement existe déjà, nous le mettons à jour avec le nouveau nom de fichier
                $existingRecord->file = $request->file;
                $existingRecord->name = $existingRecord->file->getClientOriginalName();
                
                $existingRecord->save();
                return redirect()->back()->with('success', 'Le fichier a bien été modifié.');
            } else {
                // Si aucun enregistrement n'existe, nous en créons un nouveau
                
                $record = new medicalRecord();
                $record->file = $request->file;
                $record->name = $record->file->getClientOriginalName();
               
                $record->user_id = $patientId;
                $record->file_path = $request->file->storeas('public/medical_records', $record->name);
                $record->save();
                return redirect()->back()->with('success', 'Le fichier a bien été ajouté.');
            }
        } else {
        
         return redirect()->back()->with('error', 'Veuillez renseigner un nom de fichier.');
        }
    }
           
    
    public function DeleteFile(Request $request){
       $fileId = $request->fileId;
        DB::table('medical_records')->where('id', '=', $fileId)->delete();
        return redirect()->back()->with('success', 'Le fichier a bien été supprimé.');
    }
    public function download($id){
    $medicalRecord = MedicalRecord::findOrFail($id);

    $filePath = $medicalRecord->file_path;

    if ($filePath && Storage::exists($filePath)) {
        return Storage::download($filePath);
    }

    return redirect()->back()->with('error', 'Fichier non trouvé');
}
}
