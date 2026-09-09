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
        return $this->products->paginate($criteria);
    }

    public function find(int $id): Product
    {
        return $this->products->findOrFail($id);
    }

    public function create(ProductData $data): Product
    {
        // TODO: implement
        throw new \BadMethodCallException('Not implemented');
    }

    public function update(int $id, ProductData $data): Product
    {
        // TODO: implement
        throw new \BadMethodCallException('Not implemented');
    }

    public function delete(int $id, int $userId): void
    {
        $product = $this->products->findOwnedOrFail($id, $userId);
        $this->products->delete($product);
    }
}
