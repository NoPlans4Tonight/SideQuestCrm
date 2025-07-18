<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Customer;
use App\Models\Job;
use App\Models\Estimate;
use App\Models\Service;
use App\Models\JobService as JobServiceModel;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomerListingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create(['domain' => 'test-' . uniqid()]);
        $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    }

    public function test_index_returns_enriched_data_by_default()
    {
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/customers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'customer' => [
                            'id',
                            'first_name',
                            'last_name',
                            'email'
                        ],
                        'related_data' => [
                            'jobs',
                            'appointments',
                            'estimates',
                            'services',
                            'summary'
                        ]
                    ]
                ],
                'meta'
            ]);
    }

    public function test_index_with_filter_active_jobs()
    {
        $customer1 = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id
        ]);

        $customer2 = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id
        ]);

        // Customer 1 has active job
        Job::factory()->create([
            'customer_id' => $customer1->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'in_progress'
        ]);

        // Customer 2 has completed job (should not be included)
        Job::factory()->create([
            'customer_id' => $customer2->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'completed'
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/customers?filter=active_jobs');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($customer1->id, $data[0]['customer']['id']);
    }

    public function test_index_with_filter_pending_estimates()
    {
        $customer1 = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id
        ]);

        $customer2 = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id
        ]);

        // Customer 1 has pending estimate
        Estimate::factory()->create([
            'customer_id' => $customer1->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'sent'
        ]);

        // Customer 2 has accepted estimate (should not be included)
        Estimate::factory()->create([
            'customer_id' => $customer2->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'accepted'
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/customers?filter=pending_estimates');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($customer1->id, $data[0]['customer']['id']);
    }

    public function test_index_with_filter_has_services()
    {
        $customer1 = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id
        ]);

        $customer2 = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id
        ]);

        $service = Service::factory()->create([
            'tenant_id' => $this->tenant->id
        ]);

        // Customer 1 has job with services
        $job = Job::factory()->create([
            'customer_id' => $customer1->id,
            'tenant_id' => $this->tenant->id
        ]);

        JobServiceModel::factory()->create([
            'job_id' => $job->id,
            'service_id' => $service->id
        ]);

        // Customer 2 has job without services
        Job::factory()->create([
            'customer_id' => $customer2->id,
            'tenant_id' => $this->tenant->id
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/customers?filter=has_services');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($customer1->id, $data[0]['customer']['id']);
    }

    public function test_index_without_enrichment()
    {
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/customers?include_related=false');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'first_name',
                        'last_name',
                        'email'
                    ]
                ],
                'meta'
            ]);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($customer->id, $data[0]['id']);
    }
}
