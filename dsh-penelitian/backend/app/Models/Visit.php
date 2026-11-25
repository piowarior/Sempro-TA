<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'meeting_number',
        'visit_date',
        'time_slot',
        'booking_type',
        'status',
        'requested_by',
        'staff_notes',
        'patient_notes',
        'confirmed_at',
        'completed_at',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'confirmed_at' => 'datetime',
        'completed_at' => 'datetime',
        'meeting_number' => 'integer',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function progressSession()
    {
        return $this->hasOne(PatientProgressSession::class, 'visit_id');
    }

    public function session()
    {
        return $this->hasOne(PatientProgressSession::class, 'visit_id');
    }
}
