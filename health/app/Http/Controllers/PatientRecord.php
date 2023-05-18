<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\medicalRecord;
use Illuminate\Support\Facades\DB;

class PatientRecord extends Controller
{
    public function allRecord(){
        $patient = Auth::user()->patient;
        $patientId = $patient->user_id;
        $record = medicalRecord::where('user_id', $patientId)->get();
        return view('recordPatient', ['record' => $record]);
        
    }
    public function CreateFile(Request $request){
        if($request->filled('fileName')){
            $name = $request->fileName;

            $patient = Auth::user()->patient;
            $patientId = $patient->user_id;
           

            $existingRecord = medicalRecord::where('user_id', $patientId )->where('name',$name)->first();
    
            if ($existingRecord) {
                // Si un enregistrement existe déjà, nous le mettons à jour avec le nouveau nom de fichier
                $existingRecord->name = $request->fileName;
                $existingRecord->save();
                return redirect()->back()->with('success', 'Le fichier a bien été modifié.');
            } else {
                // Si aucun enregistrement n'existe, nous en créons un nouveau
                $record = new medicalRecord();
                $record->name = $request->fileName;
                $record->user_id = $patientId;
                $record->save();
                return redirect()->back()->with('success', 'Le fichier a bien été ajouté.');
            }
        }else{
         return redirect()->back()->with('error', 'Veuillez renseigner un nom de fichier.');
        }
        
           
    }
    public function DeleteFile(Request $request){
       $fileId = $request->fileId;
        DB::table('medical_records')->where('id', '=', $fileId)->delete();
        return redirect()->back()->with('success', 'Le fichier a bien été supprimé.');
    }
}
