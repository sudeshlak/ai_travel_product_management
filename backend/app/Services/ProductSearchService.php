<?php

namespace App\Services;

use App\Contracts\Integrations\SearchQueryInterpreterInterface;
use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Repositories\DestinationRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\DataTransferObjects\ProductSearchCriteria;
use App\DataTransferObjects\ProductSearchFilters;
use App\Enums\ProductStatus;
use App\Exceptions\IntegrationException;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

/**
 * Runs a natural-language, catalog-wide product search: an AI-backed
 * interpreter turns free text into filters, and this service always
 * enforces the Active + currently-valid + in-stock business rules on
 * top of whatever the interpreter returns before querying via the ORM.
 */
class ProductSearchService
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly CategoryRepositoryInterface $categories,
        private readonly DestinationRepositoryInterface $destinations,
        private readonly SearchQueryInterpreterInterface $interpreter,
    ) {}

    /**
     * @return LengthAwarePaginator<int, Product>
     */
    public function search(string $query, int $page, int $perPage): LengthAwarePaginator
    {
        $filters = $this->resolveFilters($query);

        $category = $filters->categoryName !== null
            ? $this->categories->findByName($filters->categoryName)
            : null;

        $destination = $filters->destinationName !== null
            ? $this->destinations->findByName($filters->destinationName)
            : null;

        $criteria = new ProductSearchCriteria(
            keyword: $filters->keyword,
            categoryId: $category?->id,
            destinationIds: $destination !== null ? [$destination->id] : [],
            minPrice: $filters->minPrice,
            maxPrice: $filters->maxPrice,
            status: ProductStatus::Active,
            onlyValid: true,
            onlyInStock: true,
            userId: null,
            page: $page,
            perPage: $perPage,
        );

        return $this->products->paginate($criteria);
    }

    private function resolveFilters(string $query): ProductSearchFilters
    {
        $trimmed = trim($query);

        if ($trimmed === '') {
            return new ProductSearchFilters;
        }

        try {
            $filters = $this->interpreter->interpret($trimmed);
            Log::info('AI search filters', [
                'query' => $trimmed,
                'filters' => [
                    'keyword' => $filters->keyword,
                    'categoryName' => $filters->categoryName,
                    'destinationName' => $filters->destinationName,
                    'minPrice' => $filters->minPrice,
                    'maxPrice' => $filters->maxPrice,
                ],
            ]);
            return $filters;
        } catch (IntegrationException $e) {
            Log::error('AI search failed', [
                'query' => $trimmed,
                'message' => $e->getMessage(),
                'previous' => $e->getPrevious()?->getMessage(),
            ]);
            return new ProductSearchFilters(keyword: $trimmed);
        }
    }
}
