<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class AppointmentDateFilterTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a tenant
        $this->tenant = Tenant::factory()->create();

        // Create a user in the tenant
        $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    }

    public function test_can_filter_appointments_by_date_range()
    {
        // Create appointments for different dates
        $today = now()->format('Y-m-d');
        $tomorrow = now()->addDay()->format('Y-m-d');
        $nextWeek = now()->addWeek()->format('Y-m-d');

        $appointment1 = Appointment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'assigned_to' => $this->user->id,
            'start_time' => $today . ' 10:00:00',
            'status' => 'scheduled'
        ]);

        $appointment2 = Appointment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'assigned_to' => $this->user->id,
            'start_time' => $tomorrow . ' 14:00:00',
            'status' => 'scheduled'
        ]);

        $appointment3 = Appointment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'assigned_to' => $this->user->id,
            'start_time' => $nextWeek . ' 09:00:00',
            'status' => 'scheduled'
        ]);

        // Test filtering by today only
        $response = $this->actingAs($this->user)
            ->getJson('/api/appointments?date_from=' . $today . '&date_to=' . $today);

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $appointment1->id);

        // Test filtering by today and tomorrow
        $response = $this->actingAs($this->user)
            ->getJson('/api/appointments?date_from=' . $today . '&date_to=' . $tomorrow);

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');

        $appointmentIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($appointment1->id, $appointmentIds);
        $this->assertContains($appointment2->id, $appointmentIds);
        $this->assertNotContains($appointment3->id, $appointmentIds);
    }

    public function test_can_filter_appointments_by_status()
    {
        $today = now()->format('Y-m-d');

        $scheduledAppointment = Appointment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'assigned_to' => $this->user->id,
            'start_time' => $today . ' 10:00:00',
            'status' => 'scheduled'
        ]);

        $confirmedAppointment = Appointment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'assigned_to' => $this->user->id,
            'start_time' => $today . ' 14:00:00',
            'status' => 'confirmed'
        ]);

        // Test filtering by scheduled status
        $response = $this->actingAs($this->user)
            ->getJson('/api/appointments?status=scheduled');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $scheduledAppointment->id);

        // Test filtering by confirmed status
        $response = $this->actingAs($this->user)
            ->getJson('/api/appointments?status=confirmed');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $confirmedAppointment->id);
    }

    public function test_can_filter_appointments_by_assigned_user()
    {
        $today = now()->format('Y-m-d');
        $otherUser = User::factory()->create(['tenant_id' => $this->tenant->id]);

        $myAppointment = Appointment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'assigned_to' => $this->user->id,
            'start_time' => $today . ' 10:00:00',
            'status' => 'scheduled'
        ]);

        $otherAppointment = Appointment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'assigned_to' => $otherUser->id,
            'start_time' => $today . ' 14:00:00',
            'status' => 'scheduled'
        ]);

        // Test filtering by assigned user
        $response = $this->actingAs($this->user)
            ->getJson('/api/appointments?assigned_to=' . $this->user->id);

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $myAppointment->id);

        // Test filtering by other user
        $response = $this->actingAs($this->user)
            ->getJson('/api/appointments?assigned_to=' . $otherUser->id);

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $otherAppointment->id);
    }

    public function test_can_combine_multiple_filters()
    {
        $today = now()->format('Y-m-d');
        $otherUser = User::factory()->create(['tenant_id' => $this->tenant->id]);

        $myScheduledAppointment = Appointment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'assigned_to' => $this->user->id,
            'start_time' => $today . ' 10:00:00',
            'status' => 'scheduled'
        ]);

        $myConfirmedAppointment = Appointment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'assigned_to' => $this->user->id,
            'start_time' => $today . ' 14:00:00',
            'status' => 'confirmed'
        ]);

        $otherAppointment = Appointment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'assigned_to' => $otherUser->id,
            'start_time' => $today . ' 16:00:00',
            'status' => 'scheduled'
        ]);

        // Test combining date, status, and assigned_to filters
        $response = $this->actingAs($this->user)
            ->getJson('/api/appointments?date_from=' . $today . '&date_to=' . $today . '&status=scheduled&assigned_to=' . $this->user->id);

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $myScheduledAppointment->id);
    }
}
