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
        'systolic',
        'diastolic',
        'heart_rate',
        'hemoglobin',
        'glucose',
        'cholesterol',
        'uric_acid',
        'ketone',
        'gki',
        'metabolic_notes',
    ];

    protected $casts = [
        'systolic' => 'integer',
        'diastolic' => 'integer',
        'heart_rate' => 'integer',
        'hemoglobin' => 'decimal:2',
        'glucose' => 'decimal:2',
        'cholesterol' => 'decimal:2',
        'uric_acid' => 'decimal:2',
        'ketone' => 'decimal:2',
        'gki' => 'decimal:2',
    ];

    public static function computeGki(?float $glucose, ?float $ketone): ?float
    {
        if ($glucose && $ketone && $ketone > 0) {
            return round(($glucose / 18) / $ketone, 2);
        }
        return null;
    }

    public function session()
    {
        return $this->belongsTo(PatientProgressSession::class, 'session_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}

