<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class CustomerCreateUpdateTest extends TestCase
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

    public function test_can_create_customer_with_valid_data()
    {
        $customerData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '123-456-7890',
            'address' => '123 Main St',
            'city' => 'Anytown',
            'state' => 'CA',
            'zip_code' => '90210',
            'notes' => 'Test customer',
            'status' => 'active'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/customers', $customerData);

        $response->assertStatus(201);
        $response->assertJsonStructure([
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
                'notes',
                'status',
                'created_at',
                'updated_at'
            ]
        ]);

        $this->assertDatabaseHas('customers', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id
        ]);
    }

    public function test_can_create_customer_with_minimal_data()
    {
        $customerData = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/customers', $customerData);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'first_name',
                'last_name',
                'email'
            ]
        ]);

        $this->assertDatabaseHas('customers', [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id
        ]);
    }

    public function test_cannot_create_customer_without_required_fields()
    {
        $customerData = [
            'email' => 'test@example.com'
            // Missing first_name and last_name
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/customers', $customerData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['first_name', 'last_name']);
    }

    public function test_cannot_create_customer_with_invalid_email()
    {
        $customerData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'invalid-email'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/customers', $customerData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_cannot_create_customer_with_duplicate_email()
    {
        // Create first customer
        Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email' => 'test@example.com',
            'created_by' => $this->user->id
        ]);

        // Try to create second customer with same email
        $customerData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'test@example.com'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/customers', $customerData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_can_update_customer_with_valid_data()
    {
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com'
        ]);

        $updateData = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'phone' => '987-654-3210',
            'status' => 'inactive'
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/customers/{$customer->id}", $updateData);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'first_name',
                'last_name',
                'email',
                'phone',
                'status',
                'updated_at'
            ]
        ]);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'phone' => '987-654-3210',
            'status' => 'inactive'
        ]);
    }

    public function test_can_update_customer_with_partial_data()
    {
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com'
        ]);

        $updateData = [
            'phone' => '555-123-4567'
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/customers/{$customer->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'phone' => '555-123-4567',
            'first_name' => 'John', // Should remain unchanged
            'last_name' => 'Doe'    // Should remain unchanged
        ]);
    }

    public function test_cannot_update_customer_with_invalid_data()
    {
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id
        ]);

        $updateData = [
            'email' => 'invalid-email'
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/customers/{$customer->id}", $updateData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_cannot_update_customer_with_duplicate_email()
    {
        // Create two customers
        $customer1 = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email' => 'customer1@example.com',
            'created_by' => $this->user->id
        ]);

        $customer2 = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email' => 'customer2@example.com',
            'created_by' => $this->user->id
        ]);

        // Try to update customer2 with customer1's email
        $updateData = [
            'email' => 'customer1@example.com'
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/customers/{$customer2->id}", $updateData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_can_update_customer_with_same_email()
    {
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email' => 'test@example.com',
            'created_by' => $this->user->id
        ]);

        $updateData = [
            'email' => 'test@example.com' // Same email should be allowed
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/customers/{$customer->id}", $updateData);

        $response->assertStatus(200);
    }

    public function test_returns_404_when_updating_nonexistent_customer()
    {
        $updateData = [
            'first_name' => 'John',
            'last_name' => 'Doe'
        ];

        $response = $this->actingAs($this->user)
            ->putJson('/api/customers/99999', $updateData);

        $response->assertStatus(404);
    }

    public function test_tenant_isolation_for_customer_creation()
    {
        // Create another tenant and user
        $otherTenant = Tenant::factory()->create();
        $otherUser = User::factory()->create(['tenant_id' => $otherTenant->id]);

        $customerData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com'
        ];

        // Create customer with other user
        $response = $this->actingAs($otherUser)
            ->postJson('/api/customers', $customerData);

        $response->assertStatus(201);

        // Verify customer belongs to other tenant
        $this->assertDatabaseHas('customers', [
            'email' => 'john.doe@example.com',
            'tenant_id' => $otherTenant->id,
            'created_by' => $otherUser->id
        ]);

        // Verify customer does not belong to first tenant
        $this->assertDatabaseMissing('customers', [
            'email' => 'john.doe@example.com',
            'tenant_id' => $this->tenant->id
        ]);
    }

    public function test_tenant_isolation_for_customer_updates()
    {
        // Create customer in other tenant
        $otherTenant = Tenant::factory()->create();
        $otherUser = User::factory()->create(['tenant_id' => $otherTenant->id]);
        $otherCustomer = Customer::factory()->create([
            'tenant_id' => $otherTenant->id,
            'created_by' => $otherUser->id
        ]);

        // Try to update customer from different tenant
        $updateData = [
            'first_name' => 'Hacked'
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/customers/{$otherCustomer->id}", $updateData);

        $response->assertStatus(404);
    }
}
