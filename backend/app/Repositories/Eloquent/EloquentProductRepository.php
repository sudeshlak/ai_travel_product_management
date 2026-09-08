<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\DataTransferObjects\ProductData;
use App\DataTransferObjects\ProductSearchCriteria;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentProductRepository implements ProductRepositoryInterface
{
    /**
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginate(ProductSearchCriteria $criteria): LengthAwarePaginator
    {
        // TODO: implement
    }

    public function findOrFail(int $id): Product
    {
        // TODO: implement
    }

    public function create(ProductData $data): Product
    {
        // TODO: implement
    }

    public function update(Product $product, ProductData $data): Product
    {
        // TODO: implement
    }

    public function delete(Product $product): void
    {
        // TODO: implement
    }
}
