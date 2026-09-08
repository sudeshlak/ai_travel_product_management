<?php

namespace App\Services;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\DataTransferObjects\ProductData;
use App\DataTransferObjects\ProductSearchCriteria;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductService
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
    ) {}

    /**
     * @return LengthAwarePaginator<int, Product>
     */
    public function list(ProductSearchCriteria $criteria): LengthAwarePaginator
    {
        // TODO: implement
    }

    public function find(int $id): Product
    {
        // TODO: implement
    }

    public function create(ProductData $data): Product
    {
        // TODO: implement
    }

    public function update(int $id, ProductData $data): Product
    {
        // TODO: implement
    }

    public function delete(int $id): void
    {
        // TODO: implement
    }
}
