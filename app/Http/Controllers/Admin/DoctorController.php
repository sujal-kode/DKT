<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDoctorRequest;
use App\Models\Doctor;

class DoctorController extends Controller
{
    public function index()
    {
        $doctors = Doctor::withCount('availabilities')->latest()->get();

        return view('admin.doctors.index', compact('doctors'));
    }

    public function store(StoreDoctorRequest $request)
    {
        Doctor::create($request->validated());

        return back()->with('status', 'Doctor added.');
    }

    public function destroy(Doctor $doctor)
    {
        $doctor->delete();

        return back()->with('status', 'Doctor removed.');
    }
}
