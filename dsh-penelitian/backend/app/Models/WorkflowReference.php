<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowReference extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_plan_id',
        'workflow_id',
        'run_id',
        'workflow_type',
        'status',
        'started_at',
        'completed_at',
        'last_heartbeat_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_heartbeat_at' => 'datetime',
    ];

    public function plan()
    {
        return $this->belongsTo(ActivityPlan::class, 'activity_plan_id');
    }
}

