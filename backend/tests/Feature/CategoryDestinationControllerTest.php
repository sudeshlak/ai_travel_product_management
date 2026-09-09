<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Destination;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CategoryDestinationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_index_requires_authentication(): void
    {
        $this->getJson('/api/v1/categories')->assertUnauthorized();
    }

    public function test_destinations_index_requires_authentication(): void
    {
        $this->getJson('/api/v1/destinations')->assertUnauthorized();
    }

    public function test_categories_index_returns_ordered_named_resources(): void
    {
        $user = User::factory()->create();
        Category::factory()->create(['name' => 'Wellness']);
        Category::factory()->create(['name' => 'Adventure']);
        $deleted = Category::factory()->create(['name' => 'Hidden']);
        $deleted->delete();

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/categories')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Adventure')
            ->assertJsonPath('data.1.name', 'Wellness')
            ->assertJsonStructure([
                'data' => [['id', 'name']],
            ])
            ->assertJsonMissing(['name' => 'Hidden']);
    }

    public function test_destinations_index_returns_ordered_named_resources(): void
    {
        $user = User::factory()->create();
        Destination::factory()->create(['name' => 'Sigiriya']);
        Destination::factory()->create(['name' => 'Colombo']);
        $deleted = Destination::factory()->create(['name' => 'Hidden']);
        $deleted->delete();

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/destinations')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Colombo')
            ->assertJsonPath('data.1.name', 'Sigiriya')
            ->assertJsonStructure([
                'data' => [['id', 'name']],
            ])
            ->assertJsonMissing(['name' => 'Hidden']);
    }
}
