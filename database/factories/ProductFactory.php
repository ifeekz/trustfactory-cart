<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->words(2, true)),
            'price' => $this->faker->numberBetween(500, 5000), // cents
            'stock_quantity' => $this->faker->numberBetween(1, 50),
        ];
    }

    public function lowStock(): static
    {
        return $this->state(fn() => [
            'stock_quantity' => config('shop.low_stock_threshold'),
        ]);
    }
}
