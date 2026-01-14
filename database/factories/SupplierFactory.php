<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Supplier>
 */
class SupplierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company() . ' ' . fake()->randomElement(['Ltda', 'S.A.', 'Distribuidora', 'Comércio']),
            'cnpj' => $this->generateCNPJ(),
            'cep' => fake()->numerify('########'),
            'address' => fake()->streetAddress() . ' - ' . fake()->city() . ' - ' . fake()->stateAbbr(),
            'status' => fake()->randomElement([1, 1, 1, 1, 0]), // 80% ativos, 20% inativos
        ];
    }

    /**
     * Indicate that the supplier is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 0,
        ]);
    }

    /**
     * Indicate that the supplier is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 1,
        ]);
    }

    /**
     * Generate a valid CNPJ format (not necessarily valid with check digits).
     */
    private function generateCNPJ(): string
    {
        return fake()->numerify('##############');
    }
}

