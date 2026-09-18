<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\AppointmentSlot;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = request()->user()
            ->appointments()
            ->with(['doctor', 'slot'])
            ->latest()
            ->get();

        return view('appointments.index', compact('appointments'));
    }

    public function store(StoreAppointmentRequest $request)
    {
        $appointment = DB::transaction(function () use ($request) {
            $slot = AppointmentSlot::whereKey($request->validated('appointment_slot_id'))
                ->lockForUpdate()
                ->firstOrFail();

            if ($slot->status !== 'available') {
                abort(409, 'That slot has already been booked.');
            }

            $slot->update(['status' => 'booked']);

            return Appointment::create([
                'patient_id' => $request->user()->id,
                'doctor_id' => $slot->doctor_id,
                'appointment_slot_id' => $slot->id,
                'status' => 'booked',
            ]);
        });

        return redirect()->route('appointments.index')->with('status', 'Appointment booked.');
    }

    public function destroy(Appointment $appointment)
    {
        abort_unless($appointment->patient_id === request()->user()->id, 403);

        DB::transaction(function () use ($appointment) {
            $appointment->update(['status' => 'cancelled']);
            $appointment->slot()->update(['status' => 'available']);
        });

        return back()->with('status', 'Appointment cancelled.');
    }
}
