<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\User;
use App\Models\Tenant;
use App\Repositories\CustomerRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class CustomerRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private CustomerRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new CustomerRepository();
    }

    public function test_find_by_id_returns_customer_when_exists(): void
    {
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $result = $this->repository->findById($customer->id);

        $this->assertInstanceOf(Customer::class, $result);
        $this->assertEquals($customer->id, $result->id);
    }

    public function test_find_by_id_returns_null_when_customer_not_exists(): void
    {
        $result = $this->repository->findById(999);

        $this->assertNull($result);
    }

    public function test_find_by_tenant_returns_customers_for_tenant(): void
    {
        // Create customers for current tenant
        Customer::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
        ]);

        // Create customer for different tenant
        $otherTenant = Tenant::factory()->create();
        Customer::factory()->create([
            'tenant_id' => $otherTenant->id,
        ]);

        $result = $this->repository->findByTenant($this->tenant->id);

        $this->assertCount(3, $result);
        $this->assertTrue($result->every(fn($customer) => $customer->tenant_id === $this->tenant->id));
    }

    public function test_paginate_by_tenant_returns_paginated_customers(): void
    {
        Customer::factory()->count(20)->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $result = $this->repository->paginateByTenant($this->tenant->id, 10);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(20, $result->total());
        $this->assertCount(10, $result->items());
    }

    public function test_create_returns_new_customer(): void
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
        ];

        $result = $this->repository->create($data);

        $this->assertInstanceOf(Customer::class, $result);
        $this->assertEquals('John', $result->first_name);
        $this->assertEquals('Doe', $result->last_name);
        $this->assertEquals('john@example.com', $result->email);
        $this->assertDatabaseHas('customers', $data);
    }

    public function test_update_returns_updated_customer(): void
    {
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $updateData = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
        ];

        $result = $this->repository->update($customer, $updateData);

        $this->assertInstanceOf(Customer::class, $result);
        $this->assertEquals('Jane', $result->first_name);
        $this->assertEquals('Smith', $result->last_name);
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
        ]);
    }

    public function test_delete_returns_true_and_removes_customer(): void
    {
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $result = $this->repository->delete($customer);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    public function test_find_by_email_returns_customer_when_exists(): void
    {
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email' => 'test@example.com',
        ]);

        $result = $this->repository->findByEmail('test@example.com', $this->tenant->id);

        $this->assertInstanceOf(Customer::class, $result);
        $this->assertEquals($customer->id, $result->id);
    }

    public function test_find_by_email_returns_null_when_not_exists(): void
    {
        $result = $this->repository->findByEmail('nonexistent@example.com', $this->tenant->id);

        $this->assertNull($result);
    }

    public function test_search_by_tenant_returns_matching_customers(): void
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

        $result = $this->repository->searchByTenant($this->tenant->id, 'john');

        $this->assertCount(1, $result);
        $this->assertEquals('John', $result->first()->first_name);
    }

    public function test_get_by_status_returns_customers_with_status(): void
    {
        Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'active',
        ]);

        Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'inactive',
        ]);

        $result = $this->repository->getByStatus($this->tenant->id, 'active');

        $this->assertCount(1, $result);
        $this->assertEquals('active', $result->first()->status);
    }

    public function test_get_by_assigned_user_returns_customers_assigned_to_user(): void
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

        $result = $this->repository->getByAssignedUser($this->tenant->id, $assignedUser->id);

        $this->assertCount(1, $result);
        $this->assertEquals($assignedUser->id, $result->first()->assigned_to);
    }
}
