<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CustomerService;
use App\Repositories\CustomerRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CustomerServiceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private CustomerService $service;
    protected Tenant $tenant;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new CustomerService(new CustomerRepository());
        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    }

    public function test_create_customer_with_duplicate_email_throws_validation_exception(): void
    {
        // Arrange - Create existing customer
        Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email' => 'john@example.com',
        ]);

        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com', // Duplicate email
        ];

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->service->createCustomer($data, $this->tenant->id, $this->user->id);
    }

    public function test_update_customer_allows_same_email_for_same_customer(): void
    {
        // Arrange
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email' => 'john@example.com',
        ]);

        $updateData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com', // Same email should be allowed
        ];

        // Act
        $result = $this->service->updateCustomer($customer->id, $updateData);

        // Assert
        $this->assertInstanceOf(Customer::class, $result);
        $this->assertEquals('john@example.com', $result->email);
    }

    public function test_update_customer_with_duplicate_email_throws_validation_exception(): void
    {
        // Arrange - Create two customers
        $customer1 = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email' => 'customer1@example.com',
        ]);

        $customer2 = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email' => 'customer2@example.com',
        ]);

        $updateData = [
            'email' => 'customer1@example.com', // Try to use customer1's email
        ];

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->service->updateCustomer($customer2->id, $updateData);
    }

    public function test_email_uniqueness_validation_for_new_customer(): void
    {
        // Arrange
        Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email' => 'existing@example.com',
        ]);

        $data = [
            'first_name' => 'New',
            'last_name' => 'Customer',
            'email' => 'existing@example.com',
        ];

        // Act & Assert - Test through the full create flow
        $this->expectException(ValidationException::class);
        $this->service->createCustomer($data, $this->tenant->id, $this->user->id);
    }

    public function test_email_uniqueness_validation_allows_same_email_for_existing_customer(): void
    {
        // Arrange
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email' => 'existing@example.com',
        ]);

        $data = [
            'first_name' => 'Updated',
            'last_name' => 'Customer',
            'email' => 'existing@example.com',
        ];

        // Act - Test through the full update flow
        $result = $this->service->updateCustomer($customer->id, $data);

        // Assert
        $this->assertInstanceOf(Customer::class, $result);
        $this->assertEquals('existing@example.com', $result->email);
    }
}
