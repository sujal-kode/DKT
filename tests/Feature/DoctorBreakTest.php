<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\AppointmentSlot;
use App\Models\Availability;
use App\Models\Doctor;
use App\Models\DoctorBreak;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorBreakTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $patient;
    private Doctor $doctor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->patient = User::factory()->create(['role' => 'patient']);
        $this->doctor = Doctor::create(['name' => 'Dr. Gregory', 'specialization' => 'Neurology']);
    }

    private function createAvailability(string $date, string $start = '09:00', string $end = '12:00'): Availability
    {
        $avail = Availability::create([
            'doctor_id' => $this->doctor->id,
            'date' => $date,
            'start_time' => $start,
            'end_time' => $end,
        ]);
        $avail->generateSlots();

        return $avail;
    }

    public function test_adding_a_break_blocks_its_covered_slots(): void
    {
        $date = now()->addDays(2)->toDateString();
        $this->createAvailability($date, '09:00', '12:00'); // 9:00, 9:30, 10:00, 10:30, 11:00, 11:30

        $response = $this->actingAs($this->admin)->post(route('admin.breaks.store', $this->doctor), [
            'date' => $date,
            'start_time' => '10:00',
            'end_time' => '11:00',
        ]);
        $response->assertSessionHasNoErrors();

        // 10:00 and 10:30 slots should now be blocked
        $blockedSlots = AppointmentSlot::where('doctor_id', $this->doctor->id)
            ->whereDate('date', $date)
            ->where('status', 'blocked')
            ->pluck('start_time')
            ->map(fn ($t) => substr($t, 0, 5))
            ->toArray();

        $this->assertEquals(['10:00', '10:30'], $blockedSlots);

        // Patient fetching slots should not see 10:00 or 10:30
        $patientResponse = $this->actingAs($this->patient)->get(route('doctors.slots', $this->doctor));
        $patientResponse->assertDontSee('10:00 AM');
        $patientResponse->assertDontSee('10:30 AM');
        $patientResponse->assertSee('9:00 AM');
    }

    public function test_booking_a_blocked_slot_is_rejected(): void
    {
        $date = now()->addDays(2)->toDateString();
        $this->createAvailability($date, '09:00', '10:00');

        $slot = AppointmentSlot::where('doctor_id', $this->doctor->id)->whereDate('date', $date)->first();
        $slot->update(['status' => 'blocked']);

        $response = $this->actingAs($this->patient)->post('/appointments', [
            'appointment_slot_id' => $slot->id,
        ]);

        $response->assertStatus(409);
    }

    public function test_overlapping_break_for_same_doctor_and_date_is_rejected(): void
    {
        $date = now()->addDays(2)->toDateString();

        $this->actingAs($this->admin)->post(route('admin.breaks.store', $this->doctor), [
            'date' => $date,
            'start_time' => '10:00',
            'end_time' => '11:00',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.breaks.store', $this->doctor), [
            'date' => $date,
            'start_time' => '10:30',
            'end_time' => '11:30',
        ]);

        $response->assertSessionHasErrors('start_time');
        $this->assertDatabaseCount('doctor_breaks', 1);
    }

    public function test_past_start_time_on_today_is_rejected(): void
    {
        $date = now()->toDateString();
        $pastStartTime = now()->subHours(2)->format('H:i');
        $pastEndTime = now()->subHours(1)->format('H:i');

        $response = $this->actingAs($this->admin)->post(route('admin.breaks.store', $this->doctor), [
            'date' => $date,
            'start_time' => $pastStartTime,
            'end_time' => $pastEndTime,
        ]);

        $response->assertSessionHasErrors('start_time');
        $this->assertDatabaseCount('doctor_breaks', 0);
    }

    public function test_break_covering_booked_slot_with_equidistant_slots_moves_to_later_slot(): void
    {
        $date = now()->addDays(2)->toDateString();
        // Slots: 09:00, 09:30, 10:00, 10:30, 11:00
        $this->createAvailability($date, '09:00', '11:30');

        $slot1000 = AppointmentSlot::where('doctor_id', $this->doctor->id)
            ->whereDate('date', $date)
            ->where('start_time', 'like', '10:00%')
            ->first();

        $slot1000->update(['status' => 'booked']);
        $appointment = Appointment::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_slot_id' => $slot1000->id,
            'status' => 'booked',
        ]);

        // Break covering 10:00 to 10:30 (affects 10:00 slot)
        // Equidistant available slots: 09:30 (-30m) and 10:30 (+30m)
        // Tie-breaker must prefer 10:30
        $this->actingAs($this->admin)->post(route('admin.breaks.store', $this->doctor), [
            'date' => $date,
            'start_time' => '10:00',
            'end_time' => '10:30',
        ]);

        $appointment->refresh();
        $this->assertEquals('booked', $appointment->status);

        $newSlot = $appointment->slot;
        $this->assertStringStartsWith('10:30', $newSlot->start_time);
        $this->assertEquals('booked', $newSlot->status);

        // Original 10:00 slot is covered by break so it must be blocked
        $slot1000->refresh();
        $this->assertEquals('blocked', $slot1000->status);
    }

    public function test_break_with_no_later_same_day_slot_moves_to_nearest_earlier_slot(): void
    {
        $date = now()->addDays(2)->toDateString();
        // Slots: 09:00, 09:30, 10:00 (end 10:30)
        $this->createAvailability($date, '09:00', '10:30');

        $slot1000 = AppointmentSlot::where('doctor_id', $this->doctor->id)
            ->whereDate('date', $date)
            ->where('start_time', 'like', '10:00%')
            ->first();

        $slot1000->update(['status' => 'booked']);
        $appointment = Appointment::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_slot_id' => $slot1000->id,
            'status' => 'booked',
        ]);

        // Break 10:00-10:30: no later same-day slot exists, nearest earlier is 09:30
        $this->actingAs($this->admin)->post(route('admin.breaks.store', $this->doctor), [
            'date' => $date,
            'start_time' => '10:00',
            'end_time' => '10:30',
        ]);

        $appointment->refresh();
        $this->assertEquals('booked', $appointment->status);
        $this->assertStringStartsWith('09:30', $appointment->slot->start_time);
    }

    public function test_break_covering_only_same_day_slot_moves_to_next_available_day(): void
    {
        $date1 = now()->addDays(2)->toDateString();
        $date2 = now()->addDays(3)->toDateString();

        $this->createAvailability($date1, '09:00', '09:30'); // Only 1 slot on date1
        $this->createAvailability($date2, '14:00', '15:00'); // Slots on date2: 14:00, 14:30

        $slotDay1 = AppointmentSlot::where('doctor_id', $this->doctor->id)
            ->whereDate('date', $date1)
            ->first();

        $slotDay1->update(['status' => 'booked']);
        $appointment = Appointment::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_slot_id' => $slotDay1->id,
            'status' => 'booked',
        ]);

        // Break on date1 covering 09:00-09:30
        $this->actingAs($this->admin)->post(route('admin.breaks.store', $this->doctor), [
            'date' => $date1,
            'start_time' => '09:00',
            'end_time' => '09:30',
        ]);

        $appointment->refresh();
        $this->assertEquals('booked', $appointment->status);
        $this->assertEquals($date2, $appointment->slot->date->format('Y-m-d'));
        $this->assertStringStartsWith('14:00', $appointment->slot->start_time);
    }

    public function test_break_with_no_alternative_in_30_day_horizon_cancels_appointment(): void
    {
        $date = now()->addDays(2)->toDateString();
        $this->createAvailability($date, '09:00', '09:30'); // Only 1 slot overall

        $slot = AppointmentSlot::where('doctor_id', $this->doctor->id)->first();
        $slot->update(['status' => 'booked']);

        $appointment = Appointment::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_slot_id' => $slot->id,
            'status' => 'booked',
        ]);

        $this->actingAs($this->admin)->post(route('admin.breaks.store', $this->doctor), [
            'date' => $date,
            'start_time' => '09:00',
            'end_time' => '09:30',
        ]);

        $appointment->refresh();
        $this->assertEquals('cancelled_by_break', $appointment->status);
        $this->assertNotNull($appointment->cancellation_reason);

        // Slot remains blocked
        $slot->refresh();
        $this->assertEquals('blocked', $slot->status);
    }

    public function test_patient_appointments_index_shows_new_time_after_break_move(): void
    {
        $date = now()->addDays(2)->toDateString();
        $this->createAvailability($date, '09:00', '10:30'); // 9:00, 9:30, 10:00

        $slot = AppointmentSlot::where('doctor_id', $this->doctor->id)->where('start_time', '09:00:00')->first();
        $slot->update(['status' => 'booked']);

        $appointment = Appointment::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_slot_id' => $slot->id,
            'status' => 'booked',
        ]);

        // Move by adding break on 09:00-09:30 -> moves to 09:30
        $this->actingAs($this->admin)->post(route('admin.breaks.store', $this->doctor), [
            'date' => $date,
            'start_time' => '09:00',
            'end_time' => '09:30',
        ]);

        $response = $this->actingAs($this->patient)->get(route('appointments.index'));
        $response->assertSee('9:30 AM');
    }

    public function test_deleting_a_break_unblocks_slots_only_if_no_other_break_covers_them(): void
    {
        $date = now()->addDays(2)->toDateString();
        $this->createAvailability($date, '09:00', '12:00'); // 9:00, 9:30, 10:00, 10:30, 11:00, 11:30

        // Break 1: 09:00 - 10:30 (covers 9:00, 9:30, 10:00)
        $break1 = DoctorBreak::create([
            'doctor_id' => $this->doctor->id,
            'date' => $date,
            'start_time' => '09:00',
            'end_time' => '10:30',
        ]);
        $break1->coveredSlots()->update(['status' => 'blocked']);

        // Break 2: 10:00 - 11:00 (covers 10:00, 10:30)
        $break2 = DoctorBreak::create([
            'doctor_id' => $this->doctor->id,
            'date' => $date,
            'start_time' => '10:00',
            'end_time' => '11:00',
        ]);
        $break2->coveredSlots()->update(['status' => 'blocked']);

        // Delete Break 1
        $this->actingAs($this->admin)->delete(route('admin.breaks.destroy', $break1));

        $slot900 = AppointmentSlot::where('doctor_id', $this->doctor->id)->where('start_time', '09:00:00')->first();
        $slot930 = AppointmentSlot::where('doctor_id', $this->doctor->id)->where('start_time', '09:30:00')->first();
        $slot1000 = AppointmentSlot::where('doctor_id', $this->doctor->id)->where('start_time', '10:00:00')->first();

        // 9:00 and 9:30 should be restored to available
        $this->assertEquals('available', $slot900->status);
        $this->assertEquals('available', $slot930->status);

        // 10:00 is still covered by Break 2, so it must stay blocked
        $this->assertEquals('blocked', $slot1000->status);
    }
}
