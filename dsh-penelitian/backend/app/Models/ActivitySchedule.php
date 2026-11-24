<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivitySchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_plan_id',
        'scheduled_date',
        'reminder_time',
        'status',
        'reminder_sent_at',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'reminder_sent_at' => 'datetime',
    ];

    public function plan()
    {
        return $this->belongsTo(ActivityPlan::class, 'activity_plan_id');
    }

    public function report()
    {
        return $this->hasOne(PatientActivityReport::class, 'activity_schedule_id');
    }
}

