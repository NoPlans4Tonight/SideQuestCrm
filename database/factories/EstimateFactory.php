<?php

namespace Database\Factories;

use App\Models\Estimate;
use App\Models\Customer;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Estimate>
 */
class EstimateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Estimate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lead_id' => null, // Lead factory not available yet
            'title' => $this->faker->sentence(3, 6),
            'description' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(['draft', 'pending', 'sent', 'accepted', 'rejected', 'expired']),
            'valid_until' => $this->faker->optional()->dateTimeBetween('now', '+30 days'),
            'subtotal' => $this->faker->randomFloat(2, 100, 5000),
            'tax_rate' => $this->faker->randomFloat(2, 0, 15),
            'tax_amount' => 0, // Will be calculated
            'discount_amount' => $this->faker->randomFloat(2, 0, 500),
            'total_amount' => 0, // Will be calculated
            'notes' => $this->faker->optional()->paragraph(),
            'terms_conditions' => $this->faker->optional()->paragraphs(3, true),
            'assigned_to' => null,
            'created_by' => User::factory(),
            'sent_at' => $this->faker->optional()->dateTimeBetween('-30 days', 'now'),
            'accepted_at' => $this->faker->optional()->dateTimeBetween('-30 days', 'now'),
            'rejected_at' => $this->faker->optional()->dateTimeBetween('-30 days', 'now'),
            'expired_at' => $this->faker->optional()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Indicate that the estimate is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'sent_at' => null,
            'accepted_at' => null,
            'rejected_at' => null,
            'expired_at' => null,
        ]);
    }

    /**
     * Indicate that the estimate is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'sent_at' => null,
            'accepted_at' => null,
            'rejected_at' => null,
            'expired_at' => null,
        ]);
    }

    /**
     * Indicate that the estimate has been sent.
     */
    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sent',
            'sent_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'accepted_at' => null,
            'rejected_at' => null,
            'expired_at' => null,
        ]);
    }

    /**
     * Indicate that the estimate has been accepted.
     */
    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'accepted',
            'sent_at' => $this->faker->dateTimeBetween('-60 days', '-30 days'),
            'accepted_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'rejected_at' => null,
            'expired_at' => null,
        ]);
    }

    /**
     * Indicate that the estimate has been rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'sent_at' => $this->faker->dateTimeBetween('-60 days', '-30 days'),
            'accepted_at' => null,
            'rejected_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'expired_at' => null,
        ]);
    }

    /**
     * Indicate that the estimate has expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'sent_at' => $this->faker->dateTimeBetween('-60 days', '-30 days'),
            'accepted_at' => null,
            'rejected_at' => null,
            'expired_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'valid_until' => $this->faker->dateTimeBetween('-30 days', '-1 day'),
        ]);
    }

    /**
     * Indicate that the estimate is for a specific customer.
     */
    public function forCustomer(Customer $customer): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => $customer->tenant_id,
            'customer_id' => $customer->id,
        ]);
    }

    /**
     * Indicate that the estimate is assigned to a specific user.
     */
    public function assignedTo(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => $user->tenant_id,
            'assigned_to' => $user->id,
        ]);
    }

    /**
     * Indicate that the estimate is created by a specific user.
     */
    public function createdBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => $user->tenant_id,
            'created_by' => $user->id,
        ]);
    }
}
