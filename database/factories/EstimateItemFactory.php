<?php

namespace Database\Factories;

use App\Models\EstimateItem;
use App\Models\Estimate;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EstimateItem>
 */
class EstimateItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EstimateItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $estimate = Estimate::factory()->create();
        $service = Service::factory()->create(['tenant_id' => $estimate->tenant_id]);

        $quantity = $this->faker->randomFloat(2, 1, 10);
        $unitPrice = $this->faker->randomFloat(2, 10, 500);

        return [
            'estimate_id' => $estimate->id,
            'service_id' => $this->faker->optional()->randomElement([null, $service->id]),
            'description' => $this->faker->sentence(3, 6),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $quantity * $unitPrice,
            'notes' => $this->faker->optional()->sentence(),
            'sort_order' => $this->faker->numberBetween(1, 10),
        ];
    }

    /**
     * Indicate that the estimate item is for a specific estimate.
     */
    public function forEstimate(Estimate $estimate): static
    {
        return $this->state(fn (array $attributes) => [
            'estimate_id' => $estimate->id,
        ]);
    }

    /**
     * Indicate that the estimate item is for a specific service.
     */
    public function forService(Service $service): static
    {
        return $this->state(fn (array $attributes) => [
            'service_id' => $service->id,
            'description' => $service->name,
            'unit_price' => $service->base_price,
        ]);
    }

    /**
     * Indicate that the estimate item has a specific quantity.
     */
    public function withQuantity(float $quantity): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $quantity,
        ]);
    }

    /**
     * Indicate that the estimate item has a specific unit price.
     */
    public function withUnitPrice(float $unitPrice): static
    {
        return $this->state(fn (array $attributes) => [
            'unit_price' => $unitPrice,
        ]);
    }

    /**
     * Indicate that the estimate item is for labor.
     */
    public function labor(): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => $this->faker->randomElement([
                'Labor - General Repairs',
                'Labor - Installation',
                'Labor - Maintenance',
                'Labor - Emergency Service',
                'Labor - Consultation',
            ]),
            'unit_price' => $this->faker->randomFloat(2, 50, 150),
        ]);
    }

    /**
     * Indicate that the estimate item is for materials.
     */
    public function materials(): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => $this->faker->randomElement([
                'Materials - Plumbing Supplies',
                'Materials - Electrical Components',
                'Materials - Hardware',
                'Materials - Paint & Supplies',
                'Materials - Tools & Equipment',
            ]),
            'unit_price' => $this->faker->randomFloat(2, 10, 200),
        ]);
    }

    /**
     * Indicate that the estimate item is for a specific category.
     */
    public function forCategory(string $category): static
    {
        $descriptions = [
            'plumbing' => [
                'Pipe Repair',
                'Fixture Installation',
                'Drain Cleaning',
                'Water Heater Service',
                'Leak Repair',
            ],
            'electrical' => [
                'Outlet Installation',
                'Light Fixture Installation',
                'Circuit Repair',
                'Panel Upgrade',
                'Wiring Installation',
            ],
            'hvac' => [
                'AC Repair',
                'Heating Service',
                'Duct Cleaning',
                'Thermostat Installation',
                'Filter Replacement',
            ],
            'general' => [
                'General Repairs',
                'Maintenance Service',
                'Emergency Call',
                'Inspection',
                'Consultation',
            ],
        ];

        return $this->state(fn (array $attributes) => [
            'description' => $this->faker->randomElement($descriptions[$category] ?? ['General Service']),
        ]);
    }
}
