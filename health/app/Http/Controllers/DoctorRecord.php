<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\medicalRecord;
use Illuminate\Support\Facades\DB;
use App\Models\Patient; 
use App\Models\Doctor;
class DoctorRecord extends Controller
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
        if($request->filled('fileName')){
            $name = $request->fileName;
            $patientId = $request->id;

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
    public function deleteRecordOfPatient(Request $request){
        $fileId = $request->fileId;
         DB::table('medical_records')->where('id', '=', $fileId)->delete();
         return redirect()->back()->with('success', 'Le fichier a bien été supprimé.');

    }
    }



