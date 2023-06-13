<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MedicalRecord extends Model
{
    use HasFactory;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($medicalRecord) {
            $medicalRecord->id = Str::uuid();
        });
    }

    /**
     * Store the uploaded file and update the model attributes.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return void
     */
    public function storeFile($file)
    {
        $fileName = $file->getClientOriginalName();
        $fileExtension = $file->getClientOriginalExtension();
        $encryptedFilePath = 'public/medical_records/' . $fileName . '.bin';

        // Store the file in encrypted form
        //Encrypt the received file with the ciphered symmetric key
        $fileContent = file_get_contents($file->path());
        $encryptedKey = Storage::get('public/medical_records/' . $fileName . Auth::user()->email . '.key');
        $iv = Storage::get('public/medical_records/' . $fileName . '.iv');
        $pathPrivateKey = file_get_contents(Auth::user()->private_key);
        openssl_private_decrypt($encryptedKey, $decryptedKey, $pathPrivateKey);
        $encryptedContent = openssl_encrypt($fileContent, 'AES-256-CBC', $decryptedKey, OPENSSL_RAW_DATA, $iv);

        Storage::put($encryptedFilePath, $encryptedContent);

        $this->name = $fileName;
        $this->file_ext = $fileExtension;

        $this->save();
    }
}
