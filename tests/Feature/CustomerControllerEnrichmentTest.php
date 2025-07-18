<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Customer;
use App\Models\Appointment;
use App\Models\Estimate;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomerControllerEnrichmentTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create(['domain' => 'test-enrichment-' . uniqid()]);
        $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    }

    public function test_customer_index_with_enriched_data()
    {
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id
        ]);

        $appointment = Appointment::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'confirmed',
            'start_time' => now()->addDays(1),
            'total_cost' => 1000.00
        ]);

        $estimate = Estimate::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'draft',
            'subtotal' => 500.00,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 500.00
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
                                'total_appointments',
                                'upcoming_appointments',
                                'completed_appointments',
                                'total_estimates',
                                'pending_estimates',
                                'accepted_estimates',
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
        $this->assertTrue($customerData['related_data']['appointments']['has_appointments']);
        $this->assertEquals(1, $customerData['related_data']['appointments']['total_count']);
        $this->assertEquals(1, $customerData['related_data']['appointments']['upcoming_count']);

        $this->assertTrue($customerData['related_data']['estimates']['has_estimates']);
        $this->assertEquals(1, $customerData['related_data']['estimates']['total_count']);
        $this->assertEquals(500.00, $customerData['related_data']['estimates']['total_value']);
    }

    public function test_customer_show_with_enriched_data()
    {
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id
        ]);

        $appointment = Appointment::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'confirmed',
            'start_time' => now()->addDays(1),
            'total_cost' => 1000.00
        ]);

        $estimate = Estimate::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'draft',
            'subtotal' => 500.00,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 500.00
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/customers/{$customer->id}?include_related=true");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'customer',
                    'related_data' => [
                        'appointments',
                        'estimates',
                        'services',
                        'summary'
                    ]
                ]
            ]);

        $data = $response->json('data');
        $this->assertTrue($data['related_data']['appointments']['has_appointments']);
        $this->assertTrue($data['related_data']['estimates']['has_estimates']);
        $this->assertEquals(1000.00, $data['related_data']['summary']['total_appointment_value']);
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

        $appointment = Appointment::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'confirmed',
            'start_time' => now()->addDays(1),
            'total_cost' => 1000.00
        ]);

        $estimate = Estimate::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'draft',
            'subtotal' => 500.00,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 500.00
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/customers/{$customer->id}/summary");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total_appointments',
                    'upcoming_appointments',
                    'completed_appointments',
                    'total_estimates',
                    'pending_estimates',
                    'accepted_estimates',
                    'total_appointment_value',
                    'total_estimate_value',
                    'pending_estimate_value',
                    'last_activity',
                    'customer_since'
                ]
            ]);

        $data = $response->json('data');
        $this->assertEquals(1, $data['total_appointments']);
        $this->assertEquals(1, $data['upcoming_appointments']);
        $this->assertEquals(1, $data['total_estimates']);
        $this->assertEquals(1, $data['pending_estimates']);
        $this->assertEquals(1000.00, $data['total_appointment_value']);
        $this->assertEquals(500.00, $data['total_estimate_value']);
    }

    public function test_customers_with_active_appointments()
    {
        $customer1 = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id
        ]);
        $customer2 = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id
        ]);

        // Customer 1 has active appointment
        Appointment::factory()->create([
            'customer_id' => $customer1->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'confirmed',
            'start_time' => now()->addDays(1)
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/customers?include_related=true&filter=active_appointments');

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertCount(1, $data);
        $this->assertEquals($customer1->id, $data[0]['customer']['id']);
        $this->assertTrue($data[0]['related_data']['appointments']['has_appointments']);
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

        // Customer 1 has pending estimate
        Estimate::factory()->create([
            'customer_id' => $customer1->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'pending',
            'subtotal' => 500.00,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 500.00
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/customers?include_related=true&filter=pending_estimates');

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertCount(1, $data);
        $this->assertEquals($customer1->id, $data[0]['customer']['id']);
        $this->assertTrue($data[0]['related_data']['estimates']['has_estimates']);
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

        $appointment = Appointment::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $this->tenant->id,
            'service_id' => $service->id
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/customers/{$customer->id}?include_related=true");

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertTrue($data['related_data']['services']['has_services']);
        $this->assertEquals(1, $data['related_data']['services']['total_count']);
        $this->assertEquals($service->name, $data['related_data']['services']['services'][0]['service_name']);
    }

    public function test_customer_not_found()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/customers/999999?include_related=true');

        $response->assertStatus(404);
    }

    public function test_customer_summary_not_found()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/customers/999999/summary');

        $response->assertStatus(404);
    }

    public function test_tenant_isolation()
    {
        $otherTenant = Tenant::factory()->create(['domain' => 'other-tenant-' . uniqid()]);
        $otherUser = User::factory()->create(['tenant_id' => $otherTenant->id]);
        $otherCustomer = Customer::factory()->create(['tenant_id' => $otherTenant->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/customers/{$otherCustomer->id}?include_related=true");

        $response->assertStatus(404);
    }
}
