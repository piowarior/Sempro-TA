<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ToxinAssessmentResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'patient_id',
        'total_indicated',
        'level',
        'assessment_date',
        'notes',
    ];

    protected $casts = [
        'assessment_date' => 'date',
        'total_indicated' => 'integer',
    ];

    public static function calculateLevel(int $totalIndicated): string
    {
        if ($totalIndicated <= 5) {
            return 'Level 1';
        }
        if ($totalIndicated <= 15) {
            return 'Level 2';
        }
        return 'Level 3';
    }

    public function session()
    {
        return $this->belongsTo(PatientProgressSession::class, 'session_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function answers()
    {
        return $this->hasMany(ToxinAssessmentAnswer::class, 'result_id')->orderBy('indicator_number');
    }
}

