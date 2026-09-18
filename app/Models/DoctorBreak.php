<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorBreak extends Model
{
    protected $fillable = ['doctor_id', 'date', 'start_time', 'end_time'];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public static function overlapsExisting(int $doctorId, string $date, string $start, string $end, ?int $ignoreId = null): bool
    {
        $startFormatted = \Illuminate\Support\Carbon::parse($start)->format('H:i:s');
        $endFormatted = \Illuminate\Support\Carbon::parse($end)->format('H:i:s');

        return static::where('doctor_id', $doctorId)
            ->whereDate('date', $date)
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->where('start_time', '<', $endFormatted)
            ->where('end_time', '>', $startFormatted)
            ->exists();
    }

    /**
     * Slots for this doctor+date whose start_time falls within this break.
     */
    public function coveredSlots()
    {
        $startFormatted = \Illuminate\Support\Carbon::parse($this->start_time)->format('H:i:s');
        $endFormatted = \Illuminate\Support\Carbon::parse($this->end_time)->format('H:i:s');
        $dateStr = \Illuminate\Support\Carbon::parse($this->date)->format('Y-m-d');

        return AppointmentSlot::where('doctor_id', $this->doctor_id)
            ->whereDate('date', $dateStr)
            ->where('start_time', '>=', $startFormatted)
            ->where('start_time', '<', $endFormatted);
    }
}
