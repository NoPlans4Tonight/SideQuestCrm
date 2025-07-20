<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Customer;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Estimate;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomerApiPerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Initialize tenant and user for tests
        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);

        // Create test data
        $customers = Customer::factory()->count(10)->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
        ]);

        // Create appointments and estimates for some customers
        foreach ($customers->take(5) as $customer) {
            Appointment::factory()->count(3)->create([
                'customer_id' => $customer->id,
                'tenant_id' => $this->tenant->id,
            ]);

            Estimate::factory()->count(2)->create([
                'customer_id' => $customer->id,
                'tenant_id' => $this->tenant->id,
            ]);
        }
    }

    public function test_basic_customer_listing_performance()
    {
        $start = microtime(true);

        $response = $this->actingAs($this->user)
            ->getJson('/api/customers?include_related=false');

        $time = microtime(true) - $start;

        $response->assertStatus(200);
        $this->assertLessThan(0.1, $time, 'Basic customer listing should be under 100ms');

        $data = $response->json();
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('meta', $data);
    }

    public function test_enriched_customer_listing_performance()
    {
        $start = microtime(true);

        $response = $this->actingAs($this->user)
            ->getJson('/api/customers?include_related=true');

        $time = microtime(true) - $start;

        $response->assertStatus(200);
        $this->assertLessThan(0.5, $time, 'Enriched customer listing should be under 500ms');

        $data = $response->json();
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('meta', $data);

        // Check that summary data is included
        if (!empty($data['data'])) {
            $firstCustomer = $data['data'][0];
            $this->assertArrayHasKey('customer', $firstCustomer);
            $this->assertArrayHasKey('related_data', $firstCustomer);
            $this->assertArrayHasKey('summary', $firstCustomer['related_data']);
        }
    }

    public function test_customer_filtering_performance()
    {
        $start = microtime(true);

        $response = $this->actingAs($this->user)
            ->getJson('/api/customers?include_related=true&filter=active_appointments');

        $time = microtime(true) - $start;

        $response->assertStatus(200);
        $this->assertLessThan(0.5, $time, 'Filtered customer listing should be under 500ms');
    }

    public function test_customer_search_performance()
    {
        $start = microtime(true);

        $response = $this->actingAs($this->user)
            ->getJson('/api/customers?search=John');

        $time = microtime(true) - $start;

        $response->assertStatus(200);
        $this->assertLessThan(0.2, $time, 'Customer search should be under 200ms');
    }

    public function test_customer_pagination()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/customers?per_page=5');

        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEquals(5, $data['meta']['per_page']);
        $this->assertLessThanOrEqual(5, count($data['data']));
    }

    public function test_customer_caching()
    {
        // First request
        $start1 = microtime(true);
        $response1 = $this->actingAs($this->user)
            ->getJson('/api/customers');
        $time1 = microtime(true) - $start1;

        // Second request (should be cached)
        $start2 = microtime(true);
        $response2 = $this->actingAs($this->user)
            ->getJson('/api/customers');
        $time2 = microtime(true) - $start2;

        $response1->assertStatus(200);
        $response2->assertStatus(200);

        // Cached request should be faster
        $this->assertLessThanOrEqual($time1, $time2, 'Cached request should be faster or equal');
    }
}
