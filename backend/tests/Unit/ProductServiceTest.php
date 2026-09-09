<?php

namespace Tests\Unit;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\DataTransferObjects\ProductSearchCriteria;
use App\Enums\ProductStatus;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    private ProductRepositoryInterface&MockInterface $products;

    private ProductService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->products = Mockery::mock(ProductRepositoryInterface::class);
        $this->service = new ProductService($this->products);
    }

    public function test_list_forwards_criteria_to_repository(): void
    {
        $criteria = new ProductSearchCriteria(
            status: ProductStatus::Active,
            onlyValid: false,
            userId: 7,
            page: 2,
            perPage: 10,
        );

        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $this->products
            ->shouldReceive('paginate')
            ->once()
            ->with(Mockery::on(fn (ProductSearchCriteria $passed) => $passed === $criteria))
            ->andReturn($paginator);

        $this->assertSame($paginator, $this->service->list($criteria));
    }

    public function test_delete_finds_owned_product_then_deletes(): void
    {
        $product = new Product;
        $product->id = 15;

        $this->products
            ->shouldReceive('findOwnedOrFail')
            ->once()
            ->with(15, 3)
            ->andReturn($product);

        $this->products
            ->shouldReceive('delete')
            ->once()
            ->with($product);

        $this->service->delete(15, 3);
    }
}
