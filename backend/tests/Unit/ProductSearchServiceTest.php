<?php

namespace Tests\Unit;

use App\Contracts\Integrations\SearchQueryInterpreterInterface;
use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Repositories\DestinationRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\DataTransferObjects\ProductSearchCriteria;
use App\DataTransferObjects\ProductSearchFilters;
use App\Enums\ProductStatus;
use App\Exceptions\IntegrationException;
use App\Models\Category;
use App\Models\Destination;
use App\Services\ProductSearchService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class ProductSearchServiceTest extends TestCase
{
    private ProductRepositoryInterface&MockInterface $products;

    private CategoryRepositoryInterface&MockInterface $categories;

    private DestinationRepositoryInterface&MockInterface $destinations;

    private SearchQueryInterpreterInterface&MockInterface $interpreter;

    private ProductSearchService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->products = Mockery::mock(ProductRepositoryInterface::class);
        $this->categories = Mockery::mock(CategoryRepositoryInterface::class);
        $this->destinations = Mockery::mock(DestinationRepositoryInterface::class);
        $this->interpreter = Mockery::mock(SearchQueryInterpreterInterface::class);

        $this->service = new ProductSearchService(
            $this->products,
            $this->categories,
            $this->destinations,
            $this->interpreter,
        );
    }

    public function test_empty_query_skips_ai_and_browses_active_valid_products(): void
    {
        $this->interpreter->shouldNotReceive('interpret');
        $this->categories->shouldNotReceive('findByName');
        $this->destinations->shouldNotReceive('findByName');

        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $this->products
            ->shouldReceive('paginate')
            ->once()
            ->with(Mockery::on(function (ProductSearchCriteria $criteria) {
                return $criteria->keyword === null
                    && $criteria->categoryId === null
                    && $criteria->destinationIds === []
                    && $criteria->minPrice === null
                    && $criteria->maxPrice === null
                    && $criteria->status === ProductStatus::Active
                    && $criteria->onlyValid === true
                    && $criteria->onlyInStock === true
                    && $criteria->userId === null
                    && $criteria->page === 1
                    && $criteria->perPage === 8;
            }))
            ->andReturn($paginator);

        $this->assertSame($paginator, $this->service->search('   ', 1, 8));
    }

    public function test_non_empty_query_resolves_category_and_destination_names_to_ids(): void
    {
        $filters = new ProductSearchFilters(
            keyword: 'dinner buffet',
            categoryName: 'Dining',
            destinationName: 'Colombo',
            minPrice: null,
            maxPrice: 10000.0,
        );

        $this->interpreter
            ->shouldReceive('interpret')
            ->once()
            ->with('dinner buffets in Colombo')
            ->andReturn($filters);

        $category = new Category;
        $category->id = 4;

        $destination = new Destination;
        $destination->id = 9;

        $this->categories
            ->shouldReceive('findByName')
            ->once()
            ->with('Dining')
            ->andReturn($category);

        $this->destinations
            ->shouldReceive('findByName')
            ->once()
            ->with('Colombo')
            ->andReturn($destination);

        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $this->products
            ->shouldReceive('paginate')
            ->once()
            ->with(Mockery::on(function (ProductSearchCriteria $criteria) {
                return $criteria->keyword === 'dinner buffet'
                    && $criteria->categoryId === 4
                    && $criteria->destinationIds === [9]
                    && $criteria->maxPrice === 10000.0
                    && $criteria->status === ProductStatus::Active
                    && $criteria->onlyValid === true
                    && $criteria->onlyInStock === true
                    && $criteria->userId === null;
            }))
            ->andReturn($paginator);

        $this->assertSame($paginator, $this->service->search('dinner buffets in Colombo', 1, 15));
    }

    public function test_unmatched_names_are_dropped_without_failing(): void
    {
        $filters = new ProductSearchFilters(
            categoryName: 'Nonexistent Category',
            destinationName: 'Nowhere',
        );

        $this->interpreter->shouldReceive('interpret')->once()->andReturn($filters);

        $this->categories->shouldReceive('findByName')->once()->with('Nonexistent Category')->andReturnNull();
        $this->destinations->shouldReceive('findByName')->once()->with('Nowhere')->andReturnNull();

        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $this->products
            ->shouldReceive('paginate')
            ->once()
            ->with(Mockery::on(function (ProductSearchCriteria $criteria) {
                return $criteria->categoryId === null && $criteria->destinationIds === [];
            }))
            ->andReturn($paginator);

        $this->service->search('something obscure', 1, 15);
    }

    public function test_interpreter_failure_falls_back_to_plain_keyword_search(): void
    {
        $this->interpreter
            ->shouldReceive('interpret')
            ->once()
            ->with('show me something')
            ->andThrow(new IntegrationException('boom'));

        $this->categories->shouldNotReceive('findByName');
        $this->destinations->shouldNotReceive('findByName');

        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $this->products
            ->shouldReceive('paginate')
            ->once()
            ->with(Mockery::on(function (ProductSearchCriteria $criteria) {
                return $criteria->keyword === 'show me something'
                    && $criteria->status === ProductStatus::Active
                    && $criteria->onlyValid === true
                    && $criteria->onlyInStock === true;
            }))
            ->andReturn($paginator);

        $this->assertSame($paginator, $this->service->search('show me something', 1, 15));
    }

    public function test_status_validity_and_in_stock_are_always_forced_regardless_of_filters(): void
    {
        $filters = new ProductSearchFilters(keyword: 'anything');

        $this->interpreter->shouldReceive('interpret')->once()->andReturn($filters);

        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $this->products
            ->shouldReceive('paginate')
            ->once()
            ->with(Mockery::on(function (ProductSearchCriteria $criteria) {
                return $criteria->status === ProductStatus::Active
                    && $criteria->onlyValid === true
                    && $criteria->onlyInStock === true;
            }))
            ->andReturn($paginator);

        $this->service->search('anything', 3, 20);
    }
}
