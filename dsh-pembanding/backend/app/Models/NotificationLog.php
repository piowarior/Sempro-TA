<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'activity_plan_id',
        'patient_id',
        'notification_type',
        'recipient',
        'message',
        'sent_at',
        'delivery_status',
        'provider_response',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function plan()
    {
        return $this->belongsTo(ActivityPlan::class, 'activity_plan_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}

