<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BodyMeasurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'patient_id',
        'weight',
        'bmi',
        'waist_circumference',
        'body_fat',
        'water',
        'muscle_mass',
        'physical_rating',
        'cell_age',
        'bone_mass',
        'visceral_fat',
        'body_temperature',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'bmi' => 'decimal:2',
        'waist_circumference' => 'decimal:2',
        'body_fat' => 'decimal:2',
        'water' => 'decimal:2',
        'muscle_mass' => 'decimal:2',
        'physical_rating' => 'integer',
        'cell_age' => 'integer',
        'bone_mass' => 'decimal:2',
        'visceral_fat' => 'decimal:2',
        'body_temperature' => 'decimal:2',
    ];

    public function session()
    {
        return $this->belongsTo(PatientProgressSession::class, 'session_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}

