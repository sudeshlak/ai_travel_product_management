<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\DataTransferObjects\ProductData;
use App\DataTransferObjects\ProductSearchCriteria;
use App\DataTransferObjects\ProductSummary;
use App\Enums\ProductStatus;
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

        if ($criteria->minPrice !== null) {
            $query->where('price', '>=', $criteria->minPrice);
        }

        if ($criteria->maxPrice !== null) {
            $query->where('price', '<=', $criteria->maxPrice);
        }

        if ($criteria->onlyValid) {
            $query->valid();
        }

        if ($criteria->onlyInStock) {
            $query->where('inventory_count', '>', 0);
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

    public function create(ProductData $data, int $userId): Product
    {
        return DB::transaction(function () use ($data, $userId): Product {
            $product = Product::query()->create([
                'user_id' => $userId,
                'product_name' => $data->productName,
                'category_id' => $data->categoryId,
                'description' => $data->description,
                'price' => $data->price,
                'inventory_count' => $data->inventoryCount,
                'valid_from' => $data->validFrom,
                'valid_until' => $data->validUntil,
                'status' => $data->status,
            ]);

            $product->destinations()->sync($data->destinationIds);

            return $product->refresh();
        });
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

    public function summarizeForUser(int $userId): ProductSummary
    {
        $today = now()->toDateString();
        $active = ProductStatus::Active->value;

        $row = Product::query()
            ->where('user_id', $userId)
            ->selectRaw('COUNT(*) as total_products')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as active_products', [$active])
            ->selectRaw('SUM(CASE WHEN DATE(valid_until) < ? THEN 1 ELSE 0 END) as expired_products', [$today])
            ->first();

        return new ProductSummary(
            totalProducts: (int) ($row?->total_products ?? 0),
            activeProducts: (int) ($row?->active_products ?? 0),
            expiredProducts: (int) ($row?->expired_products ?? 0),
        );
    }
}
