<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use App\Models\Job;
use App\Models\Appointment;
use App\Models\Estimate;
use App\Models\Service;
use App\Models\JobService as JobServiceModel;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class CustomerControllerEnrichmentTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_customer_index_with_enriched_data()
    {
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id
        ]);

        $job = Job::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'in_progress',
            'materials_cost' => 600.00,
            'labor_cost' => 400.00
        ]);

        $appointment = Appointment::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'confirmed',
            'start_time' => now()->addDays(1)
        ]);

        $estimate = Estimate::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'draft',
            'subtotal' => 500.00,
            'tax_amount' => 0,
            'discount_amount' => 0
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/customers?include_related=true');

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
                            'jobs' => [
                                'has_jobs',
                                'total_count',
                                'jobs',
                                'status_breakdown',
                                'total_value'
                            ],
                            'appointments' => [
                                'has_appointments',
                                'total_count',
                                'appointments',
                                'status_breakdown',
                                'upcoming_count'
                            ],
                            'estimates' => [
                                'has_estimates',
                                'total_count',
                                'estimates',
                                'status_breakdown',
                                'total_value',
                                'pending_value'
                            ],
                            'services' => [
                                'has_services',
                                'total_count',
                                'services',
                                'unique_services'
                            ],
                            'summary' => [
                                'total_jobs',
                                'active_jobs',
                                'completed_jobs',
                                'total_appointments',
                                'upcoming_appointments',
                                'total_estimates',
                                'pending_estimates',
                                'accepted_estimates',
                                'total_job_value',
                                'total_estimate_value',
                                'pending_estimate_value',
                                'last_activity',
                                'customer_since'
                            ]
                        ]
                    ]
                ],
                'meta'
            ]);

        $customerData = $response->json('data.0');
        $this->assertTrue($customerData['related_data']['jobs']['has_jobs']);
        $this->assertEquals(1, $customerData['related_data']['jobs']['total_count']);
        $this->assertEquals(1000.00, $customerData['related_data']['jobs']['total_value']);

        $this->assertTrue($customerData['related_data']['appointments']['has_appointments']);
        $this->assertEquals(1, $customerData['related_data']['appointments']['total_count']);
        $this->assertEquals(1, $customerData['related_data']['appointments']['upcoming_count']);
    }

    public function test_customer_show_with_enriched_data()
    {
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id
        ]);

        $job = Job::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'in_progress',
            'materials_cost' => 600.00,
            'labor_cost' => 400.00
        ]);

        $estimate = Estimate::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'draft',
            'subtotal' => 500.00,
            'tax_amount' => 0,
            'discount_amount' => 0
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/customers/{$customer->id}?include_related=true");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'customer',
                    'related_data' => [
                        'jobs',
                        'appointments',
                        'estimates',
                        'services',
                        'summary'
                    ]
                ]
            ]);

        $data = $response->json('data');
        $this->assertTrue($data['related_data']['jobs']['has_jobs']);
        $this->assertTrue($data['related_data']['estimates']['has_estimates']);
        $this->assertEquals(1000.00, $data['related_data']['summary']['total_job_value']);
        $this->assertEquals(500.00, $data['related_data']['summary']['pending_estimate_value']);
    }

    public function test_customer_show_without_enriched_data()
    {
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/customers/{$customer->id}?include_related=false");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'phone',
                    'address',
                    'city',
                    'state',
                    'zip_code',
                    'country',
                    'notes',
                    'status',
                    'source',
                    'assigned_to',
                    'created_by',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    public function test_customer_summary_endpoint()
    {
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id
        ]);

        $job = Job::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'in_progress',
            'materials_cost' => 600.00,
            'labor_cost' => 400.00
        ]);

        $estimate = Estimate::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'draft',
            'subtotal' => 500.00,
            'tax_amount' => 0,
            'discount_amount' => 0
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/customers/{$customer->id}/summary");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'customer',
                    'summary',
                    'related_data'
                ]
            ]);

        $data = $response->json('data');
        $this->assertEquals(1, $data['summary']['total_jobs']);
        $this->assertEquals(1, $data['summary']['active_jobs']);
        $this->assertEquals(1, $data['summary']['total_estimates']);
        $this->assertEquals(1, $data['summary']['pending_estimates']);
        $this->assertEquals(1000.00, $data['summary']['total_job_value']);
        $this->assertEquals(500.00, $data['summary']['pending_estimate_value']);
    }

    public function test_customers_with_active_jobs()
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
            'status' => 'in_progress',
            'materials_cost' => 600.00,
            'labor_cost' => 400.00
        ]);

        // Customer 2 has completed job (should not be included)
        Job::factory()->create([
            'customer_id' => $customer2->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'completed'
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/customers?filter=active_jobs');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta'
            ]);

        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($customer1->id, $response->json('data.0.customer.id'));
    }

    public function test_customers_with_pending_estimates()
    {
        $customer1 = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id
        ]);
        $customer2 = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id
        ]);

        // Customer 1 has draft estimate (pending)
        Estimate::factory()->create([
            'customer_id' => $customer1->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'draft',
            'subtotal' => 500.00,
            'tax_amount' => 0,
            'discount_amount' => 0
        ]);

        // Customer 2 has accepted estimate (not pending)
        Estimate::factory()->create([
            'customer_id' => $customer2->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'accepted'
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/customers?filter=pending_estimates');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta'
            ]);

        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($customer1->id, $response->json('data.0.customer.id'));
    }

    public function test_customer_with_services_data()
    {
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id
        ]);

        $service = Service::factory()->create([
            'tenant_id' => $this->tenant->id,
            'hourly_rate' => 0
        ]);

        $job = Job::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id
        ]);

        JobServiceModel::factory()->create([
            'job_id' => $job->id,
            'service_id' => $service->id,
            'quantity' => 2,
            'unit_price' => 100.00,
            'hours_worked' => 0,
            'description' => 'Test Service'
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/customers/{$customer->id}?include_related=true");

        $response->assertStatus(200);

        $servicesData = $response->json('data.related_data.services');
        $this->assertTrue($servicesData['has_services']);
        $this->assertEquals(1, $servicesData['total_count']);
        $this->assertEquals($service->name, $servicesData['services'][0]['service_name']);

        // Debug: Let's see what the actual total_price is
        $actualTotalPrice = $servicesData['services'][0]['total_price'];
        $this->assertEquals(200.00, $actualTotalPrice, "Expected 200.00 but got {$actualTotalPrice}");
    }

    public function test_customer_not_found()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/customers/999999');

        $response->assertStatus(404)
            ->assertJson(['message' => 'Customer not found']);
    }

    public function test_customer_summary_not_found()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/customers/999999/summary');

        $response->assertStatus(404)
            ->assertJson(['message' => 'Customer not found']);
    }

    public function test_tenant_isolation()
    {
        $otherTenant = Tenant::factory()->create(['domain' => 'isolation-' . uniqid()]);
        $otherCustomer = Customer::factory()->create([
            'tenant_id' => $otherTenant->id
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/customers/{$otherCustomer->id}");

        $response->assertStatus(404)
            ->assertJson(['message' => 'Customer not found']);
    }
}
