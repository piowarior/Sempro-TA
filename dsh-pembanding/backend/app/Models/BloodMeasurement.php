<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloodMeasurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'patient_id',
        'blood_pressure',
        'heart_rate',
        'hemoglobin',
        'glucose',
        'cholesterol',
        'uric_acid',
        'ketone',
    ];

    protected $casts = [
        'heart_rate' => 'integer',
        'hemoglobin' => 'decimal:2',
        'glucose' => 'decimal:2',
        'cholesterol' => 'decimal:2',
        'uric_acid' => 'decimal:2',
        'ketone' => 'decimal:2',
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

