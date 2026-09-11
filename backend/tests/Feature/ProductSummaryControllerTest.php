<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductSummaryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_summary_requires_authentication(): void
    {
        $this->getJson('/api/v1/products/summary')->assertUnauthorized();
    }

    public function test_summary_returns_counts_for_owned_products_only(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        // Active + currently valid
        Product::factory()->for($owner)->create([
            'status' => ProductStatus::Active,
            'valid_from' => now()->subDays(2)->toDateString(),
            'valid_until' => now()->addDays(10)->toDateString(),
        ]);

        // Active + expired (counts toward both active and expired)
        Product::factory()->for($owner)->create([
            'status' => ProductStatus::Active,
            'valid_from' => now()->subDays(30)->toDateString(),
            'valid_until' => now()->subDays(1)->toDateString(),
        ]);

        // Inactive + currently valid
        Product::factory()->for($owner)->inactive()->create([
            'valid_from' => now()->subDays(2)->toDateString(),
            'valid_until' => now()->addDays(10)->toDateString(),
        ]);

        // Inactive + expired
        Product::factory()->for($owner)->inactive()->create([
            'valid_from' => now()->subDays(40)->toDateString(),
            'valid_until' => now()->subDays(5)->toDateString(),
        ]);

        // Other user's products must not affect the summary
        Product::factory()->for($other)->count(3)->create([
            'status' => ProductStatus::Active,
        ]);

        Sanctum::actingAs($owner);

        $this->getJson('/api/v1/products/summary')
            ->assertOk()
            ->assertJsonPath('data.total_products', 4)
            ->assertJsonPath('data.active_products', 2)
            ->assertJsonPath('data.expired_products', 2);
    }

    public function test_summary_returns_zeros_when_user_has_no_products(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/v1/products/summary')
            ->assertOk()
            ->assertJsonPath('data.total_products', 0)
            ->assertJsonPath('data.active_products', 0)
            ->assertJsonPath('data.expired_products', 0);
    }
}
