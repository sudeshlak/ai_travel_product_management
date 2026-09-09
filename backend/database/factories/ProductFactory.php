<?php

namespace Database\Factories;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $validFrom = now()->subDays(7)->toDateString();
        $validUntil = now()->addDays(30)->toDateString();

        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'product_name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 10, 500),
            'inventory_count' => fake()->numberBetween(1, 100),
            'valid_from' => $validFrom,
            'valid_until' => $validUntil,
            'status' => ProductStatus::Active,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'status' => ProductStatus::Inactive,
        ]);
    }
}
