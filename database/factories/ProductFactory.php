<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = ['Mouse', 'Teclado', 'Monitor', 'Headset', 'SSD', 'HD', 'Memória RAM', 'Placa de Vídeo', 'Processador', 'Fonte', 'Gabinete', 'Cooler', 'Webcam', 'Mousepad', 'Cabo HDMI', 'Hub USB', 'Adaptador', 'Pen Drive', 'Carregador', 'Suporte'];
        $adjectives = ['Gamer', 'Professional', 'Pro', 'Ultra', 'Premium', 'Basic', 'Advanced', 'RGB', 'Wireless', 'Mecânico'];
        $colors = ['Preto', 'Branco', 'Vermelho', 'Azul', 'Verde', 'RGB', 'Cinza', 'Prata', 'N/A'];

        $category = fake()->randomElement($categories);
        $adjective = fake()->randomElement($adjectives);

        return [
            'supplier_id' => Supplier::factory(),
            'reference' => strtoupper(fake()->bothify('??##-???-###')),
            'name' => $category . ' ' . $adjective,
            'color' => fake()->randomElement($colors),
            'price' => fake()->randomFloat(2, 29.90, 3999.90),
        ];
    }
}

