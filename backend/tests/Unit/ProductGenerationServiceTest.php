<?php

namespace Tests\Unit;

use App\Contracts\Integrations\ProductGeneratorInterface;
use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\DataTransferObjects\GeneratedProductData;
use App\DataTransferObjects\ProductGenerateInput;
use App\Exceptions\IntegrationException;
use App\Models\Category;
use App\Services\ProductGenerationService;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class ProductGenerationServiceTest extends TestCase
{
    private CategoryRepositoryInterface&MockInterface $categories;

    private ProductGeneratorInterface&MockInterface $generator;

    private ProductGenerationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categories = Mockery::mock(CategoryRepositoryInterface::class);
        $this->generator = Mockery::mock(ProductGeneratorInterface::class);
        $this->service = new ProductGenerationService($this->categories, $this->generator);
    }

    public function test_generate_passes_allowed_categories_to_generator(): void
    {
        $food = $this->category(4, 'Food');

        $this->categories
            ->shouldReceive('allOrderedByName')
            ->once()
            ->andReturn(Collection::make([$food]));

        $this->generator
            ->shouldReceive('generate')
            ->once()
            ->with(Mockery::on(function (ProductGenerateInput $input) {
                return $input->prompt === 'Create a dinner buffet at Cinnamon Grand Colombo'
                    && $input->categories === [['id' => 4, 'name' => 'Food']];
            }))
            ->andReturn(new GeneratedProductData(
                productName: 'Dinner Buffet',
                description: 'A buffet with Highlights, Inclusions, and Tags.',
                categoryId: 4,
            ));

        $result = $this->service->generate('Create a dinner buffet at Cinnamon Grand Colombo');

        $this->assertSame('Dinner Buffet', $result->productName);
        $this->assertSame('A buffet with Highlights, Inclusions, and Tags.', $result->description);
        $this->assertSame(4, $result->categoryId);
    }

    public function test_generate_truncates_overlong_description_and_name(): void
    {
        $food = $this->category(4, 'Food');

        $this->categories
            ->shouldReceive('allOrderedByName')
            ->once()
            ->andReturn(Collection::make([$food]));

        $longDescription = str_repeat('a', ProductGenerationService::MAX_DESCRIPTION_LENGTH + 50);
        $longName = str_repeat('b', ProductGenerationService::MAX_PRODUCT_NAME_LENGTH + 20);

        $this->generator
            ->shouldReceive('generate')
            ->once()
            ->andReturn(new GeneratedProductData(
                productName: $longName,
                description: $longDescription,
                categoryId: 4,
            ));

        $result = $this->service->generate('Create a dinner buffet at Cinnamon Grand Colombo');

        $this->assertSame(ProductGenerationService::MAX_PRODUCT_NAME_LENGTH, mb_strlen($result->productName));
        $this->assertSame(mb_substr($longName, 0, ProductGenerationService::MAX_PRODUCT_NAME_LENGTH), $result->productName);
        $this->assertSame(ProductGenerationService::MAX_DESCRIPTION_LENGTH, mb_strlen($result->description));
        $this->assertSame(mb_substr($longDescription, 0, ProductGenerationService::MAX_DESCRIPTION_LENGTH), $result->description);
    }

    public function test_generate_throws_when_category_id_is_not_in_catalog(): void
    {
        $food = $this->category(4, 'Food');

        $this->categories
            ->shouldReceive('allOrderedByName')
            ->once()
            ->andReturn(Collection::make([$food]));

        $this->generator
            ->shouldReceive('generate')
            ->once()
            ->andReturn(new GeneratedProductData(
                productName: 'Dinner Buffet',
                description: 'A buffet.',
                categoryId: 99,
            ));

        $this->expectException(IntegrationException::class);

        $this->service->generate('Create a dinner buffet at Cinnamon Grand Colombo');
    }

    public function test_generate_throws_when_no_categories_exist(): void
    {
        $this->categories
            ->shouldReceive('allOrderedByName')
            ->once()
            ->andReturn(Collection::make());

        $this->generator->shouldNotReceive('generate');

        $this->expectException(IntegrationException::class);

        $this->service->generate('Create a dinner buffet at Cinnamon Grand Colombo');
    }

    public function test_generate_propagates_integration_exception(): void
    {
        $food = $this->category(4, 'Food');

        $this->categories
            ->shouldReceive('allOrderedByName')
            ->once()
            ->andReturn(Collection::make([$food]));

        $this->generator
            ->shouldReceive('generate')
            ->once()
            ->andThrow(new IntegrationException('boom'));

        $this->expectException(IntegrationException::class);

        $this->service->generate('Create a dinner buffet at Cinnamon Grand Colombo');
    }

    private function category(int $id, string $name): Category
    {
        $category = new Category(['name' => $name]);
        $category->id = $id;

        return $category;
    }
}
