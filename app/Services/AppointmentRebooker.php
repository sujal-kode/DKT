<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AppointmentSlot;
use App\Models\DoctorBreak;
use Carbon\Carbon;

class AppointmentRebooker
{
    private const MAX_DAYS_HORIZON = 30;

    /**
     * Rebook or cancel appointments affected by a doctor break.
     */
    public function handleBreak(DoctorBreak $break): void
    {
        $coveredSlotIds = $break->coveredSlots()->pluck('id');

        if ($coveredSlotIds->isEmpty()) {
            return;
        }

        $appointments = Appointment::whereIn('appointment_slot_id', $coveredSlotIds)
            ->where('status', 'booked')
            ->lockForUpdate()
            ->get();

        foreach ($appointments as $appointment) {
            $originalSlot = $appointment->slot()->lockForUpdate()->first();

            if (! $originalSlot) {
                continue;
            }

            $candidateSlot = $this->findNearestSlot($originalSlot, $break);

            if ($candidateSlot) {
                $lockedCandidate = AppointmentSlot::whereKey($candidateSlot->id)
                    ->lockForUpdate()
                    ->first();

                if ($lockedCandidate && $lockedCandidate->status === 'available') {
                    $lockedCandidate->update(['status' => 'booked']);
                    $appointment->update(['appointment_slot_id' => $lockedCandidate->id]);
                    continue;
                }

                // Retry once excluding the stale candidate
                $retryCandidate = $this->findNearestSlot($originalSlot, $break, $candidateSlot->id);
                if ($retryCandidate) {
                    $lockedRetry = AppointmentSlot::whereKey($retryCandidate->id)
                        ->lockForUpdate()
                        ->first();

                    if ($lockedRetry && $lockedRetry->status === 'available') {
                        $lockedRetry->update(['status' => 'booked']);
                        $appointment->update(['appointment_slot_id' => $lockedRetry->id]);
                        continue;
                    }
                }
            }

            // No available alternative slot found
            $appointment->update([
                'status' => 'cancelled_by_break',
                'cancellation_reason' => 'Cancelled due to doctor schedule change (break).',
            ]);
        }
    }

    /**
     * Find the nearest available slot (same-day first with tie preferring later, then next 30 days).
     */
    public function findNearestSlot(AppointmentSlot $originalSlot, DoctorBreak $break, ?int $excludeSlotId = null): ?AppointmentSlot
    {
        $doctorId = $originalSlot->doctor_id;
        $originalDateStr = $originalSlot->date->format('Y-m-d');
        $origTimeCarbon = Carbon::parse($originalDateStr.' '.$originalSlot->start_time);

        // Ring 0: Same day
        $sameDayBreaks = DoctorBreak::where('doctor_id', $doctorId)
            ->whereDate('date', $originalDateStr)
            ->get();

        $sameDaySlots = AppointmentSlot::where('doctor_id', $doctorId)
            ->whereDate('date', $originalDateStr)
            ->where('status', 'available')
            ->when($excludeSlotId, fn ($q) => $q->whereKeyNot($excludeSlotId))
            ->get()
            ->filter(function (AppointmentSlot $slot) use ($sameDayBreaks) {
                return ! $this->isSlotCoveredByBreaks($slot, $sameDayBreaks);
            });

        if ($sameDaySlots->isNotEmpty()) {
            $sorted = $sameDaySlots->sort(function (AppointmentSlot $a, AppointmentSlot $b) use ($originalDateStr, $origTimeCarbon) {
                $timeA = Carbon::parse($originalDateStr.' '.$a->start_time);
                $timeB = Carbon::parse($originalDateStr.' '.$b->start_time);

                $diffA = abs($origTimeCarbon->diffInMinutes($timeA));
                $diffB = abs($origTimeCarbon->diffInMinutes($timeB));

                if ($diffA !== $diffB) {
                    return $diffA <=> $diffB;
                }

                // Tie-breaker: prefer later slot
                return $timeB <=> $timeA;
            });

            return $sorted->first();
        }

        // Ring 1..30: Future days
        for ($dayOffset = 1; $dayOffset <= self::MAX_DAYS_HORIZON; $dayOffset++) {
            $futureDateStr = Carbon::parse($originalDateStr)->addDays($dayOffset)->format('Y-m-d');

            $futureBreaks = DoctorBreak::where('doctor_id', $doctorId)
                ->whereDate('date', $futureDateStr)
                ->get();

            $futureSlots = AppointmentSlot::where('doctor_id', $doctorId)
                ->whereDate('date', $futureDateStr)
                ->where('status', 'available')
                ->when($excludeSlotId, fn ($q) => $q->whereKeyNot($excludeSlotId))
                ->orderBy('start_time')
                ->get()
                ->filter(function (AppointmentSlot $slot) use ($futureBreaks) {
                    return ! $this->isSlotCoveredByBreaks($slot, $futureBreaks);
                });

            if ($futureSlots->isNotEmpty()) {
                return $futureSlots->first();
            }
        }

        return null;
    }

    /**
     * Check if a slot falls within any given break window.
     */
    private function isSlotCoveredByBreaks(AppointmentSlot $slot, $breaks): bool
    {
        $slotStart = Carbon::parse($slot->date->format('Y-m-d').' '.$slot->start_time)->format('H:i:s');

        foreach ($breaks as $break) {
            $breakStart = Carbon::parse($break->date->format('Y-m-d').' '.$break->start_time)->format('H:i:s');
            $breakEnd = Carbon::parse($break->date->format('Y-m-d').' '.$break->end_time)->format('H:i:s');

            if ($slotStart >= $breakStart && $slotStart < $breakEnd) {
                return true;
            }
        }

        return false;
    }
}
