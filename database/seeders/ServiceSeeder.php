<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\Tenant;
use App\Models\User;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first tenant and user
        $tenant = Tenant::first();
        $user = User::first();

        if (!$tenant || !$user) {
            $this->command->error('No tenant or user found. Please run the TestUserSeeder first.');
            return;
        }

        $services = [
            [
                'tenant_id' => $tenant->id,
                'name' => 'Chimney Cleaning',
                'description' => 'Complete chimney cleaning and inspection service',
                'category' => 'Cleaning',
                'base_price' => 1500,
                'hourly_rate' => 750,
                'is_active' => true,
                'created_by' => $user->id,
            ],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Chimney Repair',
                'description' => 'Chimney masonry repair and restoration',
                'category' => 'Repair',
                'base_price' => 3000,
                'hourly_rate' => 850,
                'is_active' => true,
                'created_by' => $user->id,
            ],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Chimney Cap Installation',
                'description' => 'Installation of chimney caps and covers',
                'category' => 'Installation',
                'base_price' => 2000,
                'hourly_rate' => 650,
                'is_active' => true,
                'created_by' => $user->id,
            ],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Chimney Inspection',
                'description' => 'Professional chimney inspection and safety assessment',
                'category' => 'Cleaning',
                'base_price' => 1000,
                'hourly_rate' => 600,
                'is_active' => true,
                'created_by' => $user->id,
            ],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Fireplace Installation',
                'description' => 'Complete fireplace installation and setup',
                'category' => 'Installation',
                'base_price' => 25000,
                'hourly_rate' => 950,
                'is_active' => true,
                'created_by' => $user->id,
            ],
        ];

        foreach ($services as $serviceData) {
            Service::create($serviceData);
        }

        $this->command->info('Created ' . count($services) . ' test services.');
    }
}
