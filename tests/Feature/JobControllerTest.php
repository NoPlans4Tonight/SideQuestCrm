<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobControllerTest extends TestCase
{
    use RefreshDatabase;
    public function test_index_returns_paginated_jobs(): void
    {
        $this->authenticateUser();

        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        Job::factory()->count(20)->create([
            'customer_id' => $customer->id,
        ]);

        $response = $this->getJson('/api/jobs');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'customer_id',
                        'status',
                        'priority',
                        'scheduled_date',
                        'estimated_hours',
                        'price',
                        'notes',
                        'created_at',
                        'updated_at',
                        'customer' => [
                            'id',
                            'first_name',
                            'last_name',
                        ]
                    ]
                ],
                'current_page',
                'last_page',
                'per_page',
                'total',
            ]);

        $this->assertCount(15, $response->json('data')); // Default per page
        $this->assertEquals(20, $response->json('total'));
    }

    public function test_store_creates_new_job(): void
    {
        $this->authenticateUser();

        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $jobData = [
            'title' => 'Test Job',
            'description' => 'This is a test job description',
            'customer_id' => $customer->id,
            'status' => 'scheduled', // Change from 'pending' to 'scheduled'
            'priority' => 'medium',
            'scheduled_date' => '2024-01-15',
            'estimated_hours' => 5.5,
            'price' => 500.00,
            'notes' => 'Test notes',
        ];

        $response = $this->postJson('/api/jobs', $jobData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'job' => [
                    'id',
                    'title',
                    'description',
                    'customer_id',
                    'status',
                    'priority',
                    'scheduled_date',
                    'estimated_hours',
                    'price',
                    'notes',
                    'created_at',
                    'updated_at',
                    'customer' => [
                        'id',
                        'first_name',
                        'last_name',
                    ]
                ]
            ])
            ->assertJson([
                'message' => 'Job created successfully',
                'job' => [
                    'title' => 'Test Job',
                    'description' => 'This is a test job description',
                    'customer_id' => $customer->id,
                    'status' => 'scheduled',
                    'priority' => 'medium',
                ]
            ]);

        $this->assertDatabaseHas('crm_jobs', [
            'title' => 'Test Job',
            'description' => 'This is a test job description',
            'customer_id' => $customer->id,
        ]);
    }

    public function test_store_returns_validation_errors_for_invalid_data(): void
    {
        $this->authenticateUser();

        $invalidData = [
            'title' => '', // Required field is empty
            'description' => 'Test description',
            'customer_id' => 999, // Non-existent customer
            'status' => 'invalid_status', // Invalid status
            'priority' => 'invalid_priority', // Invalid priority
        ];

        $response = $this->postJson('/api/jobs', $invalidData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'title',
                    'customer_id',
                    'status',
                    'priority',
                ]
            ])
            ->assertJson([
                'message' => 'Validation failed',
            ]);
    }

    public function test_show_returns_job_with_customer(): void
    {
        $this->authenticateUser();

        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $job = Job::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
        ]);

        $response = $this->getJson("/api/jobs/{$job->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'description',
                    'customer_id',
                    'status',
                    'priority',
                    'scheduled_date',
                    'estimated_hours',
                    'price',
                    'notes',
                    'created_at',
                    'updated_at',
                    'customer' => [
                        'id',
                        'first_name',
                        'last_name',
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    'id' => $job->id,
                    'title' => $job->title,
                    'description' => $job->description,
                    'customer_id' => $customer->id,
                ]
            ]);
    }

    public function test_show_returns_404_for_nonexistent_job(): void
    {
        $this->authenticateUser();

        $response = $this->getJson('/api/jobs/999');

        $response->assertStatus(404);
    }

    public function test_update_modifies_job(): void
    {
        $this->authenticateUser();

        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $job = Job::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'scheduled',
        ]);

        $updateData = [
            'title' => 'Updated Job Title',
            'description' => 'Updated job description',
            'customer_id' => $customer->id,
            'status' => 'in_progress',
            'priority' => 'high',
            'scheduled_date' => '2024-01-20',
            'estimated_hours' => 8.0,
            'price' => 750.00,
            'notes' => 'Updated notes',
        ];

        $response = $this->putJson("/api/jobs/{$job->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'job' => [
                    'id',
                    'title',
                    'description',
                    'customer_id',
                    'status',
                    'priority',
                    'scheduled_date',
                    'estimated_hours',
                    'price',
                    'notes',
                    'created_at',
                    'updated_at',
                    'customer' => [
                        'id',
                        'first_name',
                        'last_name',
                    ]
                ]
            ])
            ->assertJson([
                'message' => 'Job updated successfully',
                'job' => [
                    'title' => 'Updated Job Title',
                    'description' => 'Updated job description',
                    'status' => 'in_progress',
                    'priority' => 'high',
                ]
            ]);

        $this->assertDatabaseHas('crm_jobs', [
            'id' => $job->id,
            'title' => 'Updated Job Title',
            'description' => 'Updated job description',
            'status' => 'in_progress',
        ]);
    }

    public function test_update_sets_completed_at_when_status_changes_to_completed(): void
    {
        $this->authenticateUser();

        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $job = Job::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'in_progress',
        ]);

        $updateData = [
            'title' => $job->title,
            'description' => $job->description,
            'customer_id' => $customer->id,
            'status' => 'completed',
            'priority' => $job->priority,
        ];

        $response = $this->putJson("/api/jobs/{$job->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('crm_jobs', [
            'id' => $job->id,
            'status' => 'completed',
        ]);

        $updatedJob = Job::find($job->id);
        $this->assertNotNull($updatedJob->completed_at);
    }

    public function test_update_returns_validation_errors_for_invalid_data(): void
    {
        $this->authenticateUser();

        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $job = Job::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
        ]);

        $invalidData = [
            'title' => '', // Required field is empty
            'description' => 'Test description',
            'customer_id' => 999, // Non-existent customer
            'status' => 'invalid_status', // Invalid status
            'priority' => 'invalid_priority', // Invalid priority
        ];

        $response = $this->putJson("/api/jobs/{$job->id}", $invalidData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'title',
                    'customer_id',
                    'status',
                    'priority',
                ]
            ]);
    }

    public function test_update_returns_404_for_nonexistent_job(): void
    {
        $this->authenticateUser();

        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $updateData = [
            'title' => 'Test Job',
            'description' => 'Test description',
            'customer_id' => $customer->id,
            'status' => 'pending',
            'priority' => 'medium',
        ];

        $response = $this->putJson('/api/jobs/999', $updateData);

        $response->assertStatus(404);
    }

    public function test_destroy_deletes_job(): void
    {
        $this->authenticateUser();

        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $job = Job::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
        ]);

        $response = $this->deleteJson("/api/jobs/{$job->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Job deleted successfully'
            ]);

        $this->assertDatabaseMissing('crm_jobs', ['id' => $job->id]);
    }

    public function test_destroy_returns_404_for_nonexistent_job(): void
    {
        $this->authenticateUser();

        $response = $this->deleteJson('/api/jobs/999');

        $response->assertStatus(404);
    }

    public function test_unauthenticated_requests_are_rejected(): void
    {
        $response = $this->getJson('/api/jobs');
        $response->assertStatus(401);

        $response = $this->postJson('/api/jobs', []);
        $response->assertStatus(401);

        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $job = Job::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
        ]);

        $response = $this->getJson("/api/jobs/{$job->id}");
        $response->assertStatus(401);

        $response = $this->putJson("/api/jobs/{$job->id}", []);
        $response->assertStatus(401);

        $response = $this->deleteJson("/api/jobs/{$job->id}");
        $response->assertStatus(401);
    }
}
