<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\AppointmentSlot;
use App\Models\Availability;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Doctor $doctor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->doctor = Doctor::create(['name' => 'Dr. House', 'specialization' => 'Diagnostics']);
    }

    public function test_admin_can_add_multiple_non_overlapping_periods_same_date(): void
    {
        $date = now()->addDays(2)->toDateString();

        $response1 = $this->actingAs($this->admin)->post(route('admin.availability.store', $this->doctor), [
            'date' => $date,
            'start_time' => '09:00',
            'end_time' => '13:00',
        ]);
        $response1->assertSessionHasNoErrors();

        $response2 = $this->actingAs($this->admin)->post(route('admin.availability.store', $this->doctor), [
            'date' => $date,
            'start_time' => '14:00',
            'end_time' => '17:00',
        ]);
        $response2->assertSessionHasNoErrors();

        $this->assertDatabaseCount('availabilities', 2);

        $avail1 = Availability::where('doctor_id', $this->doctor->id)->where('start_time', '09:00')->first();
        $avail2 = Availability::where('doctor_id', $this->doctor->id)->where('start_time', '14:00')->first();

        $this->assertNotNull($avail1);
        $this->assertNotNull($avail2);

        // Check slot count and availability_id attribution
        $slots1 = AppointmentSlot::where('availability_id', $avail1->id)->get();
        $slots2 = AppointmentSlot::where('availability_id', $avail2->id)->get();

        $this->assertCount(8, $slots1); // 9:00 - 13:00 (30m slots => 8)
        $this->assertCount(6, $slots2); // 14:00 - 17:00 (30m slots => 6)

        // Ensure no slots generated in the 13:00-14:00 gap
        $gapSlots = AppointmentSlot::where('doctor_id', $this->doctor->id)
            ->where('date', $date)
            ->where('start_time', '>=', '13:00:00')
            ->where('start_time', '<', '14:00:00')
            ->count();
        $this->assertEquals(0, $gapSlots);
    }

    public function test_overlapping_period_rejected_with_validation_error(): void
    {
        $date = now()->addDays(2)->toDateString();

        $this->actingAs($this->admin)->post(route('admin.availability.store', $this->doctor), [
            'date' => $date,
            'start_time' => '09:00',
            'end_time' => '13:00',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.availability.store', $this->doctor), [
            'date' => $date,
            'start_time' => '12:00',
            'end_time' => '15:00',
        ]);

        $response->assertSessionHasErrors('start_time');
        $this->assertDatabaseCount('availabilities', 1);
    }

    public function test_past_start_time_on_today_is_rejected(): void
    {
        $date = now()->toDateString();
        // Pick a time clearly in the past
        $pastStartTime = now()->subHours(2)->format('H:i');
        $pastEndTime = now()->subHours(1)->format('H:i');

        $response = $this->actingAs($this->admin)->post(route('admin.availability.store', $this->doctor), [
            'date' => $date,
            'start_time' => $pastStartTime,
            'end_time' => $pastEndTime,
        ]);

        $response->assertSessionHasErrors('start_time');
        $this->assertDatabaseCount('availabilities', 0);
    }

    public function test_deleting_an_availability_with_no_booked_appointments_removes_it_and_slots(): void
    {
        $date = now()->addDays(2)->toDateString();

        $this->actingAs($this->admin)->post(route('admin.availability.store', $this->doctor), [
            'date' => $date,
            'start_time' => '09:00',
            'end_time' => '11:00',
        ]);

        $availability = Availability::first();
        $this->assertNotNull($availability);
        $this->assertDatabaseCount('appointment_slots', 4);

        $response = $this->actingAs($this->admin)->delete(route('admin.availability.destroy', $availability));
        $response->assertRedirect();

        $this->assertDatabaseMissing('availabilities', ['id' => $availability->id]);
        $this->assertDatabaseCount('appointment_slots', 0);
    }

    public function test_deleting_an_availability_with_booked_appointments_is_blocked_with_409(): void
    {
        $date = now()->addDays(2)->toDateString();

        $this->actingAs($this->admin)->post(route('admin.availability.store', $this->doctor), [
            'date' => $date,
            'start_time' => '09:00',
            'end_time' => '11:00',
        ]);

        $availability = Availability::first();
        $slot = $availability->appointmentSlots()->first();

        $patient = User::factory()->create(['role' => 'patient']);
        Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_slot_id' => $slot->id,
            'status' => 'booked',
        ]);
        $slot->update(['status' => 'booked']);

        $response = $this->actingAs($this->admin)->delete(route('admin.availability.destroy', $availability));
        $response->assertStatus(409);

        $this->assertDatabaseHas('availabilities', ['id' => $availability->id]);
        $this->assertDatabaseCount('appointment_slots', 4);
    }
}
