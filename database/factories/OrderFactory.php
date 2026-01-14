<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'supplier_id' => Supplier::factory(),
            'date' => fake()->dateTimeBetween('-6 months', 'now'),
            'total_amount' => 0,
            'observation' => fake()->optional(0.5)->sentence(),
            'status' => fake()->randomElement(['Pendente', 'Concluído', 'Cancelado']),
        ];
    }

    /**
     * Indicate that the order is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Pendente',
        ]);
    }

    /**
     * Indicate that the order is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Concluído',
        ]);
    }

    /**
     * Indicate that the order is canceled.
     */
    public function canceled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Cancelado',
        ]);
    }
}

