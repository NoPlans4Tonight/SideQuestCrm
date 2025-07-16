<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\Tenant;
use App\Models\User;

class CustomerSeeder extends Seeder
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
            $this->command->error('No tenant or user found. Please run the main seeder first.');
            return;
        }

        $customers = [
            [
                'tenant_id' => $tenant->id,
                'first_name' => 'John',
                'last_name' => 'Smith',
                'email' => 'john.smith@example.com',
                'phone' => '(555) 123-4567',
                'address' => '123 Main St',
                'city' => 'Anytown',
                'state' => 'CA',
                'zip_code' => '90210',
                'country' => 'USA',
                'status' => 'active',
                'source' => 'website',
                'assigned_to' => $user->id,
                'created_by' => $user->id,
                'notes' => 'Regular customer, prefers morning appointments'
            ],
            [
                'tenant_id' => $tenant->id,
                'first_name' => 'Sarah',
                'last_name' => 'Johnson',
                'email' => 'sarah.johnson@example.com',
                'phone' => '(555) 234-5678',
                'address' => '456 Oak Ave',
                'city' => 'Somewhere',
                'state' => 'NY',
                'zip_code' => '10001',
                'country' => 'USA',
                'status' => 'active',
                'source' => 'referral',
                'assigned_to' => $user->id,
                'created_by' => $user->id,
                'notes' => 'New customer, needs quote for kitchen remodel'
            ],
            [
                'tenant_id' => $tenant->id,
                'first_name' => 'Mike',
                'last_name' => 'Davis',
                'email' => 'mike.davis@example.com',
                'phone' => '(555) 345-6789',
                'address' => '789 Pine Rd',
                'city' => 'Elsewhere',
                'state' => 'TX',
                'zip_code' => '75001',
                'country' => 'USA',
                'status' => 'prospect',
                'source' => 'cold_call',
                'assigned_to' => null,
                'created_by' => $user->id,
                'notes' => 'Interested in bathroom renovation'
            ],
            [
                'tenant_id' => $tenant->id,
                'first_name' => 'Lisa',
                'last_name' => 'Wilson',
                'email' => 'lisa.wilson@example.com',
                'phone' => '(555) 456-7890',
                'address' => '321 Elm St',
                'city' => 'Nowhere',
                'state' => 'FL',
                'zip_code' => '33101',
                'country' => 'USA',
                'status' => 'inactive',
                'source' => 'website',
                'assigned_to' => $user->id,
                'created_by' => $user->id,
                'notes' => 'Previous customer, moved out of area'
            ],
            [
                'tenant_id' => $tenant->id,
                'first_name' => 'David',
                'last_name' => 'Brown',
                'email' => 'david.brown@example.com',
                'phone' => '(555) 567-8901',
                'address' => '654 Maple Dr',
                'city' => 'Anywhere',
                'state' => 'WA',
                'zip_code' => '98101',
                'country' => 'USA',
                'status' => 'active',
                'source' => 'referral',
                'assigned_to' => $user->id,
                'created_by' => $user->id,
                'notes' => 'Commercial client, multiple properties'
            ]
        ];

        foreach ($customers as $customerData) {
            Customer::create($customerData);
        }

        $this->command->info('Created ' . count($customers) . ' test customers.');
    }
}
