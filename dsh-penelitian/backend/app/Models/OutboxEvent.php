<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OutboxEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'event_type',
        'aggregate_type',
        'aggregate_id',
        'payload',
        'correlation_id',
        'event_version',
        'status',
        'retry_count',
        'last_error',
        'occurred_at',
        'published_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'occurred_at' => 'datetime',
        'published_at' => 'datetime',
        'retry_count' => 'integer',
        'event_version' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->event_id)) {
                $model->event_id = (string) Str::uuid();
            }
            if (empty($model->correlation_id)) {
                $model->correlation_id = (string) Str::uuid();
            }
            if (empty($model->occurred_at)) {
                $model->occurred_at = now();
            }
        });
    }

    public function markAsPublished()
    {
        $this->update([
            'status' => 'published',
            'published_at' => now(),
            'last_error' => null,
        ]);
    }

    public function markAsFailed(string $error)
    {
        $this->increment('retry_count');
        $this->update([
            'status' => 'failed',
            'last_error' => $error,
        ]);
    }
}

