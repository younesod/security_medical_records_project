<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Patient;
use Illuminate\Support\Str;

class Doctor extends Model
{

    use HasFactory;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'doctor_id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The key type for the model.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($doctor) {
            $doctor->doctor_id = Str::uuid();
        });
    }
    /**
     * Get the user that owns the patient.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the patients associated with the doctor.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function patients()
    {
        return $this->belongsToMany(Patient::class, 'doctor_patient', 'doctor_id', 'patient_id');
    }

    /**
     * Get the patients with medical records associated with the doctor.
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function patientsWithMedicalRecord()
    {
        return $this->hasManyThrough(Patient::class, MedicalRecord::class, 'doctor_id', 'patient_id');
    }

    /**
     * Get the consent requests associated with the doctor.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function consentRequests()
    {
        return $this->hasMany(ConsentRequest::class);
    }
}
