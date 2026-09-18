<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAvailabilityRequest;
use App\Models\Availability;
use App\Models\Doctor;
use Illuminate\Support\Facades\DB;

class AvailabilityController extends Controller
{
    /**
     * View availability and breaks of all doctors.
     */
    public function index()
    {
        $doctors = Doctor::with([
            'availabilities' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
            'breaks' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
        ])->get();

        return view('admin.availability.index', compact('doctors'));
    }

    public function store(StoreAvailabilityRequest $request, Doctor $doctor)
    {
        DB::transaction(function () use ($request, $doctor) {
            $availability = $doctor->availabilities()->create($request->validated());
            $availability->generateSlots();
        });

        return back()->with('status', 'Availability set and slots generated.');
    }

    public function destroy(Availability $availability)
    {
        if ($availability->appointmentSlots()->where('status', 'booked')->exists()) {
            abort(409, 'Cannot delete availability period with booked appointments. Please cancel or reschedule them first.');
        }

        $availability->delete();

        return back()->with('status', 'Availability period deleted.');
    }
}
