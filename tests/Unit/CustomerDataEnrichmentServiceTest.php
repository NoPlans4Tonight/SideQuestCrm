<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\CustomerDataEnrichmentService;
use App\Services\JobService;
use App\Services\AppointmentService;
use App\Services\EstimateService;
use App\Services\ServiceService;
use App\Models\Customer;
use App\Models\Job;
use App\Models\Appointment;
use App\Models\Estimate;
use App\Models\Service;
use App\Models\JobService as JobServiceModel;
use App\Models\EstimateItem;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class CustomerDataEnrichmentServiceTest extends TestCase
{
    use RefreshDatabase;

    private CustomerDataEnrichmentService $enrichmentService;
    private $mockJobService;
    private $mockAppointmentService;
    private $mockEstimateService;
    private $mockServiceService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockJobService = Mockery::mock(JobService::class);
        $this->mockAppointmentService = Mockery::mock(AppointmentService::class);
        $this->mockEstimateService = Mockery::mock(EstimateService::class);
        $this->mockServiceService = Mockery::mock(ServiceService::class);

        $this->enrichmentService = new CustomerDataEnrichmentService(
            $this->mockJobService,
            $this->mockAppointmentService,
            $this->mockEstimateService,
            $this->mockServiceService
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_enrich_customer_data_with_no_related_data()
    {
        $customer = Customer::factory()->create();

        $result = $this->enrichmentService->enrichCustomerData($customer);

        $this->assertArrayHasKey('customer', $result);
        $this->assertArrayHasKey('related_data', $result);
        $this->assertArrayHasKey('jobs', $result['related_data']);
        $this->assertArrayHasKey('appointments', $result['related_data']);
        $this->assertArrayHasKey('estimates', $result['related_data']);
        $this->assertArrayHasKey('services', $result['related_data']);
        $this->assertArrayHasKey('summary', $result['related_data']);

        // Check jobs data
        $this->assertFalse($result['related_data']['jobs']['has_jobs']);
        $this->assertEquals(0, $result['related_data']['jobs']['total_count']);

        // Check appointments data
        $this->assertFalse($result['related_data']['appointments']['has_appointments']);
        $this->assertEquals(0, $result['related_data']['appointments']['total_count']);

        // Check estimates data
        $this->assertFalse($result['related_data']['estimates']['has_estimates']);
        $this->assertEquals(0, $result['related_data']['estimates']['total_count']);

        // Check services data
        $this->assertFalse($result['related_data']['services']['has_services']);
        $this->assertEquals(0, $result['related_data']['services']['total_count']);
    }

    public function test_enrich_customer_data_with_jobs()
    {
        $customer = Customer::factory()->create();
        $job1 = Job::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $customer->tenant_id,
            'status' => 'in_progress',
            'materials_cost' => 600.00,
            'labor_cost' => 400.00
        ]);
        $job2 = Job::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $customer->tenant_id,
            'status' => 'completed',
            'materials_cost' => 1200.00,
            'labor_cost' => 800.00
        ]);

        $result = $this->enrichmentService->enrichCustomerData($customer);

        $jobsData = $result['related_data']['jobs'];

        $this->assertTrue($jobsData['has_jobs']);
        $this->assertEquals(2, $jobsData['total_count']);
        $this->assertEquals(3000.00, $jobsData['total_value']);
        $this->assertCount(2, $jobsData['jobs']);
        $this->assertEquals(1, $jobsData['status_breakdown']['in_progress']);
        $this->assertEquals(1, $jobsData['status_breakdown']['completed']);
    }

    public function test_enrich_customer_data_with_appointments()
    {
        $customer = Customer::factory()->create();
        $appointment1 = Appointment::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'confirmed',
            'start_time' => now()->addDays(1)
        ]);
        $appointment2 = Appointment::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'completed',
            'start_time' => now()->subDays(1)
        ]);

        $result = $this->enrichmentService->enrichCustomerData($customer);

        $appointmentsData = $result['related_data']['appointments'];

        $this->assertTrue($appointmentsData['has_appointments']);
        $this->assertEquals(2, $appointmentsData['total_count']);
        $this->assertEquals(1, $appointmentsData['upcoming_count']);
        $this->assertCount(2, $appointmentsData['appointments']);
        $this->assertEquals(1, $appointmentsData['status_breakdown']['confirmed']);
        $this->assertEquals(1, $appointmentsData['status_breakdown']['completed']);
    }

    public function test_enrich_customer_data_with_estimates()
    {
        $customer = Customer::factory()->create();
        $estimate1 = Estimate::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $customer->tenant_id,
            'status' => 'draft',
            'subtotal' => 500.00,
            'tax_amount' => 0,
            'discount_amount' => 0
        ]);
        $estimate2 = Estimate::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $customer->tenant_id,
            'status' => 'accepted',
            'subtotal' => 1500.00,
            'tax_amount' => 0,
            'discount_amount' => 0
        ]);

        $result = $this->enrichmentService->enrichCustomerData($customer);

        $estimatesData = $result['related_data']['estimates'];

        $this->assertTrue($estimatesData['has_estimates']);
        $this->assertEquals(2, $estimatesData['total_count']);
        $this->assertEquals(2000.00, $estimatesData['total_value']);
        $this->assertEquals(500.00, $estimatesData['pending_value']);
        $this->assertCount(2, $estimatesData['estimates']);
        $this->assertEquals(1, $estimatesData['status_breakdown']['draft']);
        $this->assertEquals(1, $estimatesData['status_breakdown']['accepted']);
    }

    public function test_enrich_customer_data_with_services()
    {
        $customer = Customer::factory()->create();
        $service = Service::factory()->create();
        $job = Job::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $customer->tenant_id
        ]);

        JobServiceModel::factory()->create([
            'job_id' => $job->id,
            'service_id' => $service->id,
            'quantity' => 2,
            'unit_price' => 100.00,
            'total_price' => 200.00
        ]);

        $result = $this->enrichmentService->enrichCustomerData($customer);

        $servicesData = $result['related_data']['services'];

        $this->assertTrue($servicesData['has_services']);
        $this->assertEquals(1, $servicesData['total_count']);
        $this->assertCount(1, $servicesData['services']);
        $this->assertCount(1, $servicesData['unique_services']);
        $this->assertEquals($service->name, $servicesData['services'][0]['service_name']);
    }

    public function test_customer_summary_statistics()
    {
        $tenant = Tenant::factory()->create(['domain' => 'test-summary-' . uniqid()]);
        $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);

        // Create jobs
        Job::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $customer->tenant_id,
            'status' => 'in_progress',
            'materials_cost' => 600.00,
            'labor_cost' => 400.00
        ]);
        Job::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $customer->tenant_id,
            'status' => 'completed',
            'materials_cost' => 1200.00,
            'labor_cost' => 800.00
        ]);

        // Create appointments
        Appointment::factory()->create([
            'customer_id' => $customer->id,
            'start_time' => now()->addDays(1)
        ]);
        Appointment::factory()->create([
            'customer_id' => $customer->id,
            'start_time' => now()->subDays(1)
        ]);

        // Create estimates
        Estimate::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $customer->tenant_id,
            'status' => 'draft',
            'subtotal' => 500.00,
            'tax_amount' => 0,
            'discount_amount' => 0
        ]);
        Estimate::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $customer->tenant_id,
            'status' => 'accepted',
            'subtotal' => 1500.00,
            'tax_amount' => 0,
            'discount_amount' => 0
        ]);

        $result = $this->enrichmentService->enrichCustomerData($customer);
        $summary = $result['related_data']['summary'];

        $this->assertEquals(2, $summary['total_jobs']);
        $this->assertEquals(1, $summary['active_jobs']);
        $this->assertEquals(1, $summary['completed_jobs']);
        $this->assertEquals(2, $summary['total_appointments']);
        $this->assertEquals(1, $summary['upcoming_appointments']);
        $this->assertEquals(2, $summary['total_estimates']);
        $this->assertEquals(1, $summary['pending_estimates']);
        $this->assertEquals(1, $summary['accepted_estimates']);
        $this->assertEquals(3000.00, $summary['total_job_value']);
        $this->assertEquals(2000.00, $summary['total_estimate_value']);
        $this->assertEquals(500.00, $summary['pending_estimate_value']);
        $this->assertNotNull($summary['last_activity']);
        $this->assertNotNull($summary['customer_since']);
    }

    public function test_enrich_multiple_customers_data()
    {
        $customer1 = Customer::factory()->create();
        $customer2 = Customer::factory()->create();

        Job::factory()->create([
            'customer_id' => $customer1->id,
            'tenant_id' => $customer1->tenant_id
        ]);
        Appointment::factory()->create([
            'customer_id' => $customer2->id,
            'tenant_id' => $customer2->tenant_id
        ]);

        $customers = collect([$customer1, $customer2]);
        $result = $this->enrichmentService->enrichCustomersData($customers);

        $this->assertCount(2, $result);
        $this->assertArrayHasKey('customer', $result[0]);
        $this->assertArrayHasKey('related_data', $result[0]);
        $this->assertArrayHasKey('customer', $result[1]);
        $this->assertArrayHasKey('related_data', $result[1]);
    }

    public function test_last_activity_calculation()
    {
        $customer = Customer::factory()->create();

        // Create a job with recent activity
        $job = Job::factory()->create([
            'customer_id' => $customer->id,
            'tenant_id' => $customer->tenant_id,
            'updated_at' => now()->addHours(1)
        ]);

        $result = $this->enrichmentService->enrichCustomerData($customer);
        $summary = $result['related_data']['summary'];

        $this->assertNotNull($summary['last_activity']);
        $this->assertGreaterThan($customer->updated_at->toISOString(), $summary['last_activity']);
    }
}
