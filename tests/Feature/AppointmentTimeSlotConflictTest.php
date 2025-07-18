<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class AppointmentTimeSlotConflictTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a tenant
        $this->tenant = Tenant::factory()->create();

        // Create two users in the same tenant
        $this->user1 = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->user2 = User::factory()->create(['tenant_id' => $this->tenant->id]);
    }

    public function test_time_slot_conflict_only_checks_assigned_user()
    {
        // Create an appointment for user1 at a specific time
        $startTime = now()->addHour();
        $endTime = now()->addHours(2);

        $existingAppointment = Appointment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'assigned_to' => $this->user1->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'scheduled'
        ]);

        // Try to create an appointment for user2 at the same time
        // This should NOT conflict because it's a different user
        $appointmentData = [
            'title' => 'Test Appointment for User 2',
            'description' => 'Test description',
            'appointment_type' => 'estimate',
            'start_time' => $startTime->format('Y-m-d H:i:s'),
            'end_time' => $endTime->format('Y-m-d H:i:s'),
            'status' => 'scheduled',
            'assigned_to' => $this->user2->id,
        ];

        $response = $this->actingAs($this->user1)
            ->postJson('/api/appointments', $appointmentData);

        // Should succeed because it's a different user
        $response->assertStatus(201);
        $this->assertDatabaseHas('appointments', [
            'title' => 'Test Appointment for User 2',
            'assigned_to' => $this->user2->id,
        ]);
    }

    public function test_time_slot_conflict_prevents_same_user_conflict()
    {
        // Create an appointment for user1 at a specific time
        $startTime = now()->addHour();
        $endTime = now()->addHours(2);

        $existingAppointment = Appointment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'assigned_to' => $this->user1->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'scheduled'
        ]);

        // Try to create another appointment for user1 at the same time
        // This SHOULD conflict because it's the same user
        $appointmentData = [
            'title' => 'Conflicting Appointment for User 1',
            'description' => 'Test description',
            'appointment_type' => 'estimate',
            'start_time' => $startTime->format('Y-m-d H:i:s'),
            'end_time' => $endTime->format('Y-m-d H:i:s'),
            'status' => 'scheduled',
            'assigned_to' => $this->user1->id,
        ];

        $response = $this->actingAs($this->user1)
            ->postJson('/api/appointments', $appointmentData);

        // Should fail because it's the same user
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['time_slot']);
        $this->assertDatabaseMissing('appointments', [
            'title' => 'Conflicting Appointment for User 1',
        ]);
    }

    public function test_time_slot_conflict_with_overlapping_times()
    {
        // Create an appointment for user1 from 1:00 PM to 3:00 PM
        $startTime1 = now()->setTime(13, 0, 0);
        $endTime1 = now()->setTime(15, 0, 0);

        $existingAppointment = Appointment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'assigned_to' => $this->user1->id,
            'start_time' => $startTime1,
            'end_time' => $endTime1,
            'status' => 'scheduled'
        ]);

        // Try to create an appointment for user1 from 2:00 PM to 4:00 PM (overlapping)
        $startTime2 = now()->setTime(14, 0, 0);
        $endTime2 = now()->setTime(16, 0, 0);

        $appointmentData = [
            'title' => 'Overlapping Appointment',
            'description' => 'Test description',
            'appointment_type' => 'estimate',
            'start_time' => $startTime2->format('Y-m-d H:i:s'),
            'end_time' => $endTime2->format('Y-m-d H:i:s'),
            'status' => 'scheduled',
            'assigned_to' => $this->user1->id,
        ];

        $response = $this->actingAs($this->user1)
            ->postJson('/api/appointments', $appointmentData);

        // Should fail because of overlap
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['time_slot']);
    }

    public function test_time_slot_conflict_ignores_cancelled_appointments()
    {
        // Create a cancelled appointment for user1 at a specific time
        $startTime = now()->addHour();
        $endTime = now()->addHours(2);

        $cancelledAppointment = Appointment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'assigned_to' => $this->user1->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'cancelled'
        ]);

        // Try to create an appointment for user1 at the same time
        // This should succeed because the existing appointment is cancelled
        $appointmentData = [
            'title' => 'New Appointment After Cancelled',
            'description' => 'Test description',
            'appointment_type' => 'estimate',
            'start_time' => $startTime->format('Y-m-d H:i:s'),
            'end_time' => $endTime->format('Y-m-d H:i:s'),
            'status' => 'scheduled',
            'assigned_to' => $this->user1->id,
        ];

        $response = $this->actingAs($this->user1)
            ->postJson('/api/appointments', $appointmentData);

        // Should succeed because cancelled appointments don't count as conflicts
        $response->assertStatus(201);
        $this->assertDatabaseHas('appointments', [
            'title' => 'New Appointment After Cancelled',
            'assigned_to' => $this->user1->id,
        ]);
    }
}
