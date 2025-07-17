<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\User;
use App\Repositories\CustomerRepository;
use App\Services\CustomerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CustomerServiceTest extends TestCase
{
    use RefreshDatabase;

    private CustomerService $service;
    private CustomerRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new CustomerRepository();
        $this->service = new CustomerService($this->repository);
    }

    public function test_get_customers_returns_paginated_customers(): void
    {
        Customer::factory()->count(20)->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $result = $this->service->getCustomers($this->tenant->id, 10);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(20, $result->total());
    }

    public function test_get_customer_returns_customer_when_exists(): void
    {
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $result = $this->service->getCustomer($customer->id);

        $this->assertInstanceOf(Customer::class, $result);
        $this->assertEquals($customer->id, $result->id);
    }

    public function test_get_customer_returns_null_when_not_exists(): void
    {
        $result = $this->service->getCustomer(999);

        $this->assertNull($result);
    }

    public function test_create_customer_creates_and_returns_customer(): void
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
        ];

        $result = $this->service->createCustomer($data, $this->tenant->id, $this->user->id);

        $this->assertInstanceOf(Customer::class, $result);
        $this->assertEquals('John', $result->first_name);
        $this->assertEquals('Doe', $result->last_name);
        $this->assertEquals('john@example.com', $result->email);
        $this->assertEquals($this->tenant->id, $result->tenant_id);
        $this->assertEquals($this->user->id, $result->created_by);
    }

    public function test_create_customer_throws_validation_exception_for_invalid_data(): void
    {
        $this->expectException(ValidationException::class);

        $data = [
            'first_name' => '', // Required field is empty
            'last_name' => 'Doe',
        ];

        $this->service->createCustomer($data, $this->tenant->id, $this->user->id);
    }

    public function test_create_customer_throws_validation_exception_for_duplicate_email(): void
    {
        Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email' => 'john@example.com',
        ]);

        $this->expectException(ValidationException::class);

        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com', // Duplicate email
        ];

        $this->service->createCustomer($data, $this->tenant->id, $this->user->id);
    }

    public function test_update_customer_updates_and_returns_customer(): void
    {
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $updateData = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
        ];

        $result = $this->service->updateCustomer($customer->id, $updateData);

        $this->assertInstanceOf(Customer::class, $result);
        $this->assertEquals('Jane', $result->first_name);
        $this->assertEquals('Smith', $result->last_name);
        $this->assertEquals('jane@example.com', $result->email);
    }

    public function test_update_customer_throws_exception_when_customer_not_found(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Customer not found');

        $updateData = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
        ];

        $this->service->updateCustomer(999, $updateData);
    }

    public function test_update_customer_throws_validation_exception_for_invalid_data(): void
    {
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $this->expectException(ValidationException::class);

        $updateData = [
            'first_name' => '', // Required field is empty
            'last_name' => 'Smith',
        ];

        $this->service->updateCustomer($customer->id, $updateData);
    }

    public function test_update_customer_allows_same_email_for_same_customer(): void
    {
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email' => 'john@example.com',
        ]);

        $updateData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com', // Same email should be allowed
        ];

        $result = $this->service->updateCustomer($customer->id, $updateData);

        $this->assertInstanceOf(Customer::class, $result);
        $this->assertEquals('john@example.com', $result->email);
    }

    public function test_delete_customer_deletes_and_returns_true(): void
    {
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $result = $this->service->deleteCustomer($customer->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    public function test_delete_customer_throws_exception_when_customer_not_found(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Customer not found');

        $this->service->deleteCustomer(999);
    }

    public function test_search_customers_returns_matching_customers(): void
    {
        Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
        ]);

        Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
        ]);

        $result = $this->service->searchCustomers($this->tenant->id, 'john');

        $this->assertCount(1, $result);
        $this->assertEquals('John', $result->first()->first_name);
    }

    public function test_get_customers_by_status_returns_customers_with_status(): void
    {
        Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'active',
        ]);

        Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'inactive',
        ]);

        $result = $this->service->getCustomersByStatus($this->tenant->id, 'active');

        $this->assertCount(1, $result);
        $this->assertEquals('active', $result->first()->status);
    }

    public function test_get_customers_by_assigned_user_returns_customers_assigned_to_user(): void
    {
        $assignedUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'assigned_to' => $assignedUser->id,
        ]);

        Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'assigned_to' => null,
        ]);

        $result = $this->service->getCustomersByAssignedUser($this->tenant->id, $assignedUser->id);

        $this->assertCount(1, $result);
        $this->assertEquals($assignedUser->id, $result->first()->assigned_to);
    }

    public function test_validate_customer_data_returns_validated_data(): void
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'status' => 'active',
        ];

        $result = $this->service->validateCustomerData($data);

        $this->assertEquals($data, $result);
    }

    public function test_validate_customer_data_throws_exception_for_invalid_data(): void
    {
        $this->expectException(ValidationException::class);

        $data = [
            'first_name' => '', // Required field is empty
            'last_name' => 'Doe',
            'email' => 'invalid-email', // Invalid email
        ];

        $this->service->validateCustomerData($data);
    }

    public function test_validate_customer_data_validates_email_uniqueness_for_new_customer(): void
    {
        Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email' => 'john@example.com',
        ]);

        $this->expectException(ValidationException::class);

        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com', // Duplicate email
        ];

        $this->service->validateCustomerData($data);
    }

    public function test_validate_customer_data_allows_same_email_for_existing_customer(): void
    {
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email' => 'john@example.com',
        ]);

        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com', // Same email should be allowed
        ];

        $result = $this->service->validateCustomerData($data, $customer->id);

        $this->assertEquals($data, $result);
    }
}
