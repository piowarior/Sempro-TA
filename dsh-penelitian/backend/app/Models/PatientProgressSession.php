<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientProgressSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'session_number',
        'session_date',
        'staff_notes',
        'examination_summary',
        'recorded_at',
    ];

    protected $casts = [
        'session_date' => 'date',
        'recorded_at' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function toxinAssessment()
    {
        return $this->hasOne(ToxinAssessmentResult::class, 'session_id');
    }

    public function bodyMeasurement()
    {
        return $this->hasOne(BodyMeasurement::class, 'session_id');
    }

    public function bloodMeasurement()
    {
        return $this->hasOne(BloodMeasurement::class, 'session_id');
    }

    public function activityPlans()
    {
        return $this->hasMany(ActivityPlan::class, 'session_id');
    }
}

