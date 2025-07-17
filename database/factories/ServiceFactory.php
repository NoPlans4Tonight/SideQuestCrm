<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => $this->faker->unique()->words(2, true),
            'description' => $this->faker->sentence(),
            'category' => $this->faker->randomElement(['Cleaning', 'Repair', 'Installation', 'Consulting']),
            'base_price' => $this->faker->randomFloat(2, 50, 500),
            'hourly_rate' => $this->faker->randomFloat(2, 20, 100),
            'is_active' => $this->faker->boolean(90),
            'created_by' => User::factory(),
            'settings' => [],
        ];
    }
}
