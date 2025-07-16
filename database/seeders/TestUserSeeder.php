<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test tenant (Rock Hard Chimney) or get existing one
        $tenant = Tenant::firstOrCreate(
            ['name' => 'rock-hard'],
            [
            'name' => 'rock-hard',
            'domain' => 'rock-hard.localhost',
            'database' => 'rock_hard_chimney',
            'company_name' => 'Rock Hard Chimney',
            'contact_email' => 'admin@rockhardchimney.com',
            'contact_phone' => '(555) 123-4567',
            'address' => '123 Main St',
            'city' => 'Anytown',
            'state' => 'CA',
            'zip_code' => '90210',
            'country' => 'US',
            'timezone' => 'America/Los_Angeles',
            'is_active' => true,
            ]
        );

        // Create a super admin user or get existing one
        $user = User::firstOrCreate(
            ['email' => 'admin@sidequest.com'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Super Admin',
                'email' => 'admin@sidequest.com',
                'password' => Hash::make('password'),
                'position' => 'Super Administrator',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create a regular test user or get existing one
        $testUser = User::firstOrCreate(
            ['email' => 'test@sidequest.com'],
            [
            'tenant_id' => $tenant->id,
            'name' => 'Test User',
            'email' => 'test@sidequest.com',
            'password' => Hash::make('password'),
            'position' => 'Sales Representative',
            'is_active' => true,
            'email_verified_at' => now(),
            ]
        );

        $this->command->info('Test users created successfully!');
        $this->command->info('Super Admin: admin@sidequest.com / password');
        $this->command->info('Test User: test@sidequest.com / password');
        $this->command->info('Tenant: Rock Hard Chimney (rock-hard)');
    }
}
