<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Job;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;
    public function test_index_returns_dashboard_data(): void
    {
        $this->authenticateUser();

        // Create test data
        Customer::factory()->count(5)->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        // Create jobs with different statuses
        Job::factory()->count(3)->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'scheduled',
            'scheduled_date' => Carbon::tomorrow(), // These are the upcoming jobs
        ]);

        Job::factory()->count(2)->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'in_progress',
            'scheduled_date' => null, // Ensure these don't appear in upcoming jobs
        ]);

        // Create completed jobs for this month
        Job::factory()->count(4)->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'completed',
            'completed_at' => Carbon::now(),
            'total_cost' => 1000.00,
        ]);

        // Remove the separate "upcoming jobs" creation since they're the same as the scheduled jobs above

        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'name',
                    'email',
                ],
                'stats' => [
                    'totalCustomers',
                    'activeJobs',
                    'completedThisMonth',
                    'revenueThisMonth',
                ],
                'upcomingJobs' => [
                    '*' => [
                        'id',
                        'title',
                        'scheduled_date',
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
        $this->assertEquals(6, $responseData['stats']['totalCustomers']); // 5 + 1 customer
        $this->assertEquals(5, $responseData['stats']['activeJobs']); // 3 scheduled + 2 in_progress
        $this->assertEquals(4, $responseData['stats']['completedThisMonth']);
        $this->assertEquals(4000.00, $responseData['stats']['revenueThisMonth']); // 4 * 1000.00

        // Assert upcoming jobs
        $this->assertCount(3, $responseData['upcomingJobs']);
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
                    'activeJobs' => 0,
                    'completedThisMonth' => 0,
                    'revenueThisMonth' => 0,
                ],
                'upcomingJobs' => [],
                'recentActivity' => [],
            ]);
    }

    public function test_index_excludes_completed_and_cancelled_jobs_from_upcoming(): void
    {
        $this->authenticateUser();

        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        // Create completed job scheduled for tomorrow (should be excluded)
        Job::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'completed',
            'scheduled_date' => Carbon::tomorrow(),
        ]);

        // Create cancelled job scheduled for tomorrow (should be excluded)
        Job::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'cancelled',
            'scheduled_date' => Carbon::tomorrow(),
        ]);

        // Create scheduled job scheduled for tomorrow (should be included)
        Job::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'scheduled',
            'scheduled_date' => Carbon::tomorrow(),
        ]);

        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(200);

        $upcomingJobs = $response->json('upcomingJobs');
        $this->assertCount(1, $upcomingJobs);
        $this->assertEquals('scheduled', $upcomingJobs[0]['status']);
    }

    public function test_index_only_counts_jobs_from_current_month_for_completed_stats(): void
    {
        $this->authenticateUser();

        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        // Create completed job from last month
        Job::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'completed',
            'completed_at' => Carbon::now()->subMonth(),
            'total_cost' => 500.00,
        ]);

        // Create completed job from this month
        Job::factory()->create([
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

    public function test_index_limits_upcoming_jobs_to_five(): void
    {
        $this->authenticateUser();

        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        // Create 7 upcoming jobs
        Job::factory()->count(7)->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'scheduled',
            'scheduled_date' => Carbon::tomorrow(),
        ]);

        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(200);

        $upcomingJobs = $response->json('upcomingJobs');
        $this->assertCount(5, $upcomingJobs); // Should be limited to 5
    }

    public function test_index_orders_upcoming_jobs_by_scheduled_date(): void
    {
        $this->authenticateUser();

        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        // Create jobs with different scheduled dates
        $job3 = Job::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'scheduled',
            'scheduled_date' => Carbon::now()->addDays(3),
        ]);

        $job1 = Job::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'scheduled',
            'scheduled_date' => Carbon::now()->addDay(),
        ]);

        $job2 = Job::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'scheduled',
            'scheduled_date' => Carbon::now()->addDays(2),
        ]);

        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(200);

        $upcomingJobs = $response->json('upcomingJobs');
        $this->assertCount(3, $upcomingJobs);

        // Should be ordered by scheduled_date ascending
        $this->assertEquals($job1->id, $upcomingJobs[0]['id']);
        $this->assertEquals($job2->id, $upcomingJobs[1]['id']);
        $this->assertEquals($job3->id, $upcomingJobs[2]['id']);
    }

    public function test_index_handles_database_errors_gracefully(): void
    {
        $this->authenticateUser();

        // Mock a database error by temporarily dropping the jobs table
        // This will cause an error when the controller tries to query jobs
        \DB::statement('DROP TABLE IF EXISTS crm_jobs');

        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(500)
            ->assertJsonStructure([
                'error',
                'message',
            ])
            ->assertJson([
                'error' => 'Dashboard data could not be loaded',
            ]);

        // Don't try to restore the table - let the test framework handle it
        // The RefreshDatabase trait will handle the cleanup
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(401);
    }
}
