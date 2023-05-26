<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Patient extends Model
{
    use HasFactory;

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
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function doctors()
    {
        return $this->belongsToMany(Doctor::class, 'doctor_patient', 'patient_id', 'doctor_id');
    }

    /**
     * Get the user associated with the patient.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the medical record associated with the patient.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function medicalRecord()
    {
        return $this->hasOne(MedicalRecord::class);
    }

    /**
     * Get the consent requests associated with the patient.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function consentRequests()
    {
        return $this->hasMany(ConsentRequest::class);
    }

}
