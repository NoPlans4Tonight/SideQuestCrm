<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\CustomerCrudOrchestratorService;
use App\Services\CustomerService;
use App\Models\Customer;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Mockery;

class CustomerCrudOrchestratorServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->customerService = Mockery::mock(CustomerService::class);
        $this->orchestrator = new CustomerCrudOrchestratorService($this->customerService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_customer_returns_success_response()
    {
        // Arrange
        $request = new Request();
        $request->merge([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '123-456-7890'
        ]);

        $customer = Customer::factory()->make([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '123-456-7890'
        ]);

        $this->customerService->shouldReceive('createCustomer')
            ->once()
            ->with($request->all(), $this->tenant->id, $this->user->id)
            ->andReturn($customer);

        // Act
        $result = $this->orchestrator->createCustomer($request, $this->tenant->id, $this->user->id);

        // Assert
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals('Customer created successfully', $result['message']);
        $this->assertEquals($customer, $result['data']);
    }

    public function test_create_customer_throws_validation_exception()
    {
        // Arrange
        $request = new Request();
        $request->merge([
            'first_name' => '', // Invalid: empty first name
            'email' => 'invalid-email' // Invalid email format
        ]);

        $validationException = new ValidationException(
            validator([], ['first_name' => 'required', 'email' => 'email'])
        );

        $this->customerService->shouldReceive('createCustomer')
            ->once()
            ->with($request->all(), $this->tenant->id, $this->user->id)
            ->andThrow($validationException);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->orchestrator->createCustomer($request, $this->tenant->id, $this->user->id);
    }

    public function test_update_customer_returns_success_response()
    {
        // Arrange
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com'
        ]);

        $request = new Request();
        $request->merge([
            'first_name' => 'Jane',
            'last_name' => 'Smith'
        ]);

        $updatedCustomer = $customer->replicate();
        $updatedCustomer->first_name = 'Jane';
        $updatedCustomer->last_name = 'Smith';

        $this->customerService->shouldReceive('updateCustomer')
            ->once()
            ->with($customer->id, $request->all())
            ->andReturn($updatedCustomer);

        // Act
        $result = $this->orchestrator->updateCustomer($customer->id, $request);

        // Assert
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals('Customer updated successfully', $result['message']);
        $this->assertInstanceOf(\App\Http\Resources\CustomerResource::class, $result['data']);
    }

    public function test_update_customer_throws_404_when_customer_not_found()
    {
        // Arrange
        $nonExistentId = 99999;
        $request = new Request();
        $request->merge(['first_name' => 'Jane']);

        // Act & Assert
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
        $this->orchestrator->updateCustomer($nonExistentId, $request);
    }

    public function test_update_customer_throws_validation_exception()
    {
        // Arrange
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id
        ]);

        $request = new Request();
        $request->merge([
            'email' => 'invalid-email' // Invalid email format
        ]);

        $validationException = new ValidationException(
            validator([], ['email' => 'email'])
        );

        $this->customerService->shouldReceive('updateCustomer')
            ->once()
            ->with($customer->id, $request->all())
            ->andThrow($validationException);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->orchestrator->updateCustomer($customer->id, $request);
    }

    public function test_delete_customer_returns_success_response()
    {
        // Arrange
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id
        ]);

        $this->customerService->shouldReceive('deleteCustomer')
            ->once()
            ->with($customer->id)
            ->andReturn(true);

        // Act
        $result = $this->orchestrator->deleteCustomer($customer->id);

        // Assert
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals('Customer deleted successfully', $result['message']);
    }

    public function test_delete_customer_throws_404_when_customer_not_found()
    {
        // Arrange
        $nonExistentId = 99999;

        // Act & Assert
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
        $this->orchestrator->deleteCustomer($nonExistentId);
    }

    public function test_find_customer_or_fail_returns_customer_when_exists()
    {
        // Arrange
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id
        ]);

        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->orchestrator);
        $method = $reflection->getMethod('findCustomerOrFail');
        $method->setAccessible(true);

        // Act
        $result = $method->invoke($this->orchestrator, $customer->id);

        // Assert
        $this->assertInstanceOf(Customer::class, $result);
        $this->assertEquals($customer->id, $result->id);
    }

    public function test_find_customer_or_fail_throws_404_when_customer_not_exists()
    {
        // Arrange
        $nonExistentId = 99999;

        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->orchestrator);
        $method = $reflection->getMethod('findCustomerOrFail');
        $method->setAccessible(true);

        // Act & Assert
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
        $method->invoke($this->orchestrator, $nonExistentId);
    }

    public function test_update_customer_with_complete_data()
    {
        // Arrange
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '123-456-7890'
        ]);

        $request = new Request();
        $request->merge([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
            'phone' => '987-654-3210',
            'address' => '123 Main St',
            'city' => 'Anytown',
            'state' => 'CA',
            'zip_code' => '90210',
            'status' => 'active'
        ]);

        $updatedCustomer = $customer->replicate();
        $updatedCustomer->fill($request->all());

        $this->customerService->shouldReceive('updateCustomer')
            ->once()
            ->with($customer->id, $request->all())
            ->andReturn($updatedCustomer);

        // Act
        $result = $this->orchestrator->updateCustomer($customer->id, $request);

        // Assert
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals('Customer updated successfully', $result['message']);
        $this->assertInstanceOf(\App\Http\Resources\CustomerResource::class, $result['data']);
    }

    public function test_create_customer_with_minimal_data()
    {
        // Arrange
        $request = new Request();
        $request->merge([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com'
        ]);

        $customer = Customer::factory()->make([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com'
        ]);

        $this->customerService->shouldReceive('createCustomer')
            ->once()
            ->with($request->all(), $this->tenant->id, $this->user->id)
            ->andReturn($customer);

        // Act
        $result = $this->orchestrator->createCustomer($request, $this->tenant->id, $this->user->id);

        // Assert
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals('Customer created successfully', $result['message']);
        $this->assertEquals($customer, $result['data']);
    }
}
