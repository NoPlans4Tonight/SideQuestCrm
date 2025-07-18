<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Customer;
use App\Models\Appointment;
use App\Models\Estimate;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create(['domain' => 'test-dashboard-' . uniqid()]);
        $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    }

    public function test_index_returns_dashboard_data(): void
    {
        $this->authenticateUser();

        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        // Create appointments with different statuses
        Appointment::factory()->count(3)->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'confirmed',
            'start_time' => Carbon::tomorrow(), // These are the upcoming appointments
        ]);

        Appointment::factory()->count(2)->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'in_progress',
            'start_time' => Carbon::now()->addDays(2), // Ensure these appear in upcoming appointments
        ]);

        // Create completed appointments for this month
        Appointment::factory()->count(4)->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'completed',
            'completed_at' => Carbon::now(),
            'total_cost' => 1000.00,
        ]);

        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'name',
                    'email',
                ],
                'stats' => [
                    'totalCustomers',
                    'activeAppointments',
                    'completedThisMonth',
                    'revenueThisMonth',
                ],
                'upcomingAppointments' => [
                    '*' => [
                        'id',
                        'title',
                        'start_time',
                        'status',
                        'customer' => [
                            'id',
                            'first_name',
                            'last_name',
                        ]
                    ]
                ],
                'recentActivity',
            ]);

        $responseData = $response->json();

        // Assert user data
        $this->assertEquals($this->user->name, $responseData['user']['name']);
        $this->assertEquals($this->user->email, $responseData['user']['email']);

        // Assert statistics
        $this->assertEquals(1, $responseData['stats']['totalCustomers']); // 1 customer
        $this->assertEquals(5, $responseData['stats']['activeAppointments']); // 3 confirmed + 2 in_progress
        $this->assertEquals(4, $responseData['stats']['completedThisMonth']);
        $this->assertEquals(4000.00, $responseData['stats']['revenueThisMonth']); // 4 * 1000.00

        // Assert upcoming appointments
        $this->assertCount(5, $responseData['upcomingAppointments']);
    }

    public function test_index_returns_zero_stats_when_no_data(): void
    {
        $this->authenticateUser();

        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ],
                'stats' => [
                    'totalCustomers' => 0,
                    'activeAppointments' => 0,
                    'completedThisMonth' => 0,
                    'revenueThisMonth' => 0,
                ],
                'upcomingAppointments' => [],
                'recentActivity' => [],
            ]);
    }

    public function test_index_excludes_completed_and_cancelled_appointments_from_upcoming(): void
    {
        $this->authenticateUser();

        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        // Create completed appointment scheduled for tomorrow (should be excluded)
        Appointment::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'completed',
            'start_time' => Carbon::tomorrow(),
        ]);

        // Create cancelled appointment scheduled for tomorrow (should be excluded)
        Appointment::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'cancelled',
            'start_time' => Carbon::tomorrow(),
        ]);

        // Create confirmed appointment scheduled for tomorrow (should be included)
        Appointment::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'confirmed',
            'start_time' => Carbon::tomorrow(),
        ]);

        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(200);

        $upcomingAppointments = $response->json('upcomingAppointments');
        $this->assertCount(1, $upcomingAppointments);
        $this->assertEquals('confirmed', $upcomingAppointments[0]['status']);
    }

    public function test_index_only_counts_appointments_from_current_month_for_completed_stats(): void
    {
        $this->authenticateUser();

        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        // Create completed appointment from last month
        Appointment::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'completed',
            'completed_at' => Carbon::now()->subMonth(),
            'total_cost' => 500.00,
        ]);

        // Create completed appointment from this month
        Appointment::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'completed',
            'completed_at' => Carbon::now(),
            'total_cost' => 1000.00,
        ]);

        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(200);

        $stats = $response->json('stats');
        $this->assertEquals(1, $stats['completedThisMonth']);
        $this->assertEquals(1000.00, $stats['revenueThisMonth']);
    }

    public function test_index_limits_upcoming_appointments_to_five(): void
    {
        $this->authenticateUser();

        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        // Create 7 upcoming appointments
        Appointment::factory()->count(7)->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'confirmed',
            'start_time' => Carbon::tomorrow(),
        ]);

        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(200);

        $upcomingAppointments = $response->json('upcomingAppointments');
        $this->assertCount(5, $upcomingAppointments); // Should be limited to 5
    }

    public function test_index_orders_upcoming_appointments_by_start_time(): void
    {
        $this->authenticateUser();

        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        // Create appointments with different start times
        $appointment3 = Appointment::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'confirmed',
            'start_time' => Carbon::now()->addDays(3),
        ]);

        $appointment1 = Appointment::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'confirmed',
            'start_time' => Carbon::now()->addDays(1),
        ]);

        $appointment2 = Appointment::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'confirmed',
            'start_time' => Carbon::now()->addDays(2),
        ]);

        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(200);

        $upcomingAppointments = $response->json('upcomingAppointments');
        $this->assertCount(3, $upcomingAppointments);

        // Should be ordered by start_time ascending
        $this->assertEquals($appointment1->id, $upcomingAppointments[0]['id']);
        $this->assertEquals($appointment2->id, $upcomingAppointments[1]['id']);
        $this->assertEquals($appointment3->id, $upcomingAppointments[2]['id']);
    }

    public function test_index_handles_database_errors_gracefully(): void
    {
        $this->authenticateUser();

        // Mock a database error by dropping the appointments table
        \Schema::dropIfExists('appointments');

        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(500)
            ->assertJsonStructure([
                'error',
                'message',
            ])
            ->assertJson([
                'error' => 'Dashboard data could not be loaded',
            ]);
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(401);
    }


}
