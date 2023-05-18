<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
    ];


    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'patient_id';

    /**
     * Get the doctor associated with the patient.
     */
    public function doctors()
    {
        return $this->belongsToMany(Doctor::class, 'doctor_patient', 'patient_id', 'doctor_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function medicalRecord()
    {
        return $this->hasOne(MedicalRecord::class);
    }
}
