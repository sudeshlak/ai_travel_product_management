<?php

namespace Tests\Feature;

use App\Contracts\Integrations\ProductDescriptionPolisherInterface;
use App\DataTransferObjects\ProductDescriptionPolishInput;
use App\Exceptions\IntegrationException;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductDescriptionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_description_requires_authentication(): void
    {
        $this->bindPolisher(fn () => 'polished');

        $this->postJson('/api/v1/products/generate-description', [
            'description' => 'a great trip through the mountains',
        ])->assertUnauthorized();
    }

    public function test_generate_description_rejects_too_few_words(): void
    {
        $this->bindPolisher(fn () => 'polished');

        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/products/generate-description', [
            'description' => 'too few words',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['description']);
    }

    public function test_generate_description_rejects_overlong_description(): void
    {
        $this->bindPolisher(fn () => 'polished');

        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/products/generate-description', [
            'description' => str_repeat('word ', 500).'end',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['description']);
    }

    public function test_generate_description_returns_polished_text(): void
    {
        $this->bindPolisher(function (ProductDescriptionPolishInput $input): string {
            $this->assertSame('a great trip through the mountains', $input->description);
            $this->assertSame('Alpine Escape', $input->productName);
            $this->assertSame('Tours', $input->category);

            return 'Experience an unforgettable journey through the mountains.';
        });

        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/products/generate-description', [
            'description' => 'a great trip through the mountains',
            'product_name' => 'Alpine Escape',
            'category' => 'Tours',
        ])
            ->assertOk()
            ->assertJsonPath(
                'data.description',
                'Experience an unforgettable journey through the mountains.',
            );
    }

    public function test_generate_description_returns_bad_gateway_on_integration_failure(): void
    {
        $this->app->bind(ProductDescriptionPolisherInterface::class, fn () => new class implements ProductDescriptionPolisherInterface
        {
            public function polish(ProductDescriptionPolishInput $input): string
            {
                throw new IntegrationException('provider down');
            }
        });

        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/products/generate-description', [
            'description' => 'a great trip through the mountains',
        ])
            ->assertStatus(502)
            ->assertJsonPath('message', 'Unable to generate description. Please try again.');
    }

    /**
     * @param  callable(ProductDescriptionPolishInput): string  $callback
     */
    private function bindPolisher(callable $callback): void
    {
        $this->app->bind(ProductDescriptionPolisherInterface::class, fn () => new class($callback) implements ProductDescriptionPolisherInterface
        {
            /**
             * @param  callable(ProductDescriptionPolishInput): string  $callback
             */
            public function __construct(private $callback) {}

            public function polish(ProductDescriptionPolishInput $input): string
            {
                return ($this->callback)($input);
            }
        });
    }
}
