<?php

namespace Tests\Unit;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\DataTransferObjects\ProductData;
use App\DataTransferObjects\ProductSearchCriteria;
use App\Enums\ProductStatus;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
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

    public function test_update_finds_owned_product_then_updates(): void
    {
        $product = new Product;
        $product->id = 9;

        $data = new ProductData(
            productName: 'Updated',
            categoryId: 2,
            description: 'Desc',
            price: 20.5,
            inventoryCount: 4,
            validFrom: Carbon::parse('2026-01-01'),
            validUntil: Carbon::parse('2026-06-01'),
            status: ProductStatus::Active,
            destinationIds: [1, 3],
        );

        $updated = new Product;
        $updated->id = 9;

        $this->products
            ->shouldReceive('findOwnedOrFail')
            ->once()
            ->with(9, 4)
            ->andReturn($product);

        $this->products
            ->shouldReceive('update')
            ->once()
            ->with($product, $data)
            ->andReturn($updated);

        $this->assertSame($updated, $this->service->update(9, $data, 4));
    }

    public function test_create_forwards_data_and_user_id_to_repository(): void
    {
        $data = new ProductData(
            productName: 'Created',
            categoryId: 1,
            description: 'New product',
            price: 99.0,
            inventoryCount: 3,
            validFrom: Carbon::parse('2026-01-01'),
            validUntil: Carbon::parse('2026-12-31'),
            status: ProductStatus::Active,
            destinationIds: [2],
        );

        $created = new Product;
        $created->id = 21;

        $this->products
            ->shouldReceive('create')
            ->once()
            ->with($data, 5)
            ->andReturn($created);

        $this->assertSame($created, $this->service->create($data, 5));
    }
}
