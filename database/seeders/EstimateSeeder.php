<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Models\Customer;
use App\Models\Service;
use App\Models\User;
use App\Models\Tenant;

class EstimateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the existing tenant created by TestUserSeeder
        $tenant = Tenant::where('name', 'rock-hard')->first();
        if (!$tenant) {
            $this->command->error('Tenant not found. Please run TestUserSeeder first.');
            return;
        }

        // Get the existing user created by TestUserSeeder
        $user = User::where('email', 'admin@sidequest.com')->first();
        if (!$user) {
            $this->command->error('User not found. Please run TestUserSeeder first.');
            return;
        }

        // Get existing customers or create some
        $customers = Customer::where('tenant_id', $tenant->id)->get();
        if ($customers->isEmpty()) {
            $customers = Customer::factory(5)->create(['tenant_id' => $tenant->id]);
        }

        // Get existing services or create some
        $services = Service::where('tenant_id', $tenant->id)->get();
        if ($services->isEmpty()) {
            $services = Service::factory(10)->create(['tenant_id' => $tenant->id]);
        }

        // Create estimates with different statuses
        $this->createEstimates($tenant, $user, $customers, $services);
    }

    private function createEstimates($tenant, $user, $customers, $services): void
    {
        // Create draft estimates
        for ($i = 0; $i < 2; $i++) {
            $estimate = Estimate::factory()->create([
                'tenant_id' => $tenant->id,
                'customer_id' => $customers->random()->id,
                'created_by' => $user->id,
                'assigned_to' => $user->id,
            ]);

            $this->createEstimateItems($estimate, $services, 2, 4);
        }

        // Create pending estimates
        for ($i = 0; $i < 1; $i++) {
            $estimate = Estimate::factory()->pending()->create([
                'tenant_id' => $tenant->id,
                'customer_id' => $customers->random()->id,
                'created_by' => $user->id,
                'assigned_to' => $user->id,
            ]);

            $this->createEstimateItems($estimate, $services, 2, 4);
        }

        // Create sent estimates
        for ($i = 0; $i < 2; $i++) {
            $estimate = Estimate::factory()->sent()->create([
                'tenant_id' => $tenant->id,
                'customer_id' => $customers->random()->id,
                'created_by' => $user->id,
                'assigned_to' => $user->id,
            ]);

            $this->createEstimateItems($estimate, $services, 2, 4);
        }

        // Create accepted estimates
        for ($i = 0; $i < 1; $i++) {
            $estimate = Estimate::factory()->accepted()->create([
                'tenant_id' => $tenant->id,
                'customer_id' => $customers->random()->id,
                'created_by' => $user->id,
                'assigned_to' => $user->id,
            ]);

            $this->createEstimateItems($estimate, $services, 3, 5);
        }

        // Create rejected estimates
        for ($i = 0; $i < 1; $i++) {
            $estimate = Estimate::factory()->rejected()->create([
                'tenant_id' => $tenant->id,
                'customer_id' => $customers->random()->id,
                'created_by' => $user->id,
                'assigned_to' => $user->id,
            ]);

            $this->createEstimateItems($estimate, $services, 2, 4);
        }

        // Create expired estimates
        for ($i = 0; $i < 1; $i++) {
            $estimate = Estimate::factory()->expired()->create([
                'tenant_id' => $tenant->id,
                'customer_id' => $customers->random()->id,
                'created_by' => $user->id,
                'assigned_to' => $user->id,
            ]);

            $this->createEstimateItems($estimate, $services, 2, 4);
        }

        // Create some estimates with specific characteristics
        $this->createSpecialEstimates($tenant, $user, $customers, $services);
    }

    private function createEstimateItems($estimate, $services, $minItems = 2, $maxItems = 5): void
    {
        $numItems = rand($minItems, $maxItems);
        $subtotal = 0;

        for ($i = 0; $i < $numItems; $i++) {
            $service = $services->random();
            $quantity = rand(1, 5);
            $unitPrice = $service->base_price ?: rand(25, 200);
            $totalPrice = $quantity * $unitPrice;
            $subtotal += $totalPrice;

            EstimateItem::factory()->create([
                'estimate_id' => $estimate->id,
                'service_id' => $service->id,
                'description' => $service->name,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
                'sort_order' => $i + 1,
            ]);
        }

        // Update estimate totals
        $taxRate = rand(0, 999) / 100; // 0.00 to 9.99
        $taxAmount = $subtotal * ($taxRate / 100);
        $discountAmount = rand(0, 100);
        $totalAmount = $subtotal + $taxAmount - $discountAmount;

        $estimate->update([
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
        ]);
    }

    private function createSpecialEstimates($tenant, $user, $customers, $services): void
    {
        // Create a high-value estimate
        $highValueEstimate = Estimate::factory()->sent()->create([
            'tenant_id' => $tenant->id,
            'customer_id' => $customers->random()->id,
            'created_by' => $user->id,
            'assigned_to' => $user->id,
            'title' => 'Complete Home Renovation Project',
            'description' => 'Comprehensive home renovation including kitchen, bathroom, and living room updates.',
            'valid_until' => now()->addDays(30),
        ]);

        $this->createEstimateItems($highValueEstimate, $services, 8, 12);

        // Create an emergency service estimate
        $emergencyEstimate = Estimate::factory()->pending()->create([
            'tenant_id' => $tenant->id,
            'customer_id' => $customers->random()->id,
            'created_by' => $user->id,
            'assigned_to' => $user->id,
            'title' => 'Emergency Plumbing Repair',
            'description' => 'Urgent plumbing repair for burst pipe and water damage.',
            'valid_until' => now()->addDays(7),
        ]);

        $this->createEstimateItems($emergencyEstimate, $services, 1, 3);

        // Create a maintenance estimate
        $maintenanceEstimate = Estimate::factory()->draft()->create([
            'tenant_id' => $tenant->id,
            'customer_id' => $customers->random()->id,
            'created_by' => $user->id,
            'assigned_to' => $user->id,
            'title' => 'Annual HVAC Maintenance',
            'description' => 'Regular maintenance service for heating and cooling systems.',
            'valid_until' => now()->addDays(60),
        ]);

        $this->createEstimateItems($maintenanceEstimate, $services, 3, 5);
    }
}
