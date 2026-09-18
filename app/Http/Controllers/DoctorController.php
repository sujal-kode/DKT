<?php

namespace App\Http\Controllers;

use App\Models\Doctor;

class DoctorController extends Controller
{
    public function index()
    {
        $doctors = Doctor::orderBy('name')->get();

        return view('doctors.index', compact('doctors'));
    }

    public function slots(Doctor $doctor)
    {
        $slots = $doctor->appointmentSlots()
            ->available()
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->groupBy(fn ($slot) => $slot->date->format('Y-m-d'));

        return view('doctors.slots', compact('doctor', 'slots'));
    }
}
