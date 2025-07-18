<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AppointmentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $customer;

        protected function setUp(): void
    {
        parent::setUp();

        $this->customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);
    }

    public function test_can_list_appointments()
    {
        Appointment::factory()->count(5)->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/appointments');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'appointment_type',
                        'start_time',
                        'end_time',
                        'status',
                        'customer',
                        'assigned_user',
                    ]
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ]
            ]);
    }

    public function test_can_create_appointment()
    {
        $appointmentData = [
            'title' => 'Test Appointment',
            'description' => 'Test description',
            'appointment_type' => 'estimate',
            'customer_id' => $this->customer->id,
            'start_time' => Carbon::tomorrow()->setTime(9, 0)->toISOString(),
            'end_time' => Carbon::tomorrow()->setTime(10, 0)->toISOString(),
            'duration' => 60,
            'status' => 'scheduled',
            'location' => 'Test Location',
            'notes' => 'Test notes',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/appointments', $appointmentData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'title',
                    'description',
                    'appointment_type',
                    'start_time',
                    'end_time',
                    'status',
                ]
            ]);

        $this->assertDatabaseHas('appointments', [
            'title' => 'Test Appointment',
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
        ]);
    }

    public function test_can_show_appointment()
    {
        $appointment = Appointment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/appointments/{$appointment->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'description',
                    'appointment_type',
                    'start_time',
                    'end_time',
                    'status',
                    'customer',
                ]
            ]);
    }

    public function test_can_update_appointment()
    {
        $appointment = Appointment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
        ]);

        $updateData = [
            'title' => 'Updated Appointment',
            'description' => 'Updated description',
            'appointment_type' => 'repair',
            'status' => 'confirmed',
            'location' => 'Updated Location',
            'start_time' => Carbon::tomorrow()->setTime(9, 0)->toISOString(),
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/appointments/{$appointment->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'title',
                    'description',
                    'appointment_type',
                    'status',
                ]
            ]);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'title' => 'Updated Appointment',
            'status' => 'confirmed',
        ]);
    }

    public function test_can_delete_appointment()
    {
        $appointment = Appointment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/appointments/{$appointment->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Appointment deleted successfully'
            ]);

        $this->assertDatabaseMissing('appointments', [
            'id' => $appointment->id,
        ]);
    }

    public function test_can_get_upcoming_appointments()
    {
        Appointment::factory()->count(3)->upcoming()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/appointments/upcoming');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'start_time',
                        'status',
                    ]
                ]
            ]);
    }

    public function test_can_get_appointments_by_date()
    {
        $date = Carbon::tomorrow()->format('Y-m-d');

        Appointment::factory()->count(2)->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'start_time' => Carbon::tomorrow()->setTime(9, 0),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/appointments/by-date?date={$date}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'start_time',
                    ]
                ]
            ]);
    }

    public function test_can_check_availability()
    {
        $startTime = Carbon::tomorrow()->setTime(9, 0)->toISOString();
        $endTime = Carbon::tomorrow()->setTime(10, 0)->toISOString();

        $response = $this->actingAs($this->user)
            ->postJson('/api/appointments/check-availability', [
                'start_time' => $startTime,
                'end_time' => $endTime,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'available'
            ]);
    }

    public function test_can_mark_appointment_as_confirmed()
    {
        $appointment = Appointment::factory()->scheduled()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/appointments/{$appointment->id}/confirm");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Appointment marked as confirmed'
            ]);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'confirmed',
        ]);
    }

    public function test_can_mark_appointment_as_completed()
    {
        $appointment = Appointment::factory()->confirmed()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/appointments/{$appointment->id}/complete");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Appointment marked as completed'
            ]);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'completed',
        ]);
    }

    public function test_can_mark_appointment_as_cancelled()
    {
        $appointment = Appointment::factory()->scheduled()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/appointments/{$appointment->id}/cancel");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Appointment marked as cancelled'
            ]);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_can_mark_appointment_as_no_show()
    {
        $appointment = Appointment::factory()->scheduled()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/appointments/{$appointment->id}/no-show");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Appointment marked as no show'
            ]);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'no_show',
        ]);
    }

    public function test_validates_required_fields_on_create()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/appointments', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'title',
                'appointment_type',
                'start_time',
                'status',
            ]);
    }

    public function test_validates_start_time_is_in_future()
    {
        $appointmentData = [
            'title' => 'Test Appointment',
            'appointment_type' => 'estimate',
            'start_time' => Carbon::yesterday()->toISOString(),
            'status' => 'scheduled',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/appointments', $appointmentData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['start_time']);
    }

    public function test_validates_end_time_is_after_start_time()
    {
        $appointmentData = [
            'title' => 'Test Appointment',
            'appointment_type' => 'estimate',
            'start_time' => Carbon::tomorrow()->setTime(10, 0)->toISOString(),
            'end_time' => Carbon::tomorrow()->setTime(9, 0)->toISOString(),
            'status' => 'scheduled',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/appointments', $appointmentData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_time']);
    }

    public function test_prevents_access_to_other_tenant_appointments()
    {
        $otherTenant = Tenant::factory()->create();
        $otherAppointment = Appointment::factory()->create([
            'tenant_id' => $otherTenant->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/appointments/{$otherAppointment->id}");

        $response->assertStatus(404);
    }
}
