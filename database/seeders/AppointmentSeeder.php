<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first tenant and users for seeding
        $tenant = Tenant::first();
        $users = User::where('tenant_id', $tenant->id)->get();
        $customers = Customer::where('tenant_id', $tenant->id)->get();

        if ($users->isEmpty() || $customers->isEmpty()) {
            $this->command->warn('No users or customers found. Skipping appointment seeding.');
            return;
        }

        // Create upcoming appointments
        Appointment::factory()
            ->count(10)
            ->upcoming()
            ->create([
                'tenant_id' => $tenant->id,
                'customer_id' => $customers->random()->id,
                'assigned_to' => $users->random()->id,
                'created_by' => $users->random()->id,
            ]);

        // Create past appointments
        Appointment::factory()
            ->count(15)
            ->past()
            ->create([
                'tenant_id' => $tenant->id,
                'customer_id' => $customers->random()->id,
                'assigned_to' => $users->random()->id,
                'created_by' => $users->random()->id,
            ]);

        // Create appointments for today
        Appointment::factory()
            ->count(3)
            ->today()
            ->create([
                'tenant_id' => $tenant->id,
                'customer_id' => $customers->random()->id,
                'assigned_to' => $users->random()->id,
                'created_by' => $users->random()->id,
            ]);

        // Create appointments for tomorrow
        Appointment::factory()
            ->count(5)
            ->tomorrow()
            ->create([
                'tenant_id' => $tenant->id,
                'customer_id' => $customers->random()->id,
                'assigned_to' => $users->random()->id,
                'created_by' => $users->random()->id,
            ]);

        // Create some estimate appointments
        Appointment::factory()
            ->count(8)
            ->estimate()
            ->upcoming()
            ->create([
                'tenant_id' => $tenant->id,
                'customer_id' => $customers->random()->id,
                'assigned_to' => $users->random()->id,
                'created_by' => $users->random()->id,
            ]);

        // Create some repair appointments
        Appointment::factory()
            ->count(6)
            ->repair()
            ->upcoming()
            ->create([
                'tenant_id' => $tenant->id,
                'customer_id' => $customers->random()->id,
                'assigned_to' => $users->random()->id,
                'created_by' => $users->random()->id,
            ]);

        // Create some maintenance appointments
        Appointment::factory()
            ->count(4)
            ->maintenance()
            ->upcoming()
            ->create([
                'tenant_id' => $tenant->id,
                'customer_id' => $customers->random()->id,
                'assigned_to' => $users->random()->id,
                'created_by' => $users->random()->id,
            ]);

        // Create some unassigned appointments
        Appointment::factory()
            ->count(3)
            ->unassigned()
            ->upcoming()
            ->create([
                'tenant_id' => $tenant->id,
                'customer_id' => $customers->random()->id,
                'created_by' => $users->random()->id,
            ]);

        $this->command->info('Appointments seeded successfully!');
    }
}
