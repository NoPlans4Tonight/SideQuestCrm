<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        $startTime = $this->faker->dateTimeBetween('now', '+30 days');
        $duration = $this->faker->randomElement([30, 60, 90, 120, 180]);
        $endTime = Carbon::parse($startTime)->addMinutes($duration);

        return [
            'tenant_id' => Tenant::factory(),
            'customer_id' => Customer::factory(),
            'lead_id' => null,
            'estimate_id' => null,
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'appointment_type' => $this->faker->randomElement(['estimate', 'inspection', 'repair', 'maintenance', 'follow_up', 'other']),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'duration' => $duration,
            'status' => $this->faker->randomElement(['scheduled', 'confirmed', 'completed', 'cancelled', 'no_show']),
            'assigned_to' => User::factory(),
            'created_by' => User::factory(),
            'location' => $this->faker->address(),
            'notes' => $this->faker->optional()->paragraph(),
            'reminder_sent' => $this->faker->boolean(20),
            'reminder_sent_at' => $this->faker->optional()->dateTimeBetween('-1 week', 'now'),
        ];
    }

    /**
     * Indicate that the appointment is scheduled.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'scheduled',
        ]);
    }

    /**
     * Indicate that the appointment is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
        ]);
    }

    /**
     * Indicate that the appointment is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    /**
     * Indicate that the appointment is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    /**
     * Indicate that the appointment is a no show.
     */
    public function noShow(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'no_show',
        ]);
    }

    /**
     * Indicate that the appointment is for an estimate.
     */
    public function estimate(): static
    {
        return $this->state(fn (array $attributes) => [
            'appointment_type' => 'estimate',
        ]);
    }

    /**
     * Indicate that the appointment is for an inspection.
     */
    public function inspection(): static
    {
        return $this->state(fn (array $attributes) => [
            'appointment_type' => 'inspection',
        ]);
    }

    /**
     * Indicate that the appointment is for a repair.
     */
    public function repair(): static
    {
        return $this->state(fn (array $attributes) => [
            'appointment_type' => 'repair',
        ]);
    }

    /**
     * Indicate that the appointment is for maintenance.
     */
    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'appointment_type' => 'maintenance',
        ]);
    }

    /**
     * Indicate that the appointment is for a follow up.
     */
    public function followUp(): static
    {
        return $this->state(fn (array $attributes) => [
            'appointment_type' => 'follow_up',
        ]);
    }

    /**
     * Indicate that the appointment is upcoming (in the future).
     */
    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_time' => $this->faker->dateTimeBetween('now', '+30 days'),
            'status' => $this->faker->randomElement(['scheduled', 'confirmed']),
        ]);
    }

    /**
     * Indicate that the appointment is past.
     */
    public function past(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_time' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'status' => $this->faker->randomElement(['completed', 'cancelled', 'no_show']),
        ]);
    }

    /**
     * Indicate that the appointment is for today.
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_time' => $this->faker->dateTimeBetween('today', 'today +1 day'),
            'status' => $this->faker->randomElement(['scheduled', 'confirmed']),
        ]);
    }

    /**
     * Indicate that the appointment is for tomorrow.
     */
    public function tomorrow(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_time' => $this->faker->dateTimeBetween('tomorrow', 'tomorrow +1 day'),
            'status' => $this->faker->randomElement(['scheduled', 'confirmed']),
        ]);
    }

    /**
     * Indicate that the appointment is for a lead (not a customer).
     */
    public function forLead(): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_id' => null,
            'lead_id' => Lead::factory(),
        ]);
    }

    /**
     * Indicate that the appointment is unassigned.
     */
    public function unassigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_to' => null,
        ]);
    }
}
