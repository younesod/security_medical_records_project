<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Patient extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($patient) {
            $patient->patient_id = Str::uuid();
        });
    }
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
        return $this->belongsTo(User::class, 'user_id');
    }
    public function medicalRecord()
    {
        return $this->hasOne(MedicalRecord::class);
    }
    public function consentRequests()
    {
        return $this->hasMany(ConsentRequest::class);
    }

}
