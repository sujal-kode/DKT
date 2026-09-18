<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDoctorBreakRequest;
use App\Models\Doctor;
use App\Models\DoctorBreak;
use App\Services\AppointmentRebooker;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DoctorBreakController extends Controller
{
    /**
     * Store a newly created doctor break and handle appointment relocation.
     */
    public function store(StoreDoctorBreakRequest $request, Doctor $doctor)
    {
        DB::transaction(function () use ($request, $doctor) {
            $doctorBreak = $doctor->breaks()->create($request->validated());

            // Block non-booked covered slots first
            $doctorBreak->coveredSlots()->where('status', 'available')->update(['status' => 'blocked']);

            // Rebook or cancel conflicting booked appointments
            (new AppointmentRebooker())->handleBreak($doctorBreak);

            // Ensure all slots covered by this break are marked blocked
            $doctorBreak->coveredSlots()->update(['status' => 'blocked']);
        });

        return back()->with('status', 'Break added and affected appointments rebooked.');
    }

    /**
     * Remove the specified doctor break and restore available slots if not covered by other breaks.
     */
    public function destroy(DoctorBreak $doctorBreak)
    {
        DB::transaction(function () use ($doctorBreak) {
            $coveredSlots = $doctorBreak->coveredSlots()->where('status', 'blocked')->get();
            $doctorId = $doctorBreak->doctor_id;
            $dateStr = $doctorBreak->date->format('Y-m-d');

            $otherBreaks = DoctorBreak::where('doctor_id', $doctorId)
                ->whereDate('date', $dateStr)
                ->whereKeyNot($doctorBreak->id)
                ->get();

            foreach ($coveredSlots as $slot) {
                $slotStart = Carbon::parse($dateStr.' '.$slot->start_time)->format('H:i:s');
                $coveredByOther = false;

                foreach ($otherBreaks as $otherBreak) {
                    $otherStart = Carbon::parse($dateStr.' '.$otherBreak->start_time)->format('H:i:s');
                    $otherEnd = Carbon::parse($dateStr.' '.$otherBreak->end_time)->format('H:i:s');

                    if ($slotStart >= $otherStart && $slotStart < $otherEnd) {
                        $coveredByOther = true;
                        break;
                    }
                }

                if (! $coveredByOther) {
                    $slot->update(['status' => 'available']);
                }
            }

            $doctorBreak->delete();
        });

        return back()->with('status', 'Break deleted and slots restored.');
    }
}
