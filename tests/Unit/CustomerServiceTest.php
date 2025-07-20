<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Repositories\CustomerRepository;
use App\Services\CustomerService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use Mockery;

class CustomerServiceTest extends TestCase
{
    private CustomerService $service;
    private CustomerRepository $mockRepository;
    private int $tenantId = 1;
    private int $userId = 1;

    protected function setUp(): void
    {
        parent::setUp();

        // Create mock repository
        $this->mockRepository = Mockery::mock(CustomerRepository::class);
        $this->service = new CustomerService($this->mockRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_customers_returns_paginated_customers(): void
    {
        // Arrange
        $mockPaginator = Mockery::mock(LengthAwarePaginator::class);

        $this->mockRepository->shouldReceive('paginateByTenant')
            ->once()
            ->with($this->tenantId, 10, [])
            ->andReturn($mockPaginator);

        // Act
        $result = $this->service->getCustomers($this->tenantId, 10);

        // Assert
        $this->assertSame($mockPaginator, $result);
    }

    public function test_get_customer_returns_customer_when_exists(): void
    {
        // Arrange
        $customerId = 1;
        $mockCustomer = Mockery::mock(Customer::class);

        $this->mockRepository->shouldReceive('findById')
            ->once()
            ->with($customerId)
            ->andReturn($mockCustomer);

        // Act
        $result = $this->service->getCustomer($customerId);

        // Assert
        $this->assertSame($mockCustomer, $result);
    }

    public function test_get_customer_returns_null_when_not_exists(): void
    {
        // Arrange
        $customerId = 999;

        $this->mockRepository->shouldReceive('findById')
            ->once()
            ->with($customerId)
            ->andReturn(null);

        // Act
        $result = $this->service->getCustomer($customerId);

        // Assert
        $this->assertNull($result);
    }

    public function test_create_customer_creates_and_returns_customer(): void
    {
        // Arrange
        $inputData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
        ];

        $expectedData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'tenant_id' => $this->tenantId,
            'created_by' => $this->userId,
        ];

        $mockCustomer = Mockery::mock(Customer::class);

        $this->mockRepository->shouldReceive('create')
            ->once()
            ->with($expectedData)
            ->andReturn($mockCustomer);

        // Act
        $result = $this->service->createCustomer($inputData, $this->tenantId, $this->userId);

        // Assert
        $this->assertSame($mockCustomer, $result);
    }

    public function test_create_customer_throws_validation_exception_for_invalid_data(): void
    {
        // Arrange
        $invalidData = [
            'first_name' => '', // Required field is empty
            'last_name' => 'Doe',
        ];

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->service->createCustomer($invalidData, $this->tenantId, $this->userId);
    }

    public function test_create_customer_throws_validation_exception_for_duplicate_email(): void
    {
        // We can't easily test this without database interaction in the validation
        // This would require mocking the validation itself, which might be overkill
        // Better tested in integration tests
        $this->markTestSkipped('Email uniqueness validation requires database - test in integration tests');
    }

    public function test_update_customer_updates_and_returns_customer(): void
    {
        // Arrange
        $customerId = 1;
        $updateData = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
        ];

        $mockCustomer = Mockery::mock(Customer::class);
        $updatedCustomer = Mockery::mock(Customer::class);

        $this->mockRepository->shouldReceive('findById')
            ->once()
            ->with($customerId)
            ->andReturn($mockCustomer);

        $this->mockRepository->shouldReceive('update')
            ->once()
            ->with($mockCustomer, $updateData)
            ->andReturn($updatedCustomer);

        // Act
        $result = $this->service->updateCustomer($customerId, $updateData);

        // Assert
        $this->assertSame($updatedCustomer, $result);
    }

    public function test_update_customer_throws_exception_when_customer_not_found(): void
    {
        // Arrange
        $customerId = 999;
        $updateData = ['first_name' => 'Jane'];

        $this->mockRepository->shouldReceive('findById')
            ->once()
            ->with($customerId)
            ->andReturn(null);

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Customer not found');

        $this->service->updateCustomer($customerId, $updateData);
    }

    public function test_update_customer_throws_validation_exception_for_invalid_data(): void
    {
        // Arrange
        $customerId = 1;
        $invalidData = [
            'email' => 'invalid-email-format',
        ];

        $mockCustomer = Mockery::mock(Customer::class);

        $this->mockRepository->shouldReceive('findById')
            ->once()
            ->with($customerId)
            ->andReturn($mockCustomer);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->service->updateCustomer($customerId, $invalidData);
    }

    public function test_delete_customer_deletes_and_returns_true(): void
    {
        // Arrange
        $customerId = 1;
        $mockCustomer = Mockery::mock(Customer::class);

        $this->mockRepository->shouldReceive('findById')
            ->once()
            ->with($customerId)
            ->andReturn($mockCustomer);

        $this->mockRepository->shouldReceive('delete')
            ->once()
            ->with($mockCustomer)
            ->andReturn(true);

        // Act
        $result = $this->service->deleteCustomer($customerId);

        // Assert
        $this->assertTrue($result);
    }

    public function test_delete_customer_throws_exception_when_customer_not_found(): void
    {
        // Arrange
        $customerId = 999;

        $this->mockRepository->shouldReceive('findById')
            ->once()
            ->with($customerId)
            ->andReturn(null);

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Customer not found');

        $this->service->deleteCustomer($customerId);
    }

    public function test_validate_customer_data_returns_validated_data(): void
    {
        // Arrange
        $validData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
        ];

        // Act
        $result = $this->service->validateCustomerData($validData);

        // Assert
        $this->assertEquals($validData, $result);
    }

    public function test_validate_customer_data_throws_exception_for_invalid_data(): void
    {
        // Arrange
        $invalidData = [
            'first_name' => '', // Required
            'last_name' => 'Doe',
            'email' => 'invalid-email',
        ];

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->service->validateCustomerData($invalidData);
    }
}
