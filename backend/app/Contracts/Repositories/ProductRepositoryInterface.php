<?php

namespace App\Contracts\Repositories;

use App\DataTransferObjects\ProductData;
use App\DataTransferObjects\ProductSearchCriteria;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    /**
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginate(ProductSearchCriteria $criteria): LengthAwarePaginator;

    public function findOrFail(int $id): Product;

    public function findOwnedOrFail(int $id, int $userId): Product;

    public function create(ProductData $data, int $userId): Product;

    public function update(Product $product, ProductData $data): Product;

    public function delete(Product $product): void;
}
