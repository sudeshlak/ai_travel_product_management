<?php

namespace Tests\Feature;

use App\Contracts\Integrations\SearchQueryInterpreterInterface;
use App\DataTransferObjects\ProductSearchFilters;
use App\Enums\ProductStatus;
use App\Exceptions\IntegrationException;
use App\Models\Destination;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use LogicException;
use Tests\TestCase;

class ProductSearchControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_requires_authentication(): void
    {
        $this->bindInterpreter(new ProductSearchFilters);

        $this->postJson('/api/v1/products/search', ['query' => ''])
            ->assertUnauthorized();
    }

    public function test_empty_query_browses_active_valid_products_across_users_without_calling_ai(): void
    {
        $this->bindInterpreterThatMustNotBeCalled();

        $owner = User::factory()->create();
        $another = User::factory()->create();

        $visibleA = Product::factory()->for($owner)->create([
            'product_name' => 'Owner Product',
            'status' => ProductStatus::Active,
        ]);
        $visibleB = Product::factory()->for($another)->create([
            'product_name' => 'Another User Product',
            'status' => ProductStatus::Active,
        ]);

        Sanctum::actingAs($owner);

        $response = $this->postJson('/api/v1/products/search', ['query' => '']);

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.current_page', 1);

        $ids = array_column($response->json('data'), 'id');
        $this->assertContains($visibleA->id, $ids);
        $this->assertContains($visibleB->id, $ids);
    }

    public function test_search_excludes_inactive_products(): void
    {
        $this->bindInterpreter(new ProductSearchFilters);

        $owner = User::factory()->create();
        Product::factory()->for($owner)->inactive()->create(['product_name' => 'Inactive Product']);
        $active = Product::factory()->for($owner)->create([
            'product_name' => 'Active Product',
            'status' => ProductStatus::Active,
        ]);

        Sanctum::actingAs($owner);

        $response = $this->postJson('/api/v1/products/search', ['query' => 'anything']);

        $response->assertOk()->assertJsonCount(1, 'data');
        $this->assertSame($active->id, $response->json('data.0.id'));
    }

    public function test_search_excludes_expired_products(): void
    {
        $this->bindInterpreter(new ProductSearchFilters);

        $owner = User::factory()->create();
        Product::factory()->for($owner)->create([
            'product_name' => 'Expired Product',
            'status' => ProductStatus::Active,
            'valid_from' => now()->subDays(60)->toDateString(),
            'valid_until' => now()->subDay()->toDateString(),
        ]);
        $valid = Product::factory()->for($owner)->create([
            'product_name' => 'Valid Product',
            'status' => ProductStatus::Active,
        ]);

        Sanctum::actingAs($owner);

        $response = $this->postJson('/api/v1/products/search', ['query' => 'anything']);

        $response->assertOk()->assertJsonCount(1, 'data');
        $this->assertSame($valid->id, $response->json('data.0.id'));
    }

    public function test_search_filters_by_destination_and_max_price_resolved_by_ai(): void
    {
        $colombo = Destination::factory()->create(['name' => 'Colombo']);
        $kandy = Destination::factory()->create(['name' => 'Kandy']);

        $this->bindInterpreter(new ProductSearchFilters(
            destinationName: 'Colombo',
            maxPrice: 10000.0,
        ));

        $owner = User::factory()->create();

        $match = Product::factory()->for($owner)->create([
            'product_name' => 'Colombo Cheap Product',
            'status' => ProductStatus::Active,
            'price' => 5000,
        ]);
        $match->destinations()->attach($colombo);

        $tooExpensive = Product::factory()->for($owner)->create([
            'product_name' => 'Colombo Pricey Product',
            'status' => ProductStatus::Active,
            'price' => 50000,
        ]);
        $tooExpensive->destinations()->attach($colombo);

        $wrongDestination = Product::factory()->for($owner)->create([
            'product_name' => 'Kandy Cheap Product',
            'status' => ProductStatus::Active,
            'price' => 3000,
        ]);
        $wrongDestination->destinations()->attach($kandy);

        Sanctum::actingAs($owner);

        $response = $this->postJson('/api/v1/products/search', ['query' => 'show me products below 10000 in Colombo']);

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $match->id);
    }

    public function test_search_falls_back_to_keyword_when_ai_interpretation_fails(): void
    {
        $this->bindInterpreter(new IntegrationException('provider unavailable'));

        $owner = User::factory()->create();

        $match = Product::factory()->for($owner)->create([
            'product_name' => 'Dinner Buffet Special',
            'status' => ProductStatus::Active,
        ]);
        $noMatch = Product::factory()->for($owner)->create([
            'product_name' => 'Airport Transfer',
            'status' => ProductStatus::Active,
        ]);

        Sanctum::actingAs($owner);

        $response = $this->postJson('/api/v1/products/search', ['query' => 'Dinner Buffet Special']);

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $match->id);
    }

    private function bindInterpreter(ProductSearchFilters|IntegrationException $result): void
    {
        $this->app->bind(SearchQueryInterpreterInterface::class, fn () => new FakeSearchQueryInterpreter($result));
    }

    private function bindInterpreterThatMustNotBeCalled(): void
    {
        $this->app->bind(
            SearchQueryInterpreterInterface::class,
            fn () => new FakeSearchQueryInterpreter(new LogicException('AI interpreter should not be called for an empty query.')),
        );
    }
}

class FakeSearchQueryInterpreter implements SearchQueryInterpreterInterface
{
    public function __construct(private readonly ProductSearchFilters|IntegrationException|LogicException $result) {}

    public function interpret(string $query): ProductSearchFilters
    {
        if ($this->result instanceof ProductSearchFilters) {
            return $this->result;
        }

        throw $this->result;
    }
}
