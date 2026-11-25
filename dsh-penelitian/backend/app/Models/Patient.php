<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'patient_number',
        'name',
        'birth_date',
        'gender',
        'phone_number',
        'address',
        'patient_type',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sessions()
    {
        return $this->hasMany(PatientProgressSession::class)->orderBy('session_date', 'desc');
    }

    public function visits()
    {
        return $this->hasMany(Visit::class)->orderBy('visit_date', 'desc');
    }

    public function activityPlans()
    {
        return $this->hasMany(ActivityPlan::class)->orderBy('start_date', 'desc');
    }

    public function activityReports()
    {
        return $this->hasMany(PatientActivityReport::class)->orderBy('report_time', 'desc');
    }
}

