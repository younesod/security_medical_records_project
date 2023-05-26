<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Patient;
use Illuminate\Support\Str;
class Doctor extends Model
{

    use HasFactory;


    protected $primaryKey = 'doctor_id';
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
    protected static function boot(){
        parent::boot();

        static::creating(function ($doctor) {
            $doctor->doctor_id = Str::uuid();
        });
    }
    /**
     * Get the user that owns the patient.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function patients()
    {
        return $this->belongsToMany(Patient::class, 'doctor_patient', 'doctor_id', 'patient_id');
    }
    public function patientsWithMedicalRecord()
    {
    return $this->hasManyThrough(Patient::class, MedicalRecord::class, 'doctor_id', 'patient_id');
    }
    public function consentRequests()
    {
        return $this->hasMany(ConsentRequest::class);
    }
    
}
