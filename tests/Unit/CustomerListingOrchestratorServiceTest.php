<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\CustomerListingOrchestratorService;
use App\Services\CustomerService;
use App\Services\CustomerListingService;
use App\Models\Customer;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Mockery;

class CustomerListingOrchestratorServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);

        // Create some test customers
        Customer::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
        ]);

        $this->customerService = Mockery::mock(CustomerService::class);
        $this->listingService = Mockery::mock(CustomerListingService::class);

        $this->orchestrator = new CustomerListingOrchestratorService(
            $this->customerService,
            $this->listingService
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_returns_simple_list_when_simple_parameter_is_true()
    {
        // Arrange
        $request = new Request();
        $request->merge(['simple' => true]);

        $customers = Customer::where('tenant_id', $this->tenant->id)->paginate(1000);

        $this->customerService->shouldReceive('getCustomers')
            ->once()
            ->with($this->tenant->id, 1000)
            ->andReturn($customers);

        // Act
        $result = $this->orchestrator->getCustomersForRequest($request, $this->tenant->id);

        // Assert
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('meta', $result);
        $this->assertCount(3, $result['data']);

        // Check that the data has the expected structure for simple list
        $firstCustomer = $result['data'][0];
        $this->assertArrayHasKey('id', $firstCustomer);
        $this->assertArrayHasKey('first_name', $firstCustomer);
        $this->assertArrayHasKey('last_name', $firstCustomer);
        $this->assertArrayHasKey('full_name', $firstCustomer);
    }

    public function test_returns_enriched_list_when_simple_parameter_is_false()
    {
        // Arrange
        $request = new Request();
        $expectedResult = [
            'data' => [
                [
                    'customer' => ['id' => 1, 'first_name' => 'John', 'last_name' => 'Doe'],
                    'related_data' => ['appointments' => [], 'estimates' => []]
                ]
            ],
            'meta' => ['total' => 1]
        ];

        $this->listingService->shouldReceive('getCustomers')
            ->once()
            ->with($request, $this->tenant->id)
            ->andReturn($expectedResult);

        // Act
        $result = $this->orchestrator->getCustomersForRequest($request, $this->tenant->id);

        // Assert
        $this->assertEquals($expectedResult, $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('meta', $result);
    }

    public function test_returns_enriched_list_when_simple_parameter_is_not_present()
    {
        // Arrange
        $request = new Request();
        $expectedResult = [
            'data' => [
                [
                    'customer' => ['id' => 1, 'first_name' => 'John', 'last_name' => 'Doe'],
                    'related_data' => ['appointments' => [], 'estimates' => []]
                ]
            ],
            'meta' => ['total' => 1]
        ];

        $this->listingService->shouldReceive('getCustomers')
            ->once()
            ->with($request, $this->tenant->id)
            ->andReturn($expectedResult);

        // Act
        $result = $this->orchestrator->getCustomersForRequest($request, $this->tenant->id);

        // Assert
        $this->assertEquals($expectedResult, $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('meta', $result);
    }

    public function test_uses_caching_when_appropriate_conditions_are_met()
    {
        // Arrange
        $request = new Request();
        $request->merge(['per_page' => 15]); // Default value

        $expectedResult = [
            'data' => [
                [
                    'customer' => ['id' => 1, 'first_name' => 'John', 'last_name' => 'Doe'],
                    'related_data' => ['appointments' => [], 'estimates' => []]
                ]
            ],
            'meta' => ['total' => 1]
        ];

        $this->listingService->shouldReceive('getCustomers')
            ->once()
            ->with($request, $this->tenant->id)
            ->andReturn($expectedResult);

        // Act
        $result = $this->orchestrator->getCustomersForRequest($request, $this->tenant->id);

        // Assert
        $this->assertEquals($expectedResult, $result);
    }

    public function test_skips_caching_when_filters_are_present()
    {
        // Arrange
        $request = new Request();
        $request->merge(['filter' => 'active', 'per_page' => 15]);

        $expectedResult = [
            'data' => [
                [
                    'customer' => ['id' => 1, 'first_name' => 'John', 'last_name' => 'Doe'],
                    'related_data' => ['appointments' => [], 'estimates' => []]
                ]
            ],
            'meta' => ['total' => 1]
        ];

        $this->listingService->shouldReceive('getCustomers')
            ->once()
            ->with($request, $this->tenant->id)
            ->andReturn($expectedResult);

        // Act
        $result = $this->orchestrator->getCustomersForRequest($request, $this->tenant->id);

        // Assert
        $this->assertEquals($expectedResult, $result);
    }

    public function test_skips_caching_when_search_is_present()
    {
        // Arrange
        $request = new Request();
        $request->merge(['search' => 'john', 'per_page' => 15]);

        $expectedResult = [
            'data' => [
                [
                    'customer' => ['id' => 1, 'first_name' => 'John', 'last_name' => 'Doe'],
                    'related_data' => ['appointments' => [], 'estimates' => []]
                ]
            ],
            'meta' => ['total' => 1]
        ];

        $this->listingService->shouldReceive('getCustomers')
            ->once()
            ->with($request, $this->tenant->id)
            ->andReturn($expectedResult);

        // Act
        $result = $this->orchestrator->getCustomersForRequest($request, $this->tenant->id);

        // Assert
        $this->assertEquals($expectedResult, $result);
    }

    public function test_skips_caching_when_per_page_is_not_default()
    {
        // Arrange
        $request = new Request();
        $request->merge(['per_page' => 50]); // Non-default value

        $expectedResult = [
            'data' => [
                [
                    'customer' => ['id' => 1, 'first_name' => 'John', 'last_name' => 'Doe'],
                    'related_data' => ['appointments' => [], 'estimates' => []]
                ]
            ],
            'meta' => ['total' => 1]
        ];

        $this->listingService->shouldReceive('getCustomers')
            ->once()
            ->with($request, $this->tenant->id)
            ->andReturn($expectedResult);

        // Act
        $result = $this->orchestrator->getCustomersForRequest($request, $this->tenant->id);

        // Assert
        $this->assertEquals($expectedResult, $result);
    }

    public function test_builds_correct_pagination_meta()
    {
        // Arrange
        $request = new Request();
        $request->merge(['simple' => true]);

        $customers = Customer::where('tenant_id', $this->tenant->id)->paginate(1000);

        $this->customerService->shouldReceive('getCustomers')
            ->once()
            ->with($this->tenant->id, 1000)
            ->andReturn($customers);

        // Act
        $result = $this->orchestrator->getCustomersForRequest($request, $this->tenant->id);

        // Assert
        $this->assertArrayHasKey('meta', $result);
        $meta = $result['meta'];

        $this->assertArrayHasKey('current_page', $meta);
        $this->assertArrayHasKey('last_page', $meta);
        $this->assertArrayHasKey('per_page', $meta);
        $this->assertArrayHasKey('total', $meta);
        $this->assertArrayHasKey('from', $meta);
        $this->assertArrayHasKey('to', $meta);

        $this->assertEquals(1, $meta['current_page']);
        $this->assertEquals(1, $meta['last_page']);
        $this->assertEquals(1000, $meta['per_page']);
        $this->assertEquals(3, $meta['total']);
    }

    public function test_handles_empty_customer_list()
    {
        // Arrange
        $request = new Request();
        $request->merge(['simple' => true]);

        $emptyPaginator = new LengthAwarePaginator([], 0, 1000, 1);

        $this->customerService->shouldReceive('getCustomers')
            ->once()
            ->with($this->tenant->id, 1000)
            ->andReturn($emptyPaginator);

        // Act
        $result = $this->orchestrator->getCustomersForRequest($request, $this->tenant->id);

        // Assert
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('meta', $result);
        $this->assertCount(0, $result['data']);
        $this->assertEquals(0, $result['meta']['total']);
    }
}
