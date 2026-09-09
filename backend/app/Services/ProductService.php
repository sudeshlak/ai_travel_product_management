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

    public function findOwned(int $id, int $userId): Product
    {
        return $this->products->findOwnedOrFail($id, $userId);
    }

    public function create(ProductData $data, int $userId): Product
    {
        return $this->products->create($data, $userId);
    }

    public function update(int $id, ProductData $data, int $userId): Product
    {
        $product = $this->products->findOwnedOrFail($id, $userId);

        return $this->products->update($product, $data);
    }

    public function delete(int $id, int $userId): void
    {
        $product = $this->products->findOwnedOrFail($id, $userId);
        $this->products->delete($product);
    }
}
