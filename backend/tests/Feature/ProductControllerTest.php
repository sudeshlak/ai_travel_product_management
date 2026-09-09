<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Models\Destination;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_index_requires_authentication(): void
    {
        $this->getJson('/api/v1/products')->assertUnauthorized();
    }

    public function test_products_destroy_requires_authentication(): void
    {
        $this->deleteJson('/api/v1/products/1')->assertUnauthorized();
    }

    public function test_index_returns_only_authenticated_users_active_products(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $ownedActive = Product::factory()->for($owner)->create([
            'product_name' => 'Owner Active',
            'status' => ProductStatus::Active,
        ]);
        $destination = Destination::factory()->create(['name' => 'Colombo']);
        $ownedActive->destinations()->attach($destination);

        Product::factory()->for($owner)->inactive()->create([
            'product_name' => 'Owner Inactive',
        ]);
        Product::factory()->for($other)->create([
            'product_name' => 'Other Active',
            'status' => ProductStatus::Active,
        ]);

        Sanctum::actingAs($owner);

        $response = $this->getJson('/api/v1/products');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ownedActive->id)
            ->assertJsonPath('data.0.product_name', 'Owner Active')
            ->assertJsonPath('data.0.status', 'Active')
            ->assertJsonPath('data.0.category.id', $ownedActive->category_id)
            ->assertJsonPath('data.0.destinations.0.name', 'Colombo')
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 15)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'product_name',
                    'price',
                    'inventory_count',
                    'valid_from',
                    'valid_until',
                    'status',
                    'category' => ['id', 'name'],
                    'destinations' => [['id', 'name']],
                ]],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    public function test_index_supports_pagination(): void
    {
        $owner = User::factory()->create();
        Product::factory()->count(3)->for($owner)->create();

        Sanctum::actingAs($owner);

        $this->getJson('/api/v1/products?page=1&per_page=2')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonPath('meta.total', 3);
    }

    public function test_destroy_soft_deletes_owned_product(): void
    {
        $owner = User::factory()->create();
        $product = Product::factory()->for($owner)->create();

        Sanctum::actingAs($owner);

        $this->deleteJson('/api/v1/products/'.$product->id)
            ->assertNoContent();

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_destroy_returns_404_for_another_users_product(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $product = Product::factory()->for($other)->create();

        Sanctum::actingAs($owner);

        $this->deleteJson('/api/v1/products/'.$product->id)
            ->assertNotFound();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'deleted_at' => null,
        ]);
    }
}
