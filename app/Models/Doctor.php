<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $fillable = [
        'name',
        'specialization',
    ];

    public function availabilities()
    {
        return $this->hasMany(Availability::class);
    }

    public function appointmentSlots()
    {
        return $this->hasMany(AppointmentSlot::class);
    }

    public function breaks()
    {
        return $this->hasMany(DoctorBreak::class);
    }
}
