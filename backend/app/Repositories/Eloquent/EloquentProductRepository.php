<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\DataTransferObjects\ProductData;
use App\DataTransferObjects\ProductSearchCriteria;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EloquentProductRepository implements ProductRepositoryInterface
{
    /**
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginate(ProductSearchCriteria $criteria): LengthAwarePaginator
    {
        $query = Product::query()
            ->with(['category', 'destinations']);

        if ($criteria->userId !== null) {
            $query->where('user_id', $criteria->userId);
        }

        if ($criteria->status !== null) {
            $query->where('status', $criteria->status);
        }

        if ($criteria->keyword !== null && $criteria->keyword !== '') {
            $query->where(function ($builder) use ($criteria) {
                $builder
                    ->where('product_name', 'like', '%'.$criteria->keyword.'%')
                    ->orWhere('description', 'like', '%'.$criteria->keyword.'%');
            });
        }

        if ($criteria->categoryId !== null) {
            $query->where('category_id', $criteria->categoryId);
        }

        if ($criteria->destinationIds !== []) {
            $query->whereHas('destinations', function ($builder) use ($criteria) {
                $builder->whereIn('destinations.id', $criteria->destinationIds);
            });
        }

        if ($criteria->maxPrice !== null) {
            $query->where('price', '<=', $criteria->maxPrice);
        }

        if ($criteria->onlyValid) {
            $query->valid();
        }

        return $query
            ->orderByDesc('id')
            ->paginate(
                perPage: $criteria->perPage,
                page: $criteria->page,
            );
    }

    public function findOrFail(int $id): Product
    {
        return Product::query()->findOrFail($id);
    }

    public function findOwnedOrFail(int $id, int $userId): Product
    {
        return Product::query()
            ->where('user_id', $userId)
            ->findOrFail($id);
    }

    public function create(ProductData $data): Product
    {
        // TODO: implement
        throw new \BadMethodCallException('Not implemented');
    }

    public function update(Product $product, ProductData $data): Product
    {
        return DB::transaction(function () use ($product, $data): Product {
            $product->fill([
                'product_name' => $data->productName,
                'category_id' => $data->categoryId,
                'description' => $data->description,
                'price' => $data->price,
                'inventory_count' => $data->inventoryCount,
                'valid_from' => $data->validFrom,
                'valid_until' => $data->validUntil,
                'status' => $data->status,
            ]);
            $product->save();

            $product->destinations()->sync($data->destinationIds);

            return $product->refresh();
        });
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }
}
