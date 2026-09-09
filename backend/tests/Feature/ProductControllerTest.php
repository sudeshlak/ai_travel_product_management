<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Models\Category;
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

    public function test_index_returns_all_authenticated_users_products_including_inactive(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $ownedActive = Product::factory()->for($owner)->create([
            'product_name' => 'Owner Active',
            'status' => ProductStatus::Active,
        ]);
        $destination = Destination::factory()->create(['name' => 'Colombo']);
        $ownedActive->destinations()->attach($destination);

        $ownedInactive = Product::factory()->for($owner)->inactive()->create([
            'product_name' => 'Owner Inactive',
        ]);
        Product::factory()->for($other)->create([
            'product_name' => 'Other Active',
            'status' => ProductStatus::Active,
        ]);

        Sanctum::actingAs($owner);

        $response = $this->getJson('/api/v1/products');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 15)
            ->assertJsonPath('meta.total', 2)
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
                    'destinations',
                ]],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);

        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertContains($ownedActive->id, $ids);
        $this->assertContains($ownedInactive->id, $ids);

        $activeRow = collect($response->json('data'))->firstWhere('id', $ownedActive->id);
        $this->assertSame('Owner Active', $activeRow['product_name']);
        $this->assertSame('Active', $activeRow['status']);
        $this->assertSame($ownedActive->category_id, $activeRow['category']['id']);
        $this->assertSame('Colombo', $activeRow['destinations'][0]['name']);

        $inactiveRow = collect($response->json('data'))->firstWhere('id', $ownedInactive->id);
        $this->assertSame('Owner Inactive', $inactiveRow['product_name']);
        $this->assertSame('Inactive', $inactiveRow['status']);
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

    public function test_products_show_requires_authentication(): void
    {
        $this->getJson('/api/v1/products/1')->assertUnauthorized();
    }

    public function test_show_returns_owned_product_with_relations(): void
    {
        $owner = User::factory()->create();
        $category = Category::factory()->create(['name' => 'Hotels']);
        $product = Product::factory()->for($owner)->for($category)->create([
            'product_name' => 'Show Product',
            'description' => 'Show description',
            'price' => 150.50,
            'inventory_count' => 8,
            'valid_from' => '2026-01-01',
            'valid_until' => '2026-12-31',
            'status' => ProductStatus::Active,
        ]);
        $destination = Destination::factory()->create(['name' => 'Galle']);
        $product->destinations()->attach($destination);

        Sanctum::actingAs($owner);

        $this->getJson('/api/v1/products/'.$product->id)
            ->assertOk()
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonPath('data.product_name', 'Show Product')
            ->assertJsonPath('data.description', 'Show description')
            ->assertJsonPath('data.price', '150.50')
            ->assertJsonPath('data.inventory_count', 8)
            ->assertJsonPath('data.valid_from', '2026-01-01')
            ->assertJsonPath('data.valid_until', '2026-12-31')
            ->assertJsonPath('data.status', 'Active')
            ->assertJsonPath('data.category.id', $category->id)
            ->assertJsonPath('data.category.name', 'Hotels')
            ->assertJsonPath('data.destinations.0.id', $destination->id)
            ->assertJsonPath('data.destinations.0.name', 'Galle');
    }

    public function test_show_returns_404_for_another_users_product(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $product = Product::factory()->for($other)->create();

        Sanctum::actingAs($owner);

        $this->getJson('/api/v1/products/'.$product->id)
            ->assertNotFound();
    }

    public function test_show_returns_404_for_missing_product(): void
    {
        $owner = User::factory()->create();

        Sanctum::actingAs($owner);

        $this->getJson('/api/v1/products/999999')
            ->assertNotFound();
    }

    public function test_products_update_requires_authentication(): void
    {
        $this->putJson('/api/v1/products/1', [])->assertUnauthorized();
    }

    public function test_update_updates_owned_product_fields_and_destinations(): void
    {
        $owner = User::factory()->create();
        $product = Product::factory()->for($owner)->create([
            'product_name' => 'Old Name',
            'description' => 'Old description',
            'price' => 50,
            'inventory_count' => 5,
            'status' => ProductStatus::Active,
        ]);
        $oldDestination = Destination::factory()->create(['name' => 'Old Dest']);
        $product->destinations()->attach($oldDestination);

        $newCategory = Category::factory()->create(['name' => 'Tours']);
        $newDestination = Destination::factory()->create(['name' => 'Kandy']);

        Sanctum::actingAs($owner);

        $response = $this->putJson('/api/v1/products/'.$product->id, [
            'product_name' => 'Updated Name',
            'category_id' => $newCategory->id,
            'description' => 'Updated description',
            'price' => 199.99,
            'inventory_count' => 12,
            'valid_from' => '2026-02-01',
            'valid_until' => '2026-12-31',
            'status' => 'Inactive',
            'destination_ids' => [$newDestination->id],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.product_name', 'Updated Name')
            ->assertJsonPath('data.description', 'Updated description')
            ->assertJsonPath('data.price', '199.99')
            ->assertJsonPath('data.inventory_count', 12)
            ->assertJsonPath('data.valid_from', '2026-02-01')
            ->assertJsonPath('data.valid_until', '2026-12-31')
            ->assertJsonPath('data.status', 'Inactive')
            ->assertJsonPath('data.category.id', $newCategory->id)
            ->assertJsonPath('data.category.name', 'Tours')
            ->assertJsonPath('data.destinations.0.id', $newDestination->id)
            ->assertJsonPath('data.destinations.0.name', 'Kandy');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'user_id' => $owner->id,
            'product_name' => 'Updated Name',
            'category_id' => $newCategory->id,
            'description' => 'Updated description',
            'status' => 'Inactive',
        ]);

        $this->assertDatabaseMissing('destination_product', [
            'product_id' => $product->id,
            'destination_id' => $oldDestination->id,
        ]);
        $this->assertDatabaseHas('destination_product', [
            'product_id' => $product->id,
            'destination_id' => $newDestination->id,
        ]);
    }

    public function test_update_validation_failure_returns_422(): void
    {
        $owner = User::factory()->create();
        $product = Product::factory()->for($owner)->create();

        Sanctum::actingAs($owner);

        $this->putJson('/api/v1/products/'.$product->id, [
            'product_name' => '',
            'valid_from' => '2026-12-31',
            'valid_until' => '2026-01-01',
            'destination_ids' => [],
        ])->assertUnprocessable()
            ->assertJsonValidationErrors([
                'product_name',
                'category_id',
                'description',
                'price',
                'inventory_count',
                'valid_until',
                'status',
                'destination_ids',
            ]);
    }

    public function test_update_returns_404_for_another_users_product(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $product = Product::factory()->for($other)->create([
            'product_name' => 'Other Product',
        ]);
        $category = Category::factory()->create();
        $destination = Destination::factory()->create();

        Sanctum::actingAs($owner);

        $this->putJson('/api/v1/products/'.$product->id, [
            'product_name' => 'Hijacked',
            'category_id' => $category->id,
            'description' => 'Should not apply',
            'price' => 10,
            'inventory_count' => 1,
            'valid_from' => '2026-01-01',
            'valid_until' => '2026-12-31',
            'status' => 'Active',
            'destination_ids' => [$destination->id],
        ])->assertNotFound();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'user_id' => $other->id,
            'product_name' => 'Other Product',
        ]);
    }

    public function test_products_store_requires_authentication(): void
    {
        $this->postJson('/api/v1/products', [])->assertUnauthorized();
    }

    public function test_store_creates_product_for_authenticated_user(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $category = Category::factory()->create(['name' => 'Adventure']);
        $destinationA = Destination::factory()->create(['name' => 'Colombo']);
        $destinationB = Destination::factory()->create(['name' => 'Galle']);

        Sanctum::actingAs($owner);

        $response = $this->postJson('/api/v1/products', [
            'product_name' => 'New Tour',
            'category_id' => $category->id,
            'description' => 'A great tour',
            'price' => 150.5,
            'inventory_count' => 8,
            'valid_from' => '2026-03-01',
            'valid_until' => '2026-09-30',
            'status' => 'Active',
            'destination_ids' => [$destinationA->id, $destinationB->id],
            'user_id' => $other->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.product_name', 'New Tour')
            ->assertJsonPath('data.description', 'A great tour')
            ->assertJsonPath('data.price', '150.50')
            ->assertJsonPath('data.inventory_count', 8)
            ->assertJsonPath('data.valid_from', '2026-03-01')
            ->assertJsonPath('data.valid_until', '2026-09-30')
            ->assertJsonPath('data.status', 'Active')
            ->assertJsonPath('data.category.id', $category->id)
            ->assertJsonPath('data.category.name', 'Adventure')
            ->assertJsonCount(2, 'data.destinations');

        $productId = $response->json('data.id');

        $this->assertDatabaseHas('products', [
            'id' => $productId,
            'user_id' => $owner->id,
            'product_name' => 'New Tour',
            'category_id' => $category->id,
            'description' => 'A great tour',
            'status' => 'Active',
        ]);

        $this->assertDatabaseHas('destination_product', [
            'product_id' => $productId,
            'destination_id' => $destinationA->id,
        ]);
        $this->assertDatabaseHas('destination_product', [
            'product_id' => $productId,
            'destination_id' => $destinationB->id,
        ]);
    }

    public function test_store_validation_failure_returns_422(): void
    {
        $owner = User::factory()->create();

        Sanctum::actingAs($owner);

        $this->postJson('/api/v1/products', [
            'product_name' => '',
            'valid_from' => '2026-12-31',
            'valid_until' => '2026-01-01',
            'destination_ids' => [],
        ])->assertUnprocessable()
            ->assertJsonValidationErrors([
                'product_name',
                'category_id',
                'description',
                'price',
                'inventory_count',
                'valid_until',
                'status',
                'destination_ids',
            ]);
    }
}
