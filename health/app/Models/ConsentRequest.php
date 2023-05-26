<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Doctor;


class ConsentRequest extends Model
{
    use HasFactory;

    // protected $table = 'consent_request';
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'doctor_id');
    }
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
  
}
