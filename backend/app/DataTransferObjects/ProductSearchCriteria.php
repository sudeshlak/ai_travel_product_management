<?php

namespace App\DataTransferObjects;

use App\Enums\ProductStatus;

readonly class ProductSearchCriteria
{
    /**
     * @param  list<int>  $destinationIds
     */
    public function __construct(
        public ?string $keyword = null,
        public ?int $categoryId = null,
        public array $destinationIds = [],
        public ?float $maxPrice = null,
        public ?ProductStatus $status = null,
        public bool $onlyValid = true,
        public ?int $userId = null,
        public int $page = 1,
        public int $perPage = 15,
    ) {}
}
