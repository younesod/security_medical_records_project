<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\DoctorPatient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MedicalRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class PatientRecordController extends Controller
{
    /**
     * Valid file extensions.
     *
     * @var array
     */
    private static $validExtensions = ['txt', 'csv', 'pdf', 'jpg', 'jpeg', 'png', 'docx', 'org'];
    private static $fileSizeLimit= 5 * 1024 * 1024; //5mo max file size

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
            $size=$file->getSize();
            if (!in_array($extension, self::$validExtensions)) {
                return redirect()->back()->with('error', 'File\'s extension unauthorized.');
            }
            if($size>self::$fileSizeLimit){
                return redirect()->back()->with('error', 'File\'s size limit exceeded(MAX 5 Mo).');
            }

            $patient = Auth::user()->patient;
            $patientId = $patient->user_id;

            $existingRecord = MedicalRecord::where('user_id', $patientId)->where('name', $name)->first();
            if ($existingRecord) {
                // If a record already exists, we update it with the new file name
                $existingRecord->storeFile($file);
                return redirect()->back()->with('success', 'The file has been modified.');
            } else {
                // If no record exists, we create a new one
                $record = new MedicalRecord();
                $record->name = $file->getClientOriginalName();
                $record->user_id = $patientId;


                $fileContent = file_get_contents($file->path());
                // Generate a symmetric encryption key
                $encryptionKey = random_bytes(32);
                // Generate a random IV
                $iv = random_bytes(16);
                // Encrypt file contents with symmetric encryption key and IV
                $encryptedContent = openssl_encrypt($fileContent, 'AES-256-CBC', $encryptionKey, OPENSSL_RAW_DATA, $iv);

                $doctorPatient = DoctorPatient::where('patient_id', Auth::user()->patient->patient_id)->get();
                if ($doctorPatient) {
                    foreach ($doctorPatient as $doctor) {
                        $doctorData = Doctor::find($doctor->doctor_id);
                        openssl_public_encrypt($encryptionKey, $encryptedKey, $doctorData->user->public_key);
                        Storage::put('public/medical_records/' . $name . $doctorData->user->email . '.key', $encryptedKey);
                    }
                }
                // Encrypt the symmetric encryption key with the public key
                openssl_public_encrypt($encryptionKey, $encryptedKey, Auth::user()->public_key);
                // Store encrypted file, IV and encrypted key
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
        //Delete bin, iv and key files
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


            // Create a temporary file
            $tempFilePath = sys_get_temp_dir() . '/' . $name;
            file_put_contents($tempFilePath, $decryptedContent);

            // Download the file in its original format
            return response()->download($tempFilePath)->deleteFileAfterSend(true);
        }

        return redirect()->back()->with('error', 'File not found.');
    }
}
