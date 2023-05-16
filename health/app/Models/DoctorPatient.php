<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorPatient extends Model
{
    use HasFactory;
    protected $table = 'doctor_patient';

    protected $primaryKey = ['doctor_id', 'patient_id'];
    public $incrementing = false;
    public $timestamps = true;
}
