<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'session_id',
        'name',
        'description',
        'start_date',
        'end_date',
        'schedule_days',
        'frequency_type',
        'reminder_time',
        'additional_notes',
        'status',
        'created_by_user_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'schedule_days' => 'array',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function session()
    {
        return $this->belongsTo(PatientProgressSession::class, 'session_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function schedules()
    {
        return $this->hasMany(ActivitySchedule::class)->orderBy('scheduled_date');
    }

    public function reports()
    {
        return $this->hasMany(PatientActivityReport::class)->orderBy('report_time', 'desc');
    }

    public function workflowReference()
    {
        return $this->hasOne(WorkflowReference::class);
    }

    public function notificationLogs()
    {
        return $this->hasMany(NotificationLog::class)->orderBy('sent_at', 'desc');
    }
}

