<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ToxinAssessmentAnswer extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'result_id',
        'indicator_number',
        'indicator_name',
        'is_indicated',
        'created_at',
    ];

    protected $casts = [
        'indicator_number' => 'integer',
        'is_indicated' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function result()
    {
        return $this->belongsTo(ToxinAssessmentResult::class, 'result_id');
    }
}

