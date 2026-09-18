<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentSlot extends Model
{
    protected $fillable = [
        'doctor_id',
        'availability_id',
        'date',
        'start_time',
        'end_time',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function availability()
    {
        return $this->belongsTo(Availability::class);
    }

    public function appointment()
    {
        return $this->hasOne(Appointment::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }
}
