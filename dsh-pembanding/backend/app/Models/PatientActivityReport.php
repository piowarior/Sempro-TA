<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientActivityReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_plan_id',
        'activity_schedule_id',
        'patient_id',
        'activity_date',
        'report_time',
        'execution_status',
        'patient_notes',
    ];

    protected $casts = [
        'activity_date' => 'date',
        'report_time' => 'datetime',
    ];

    public function plan()
    {
        return $this->belongsTo(ActivityPlan::class, 'activity_plan_id');
    }

    public function schedule()
    {
        return $this->belongsTo(ActivitySchedule::class, 'activity_schedule_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}

