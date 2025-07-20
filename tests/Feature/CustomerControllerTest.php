<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerControllerTest extends TestCase
{
    use RefreshDatabase;
    public function test_index_returns_paginated_customers(): void
    {
        $this->authenticateUser();

        Customer::factory()->count(20)->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $response = $this->getJson('/api/customers?include_related=false');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                        'phone',
                        'status',
                        'created_at',
                        'updated_at',
                    ]
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                    'from',
                    'to',
                ]
            ]);

        $this->assertCount(15, $response->json('data')); // Default per page
        $this->assertEquals(20, $response->json('meta.total'));
    }

    public function test_store_creates_new_customer(): void
    {
        $this->authenticateUser();

        $customerData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'address' => '123 Main St',
            'city' => 'Anytown',
            'state' => 'CA',
            'zip_code' => '12345',
            'country' => 'USA',
            'notes' => 'Test customer',
            'status' => 'active',
            'source' => 'website',
        ];

        $response = $this->postJson('/api/customers', $customerData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
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
                    'created_at',
                    'updated_at',
                ]
            ])
            ->assertJson([
                'message' => 'Customer created successfully',
                'data' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'email' => 'john@example.com',
                ]
            ]);

        $this->assertDatabaseHas('customers', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
        ]);
    }

    public function test_store_returns_validation_errors_for_invalid_data(): void
    {
        $this->authenticateUser();

        $invalidData = [
            'first_name' => '', // Required field is empty
            'last_name' => 'Doe',
            'email' => 'invalid-email', // Invalid email format
        ];

        $response = $this->postJson('/api/customers', $invalidData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'first_name',
                    'email',
                ]
            ]);
    }

    public function test_store_returns_validation_error_for_duplicate_email(): void
    {
        $this->authenticateUser();

        Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email' => 'john@example.com',
        ]);

        $customerData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com', // Duplicate email
        ];

        $response = $this->postJson('/api/customers', $customerData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'email',
                ]
            ]);
    }

    public function test_show_returns_customer_with_related_data(): void
    {
        $this->authenticateUser();

        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $response = $this->getJson("/api/customers/{$customer->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'customer' => [
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                        'phone',
                        'status',
                        'created_at',
                        'updated_at',
                    ],
                    'related_data' => [
                        'appointments',
                        'estimates',
                        'services',
                        'summary',
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    'customer' => [
                        'id' => $customer->id,
                        'first_name' => $customer->first_name,
                        'last_name' => $customer->last_name,
                    ]
                ]
            ]);
    }

    public function test_show_returns_404_for_nonexistent_customer(): void
    {
        $this->authenticateUser();

        $response = $this->getJson('/api/customers/999');

        $response->assertStatus(404);
    }

    public function test_update_modifies_customer(): void
    {
        $this->authenticateUser();

        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $updateData = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
            'phone' => '0987654321',
            'status' => 'inactive',
        ];

        $response = $this->putJson("/api/customers/{$customer->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'phone',
                    'status',
                    'created_at',
                    'updated_at',
                ]
            ])
            ->assertJson([
                'message' => 'Customer updated successfully',
                'data' => [
                    'first_name' => 'Jane',
                    'last_name' => 'Smith',
                    'email' => 'jane@example.com',
                    'phone' => '0987654321',
                    'status' => 'inactive',
                ]
            ]);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
        ]);
    }

    public function test_update_returns_validation_errors_for_invalid_data()
    {
        $this->authenticateUser();

        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id
        ]);

        $invalidData = [
            'email' => 'invalid-email'
        ];

        $response = $this->putJson("/api/customers/{$customer->id}", $invalidData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'email'
                ]
            ]);
    }

    public function test_update_returns_404_for_nonexistent_customer(): void
    {
        $this->authenticateUser();

        $updateData = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
        ];

        $response = $this->putJson('/api/customers/999', $updateData);

        $response->assertStatus(404);
    }

    public function test_destroy_deletes_customer(): void
    {
        $this->authenticateUser();

        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $response = $this->deleteJson("/api/customers/{$customer->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Customer deleted successfully'
            ]);

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    public function test_destroy_returns_404_for_nonexistent_customer(): void
    {
        $this->authenticateUser();

        $response = $this->deleteJson('/api/customers/999');

        $response->assertStatus(404);
    }

    public function test_unauthenticated_requests_are_rejected(): void
    {
        // Create tenant for test data without authenticating
        $tenant = Tenant::factory()->create();

        $response = $this->getJson('/api/customers');
        $response->assertStatus(401);

        $response = $this->postJson('/api/customers', []);
        $response->assertStatus(401);

        $customer = Customer::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        $response = $this->getJson("/api/customers/{$customer->id}");
        $response->assertStatus(401);

        $response = $this->putJson("/api/customers/{$customer->id}", []);
        $response->assertStatus(401);

        $response = $this->deleteJson("/api/customers/{$customer->id}");
        $response->assertStatus(401);
    }
}
