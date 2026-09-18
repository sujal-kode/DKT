<?php

namespace Tests\Feature;

use App\Models\Availability;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentBookingTest extends TestCase
{
    use RefreshDatabase;

    private function makeSlots(): Doctor
    {
        $doctor = Doctor::create(['name' => 'Dr. Smith', 'specialization' => 'Cardiology']);

        $availability = Availability::create([
            'doctor_id' => $doctor->id,
            'date' => now()->addDay()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '10:00',
        ]);

        $availability->generateSlots();

        return $doctor;
    }

    public function test_patient_can_book_an_available_slot_and_it_becomes_unavailable(): void
    {
        $doctor = $this->makeSlots();
        $patient = User::factory()->create(['role' => 'patient']);
        $slot = $doctor->appointmentSlots()->available()->first();

        $response = $this->actingAs($patient)->post('/appointments', [
            'appointment_slot_id' => $slot->id,
        ]);

        $response->assertRedirect(route('appointments.index'));

        $this->assertDatabaseHas('appointments', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_slot_id' => $slot->id,
            'status' => 'booked',
        ]);

        $this->assertDatabaseHas('appointment_slots', [
            'id' => $slot->id,
            'status' => 'booked',
        ]);

        $availableSlots = $this->actingAs($patient)->get(route('doctors.slots', $doctor));
        $availableSlots->assertDontSee($slot->start_time);
    }

    public function test_cancelling_an_appointment_frees_the_slot_for_rebooking(): void
    {
        $doctor = $this->makeSlots();
        $patient = User::factory()->create(['role' => 'patient']);
        $slot = $doctor->appointmentSlots()->available()->first();

        $this->actingAs($patient)->post('/appointments', [
            'appointment_slot_id' => $slot->id,
        ]);

        $appointment = $patient->appointments()->first();

        $this->actingAs($patient)->delete(route('appointments.destroy', $appointment));

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'cancelled',
        ]);

        $this->assertDatabaseHas('appointment_slots', [
            'id' => $slot->id,
            'status' => 'available',
        ]);

        $otherPatient = User::factory()->create(['role' => 'patient']);
        $rebook = $this->actingAs($otherPatient)->post('/appointments', [
            'appointment_slot_id' => $slot->id,
        ]);

        $rebook->assertRedirect(route('appointments.index'));
        $this->assertDatabaseHas('appointments', [
            'patient_id' => $otherPatient->id,
            'appointment_slot_id' => $slot->id,
            'status' => 'booked',
        ]);
    }

    public function test_patient_cannot_cancel_another_patients_appointment(): void
    {
        $doctor = $this->makeSlots();
        $owner = User::factory()->create(['role' => 'patient']);
        $intruder = User::factory()->create(['role' => 'patient']);
        $slot = $doctor->appointmentSlots()->available()->first();

        $this->actingAs($owner)->post('/appointments', [
            'appointment_slot_id' => $slot->id,
        ]);

        $appointment = $owner->appointments()->first();

        $response = $this->actingAs($intruder)->delete(route('appointments.destroy', $appointment));

        $response->assertForbidden();
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'booked',
        ]);
    }

    public function test_patient_cannot_access_admin_routes(): void
    {
        $patient = User::factory()->create(['role' => 'patient']);

        $response = $this->actingAs($patient)->get(route('admin.doctors.index'));

        $response->assertForbidden();
    }

    public function test_admin_cannot_access_patient_booking_routes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('doctors.index'));

        $response->assertForbidden();
    }
}
