<?php

namespace Database\Factories;

use App\Models\Job;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Job>
 */
class JobFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Job::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'customer_id' => Customer::factory(),
            'status' => $this->faker->randomElement(['pending', 'in_progress', 'completed', 'cancelled']),
            'priority' => $this->faker->randomElement(['low', 'medium', 'high', 'urgent']),
            'scheduled_date' => $this->faker->optional()->dateTimeBetween('now', '+30 days'),
            'estimated_hours' => $this->faker->optional()->randomFloat(1, 1, 40),
            'price' => $this->faker->optional()->randomFloat(2, 50, 5000),
            'total_cost' => $this->faker->optional()->randomFloat(2, 50, 5000),
            'notes' => $this->faker->optional()->paragraph(),
            'completed_at' => null,
        ];
    }

    /**
     * Indicate that the job is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'completed_at' => null,
        ]);
    }

    /**
     * Indicate that the job is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'completed_at' => null,
        ]);
    }

    /**
     * Indicate that the job is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'completed_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    /**
     * Indicate that the job is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'completed_at' => null,
        ]);
    }

    /**
     * Set the job priority to high.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'high',
        ]);
    }

    /**
     * Set the job priority to urgent.
     */
    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'urgent',
        ]);
    }

    /**
     * Set the job to be scheduled for tomorrow.
     */
    public function scheduledForTomorrow(): static
    {
        return $this->state(fn (array $attributes) => [
            'scheduled_date' => now()->addDay(),
        ]);
    }

    /**
     * Set the job to be scheduled for next week.
     */
    public function scheduledForNextWeek(): static
    {
        return $this->state(fn (array $attributes) => [
            'scheduled_date' => now()->addWeek(),
        ]);
    }
}
