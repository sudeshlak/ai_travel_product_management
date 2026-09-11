<?php

namespace Tests\Feature;

use App\Contracts\Integrations\ProductGeneratorInterface;
use App\DataTransferObjects\GeneratedProductData;
use App\DataTransferObjects\ProductGenerateInput;
use App\Exceptions\IntegrationException;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductGenerationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_requires_authentication(): void
    {
        $this->bindGenerator(fn () => $this->generatedProduct(1));

        $this->postJson('/api/v1/products/generate', [
            'prompt' => 'Create a dinner buffet at Cinnamon Grand',
        ])->assertUnauthorized();
    }

    public function test_generate_rejects_too_few_words(): void
    {
        $this->bindGenerator(fn () => $this->generatedProduct(1));

        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/products/generate', [
            'prompt' => 'too few words',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['prompt']);
    }

    public function test_generate_rejects_overlong_prompt(): void
    {
        $this->bindGenerator(fn () => $this->generatedProduct(1));

        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/products/generate', [
            'prompt' => str_repeat('word ', 500).'end',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['prompt']);
    }

    public function test_generate_returns_generated_product_fields(): void
    {
        $category = Category::factory()->create(['name' => 'Food']);

        $this->bindGenerator(function (ProductGenerateInput $input) use ($category): GeneratedProductData {
            $this->assertSame('Create a dinner buffet at Cinnamon Grand Colombo', $input->prompt);
            $this->assertSame(
                [['id' => $category->id, 'name' => 'Food']],
                $input->categories,
            );

            return $this->generatedProduct($category->id);
        });

        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/products/generate', [
            'prompt' => 'Create a dinner buffet at Cinnamon Grand Colombo',
        ])
            ->assertOk()
            ->assertJsonPath('data.product_name', 'Dinner Buffet at Cinnamon Grand Colombo')
            ->assertJsonPath(
                'data.description',
                "A dinner buffet.\n\nHighlights: Live stations\nInclusions: Soft drinks\nTags: buffet, colombo",
            )
            ->assertJsonPath('data.category_id', $category->id);
    }

    public function test_generate_returns_bad_gateway_on_integration_failure(): void
    {
        Category::factory()->create(['name' => 'Food']);

        $this->app->bind(ProductGeneratorInterface::class, fn () => new class implements ProductGeneratorInterface
        {
            public function generate(ProductGenerateInput $input): GeneratedProductData
            {
                throw new IntegrationException('provider down');
            }
        });

        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/products/generate', [
            'prompt' => 'Create a dinner buffet at Cinnamon Grand Colombo',
        ])
            ->assertStatus(502)
            ->assertJsonPath('message', 'Unable to generate product. Please try again.');
    }

    /**
     * @param  callable(ProductGenerateInput): GeneratedProductData  $callback
     */
    private function bindGenerator(callable $callback): void
    {
        $this->app->bind(ProductGeneratorInterface::class, fn () => new class($callback) implements ProductGeneratorInterface
        {
            /**
             * @param  callable(ProductGenerateInput): GeneratedProductData  $callback
             */
            public function __construct(private $callback) {}

            public function generate(ProductGenerateInput $input): GeneratedProductData
            {
                return ($this->callback)($input);
            }
        });
    }

    private function generatedProduct(int $categoryId): GeneratedProductData
    {
        return new GeneratedProductData(
            productName: 'Dinner Buffet at Cinnamon Grand Colombo',
            description: "A dinner buffet.\n\nHighlights: Live stations\nInclusions: Soft drinks\nTags: buffet, colombo",
            categoryId: $categoryId,
        );
    }
}
