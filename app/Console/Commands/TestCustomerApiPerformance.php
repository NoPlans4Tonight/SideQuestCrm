<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CustomerListingService;
use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\User;

class TestCustomerApiPerformance extends Command
{
    protected $signature = 'test:customer-api-performance';
    protected $description = 'Test the performance of the customer API';

    public function handle()
    {
        $this->info('Testing Customer API Performance...');

        // Get a tenant and user for testing
        $tenant = Tenant::first();
        $user = User::first();

        if (!$tenant || !$user) {
            $this->error('No tenant or user found. Please seed the database first.');
            return 1;
        }

        $listingService = app(CustomerListingService::class);

        // Test 1: Basic customer listing without enrichment
        $this->info('Test 1: Basic customer listing (include_related=false)');
        $request1 = new Request(['include_related' => 'false']);

        $start = microtime(true);
        $result1 = $listingService->getCustomers($request1, $tenant->id);
        $time1 = microtime(true) - $start;

        $this->info("Time: " . round($time1 * 1000, 2) . "ms");
        $this->info("Customers returned: " . count($result1['data']));

        // Test 2: Customer listing with enrichment
        $this->info('Test 2: Customer listing with enrichment (include_related=true)');
        $request2 = new Request(['include_related' => 'true']);

        $start = microtime(true);
        $result2 = $listingService->getCustomers($request2, $tenant->id);
        $time2 = microtime(true) - $start;

        $this->info("Time: " . round($time2 * 1000, 2) . "ms");
        $this->info("Customers returned: " . count($result2['data']));

        // Test 3: Customer listing with filtering
        $this->info('Test 3: Customer listing with active_appointments filter');
        $request3 = new Request(['include_related' => 'true', 'filter' => 'active_appointments']);

        $start = microtime(true);
        $result3 = $listingService->getCustomers($request3, $tenant->id);
        $time3 = microtime(true) - $start;

        $this->info("Time: " . round($time3 * 1000, 2) . "ms");
        $this->info("Customers returned: " . count($result3['data']));

        $this->info('Performance test completed!');

        return 0;
    }
}
