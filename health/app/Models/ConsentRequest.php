<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsentRequest extends Model
{
    use HasFactory;

    // protected $table = 'consent_request';
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
    public function doctorUser()
{
    return $this->belongsTo(Doctor::class, 'doctor_id')->with('user');
}


    public function patientUser()
    {
        return $this->belongsTo(Patient::class,'patient_id')->with('user');
    }
}
