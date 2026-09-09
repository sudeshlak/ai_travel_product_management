<?php

namespace App\DataTransferObjects;

readonly class ProductSearchFilters
{
    public function __construct(
        public ?string $keyword = null,
        public ?string $categoryName = null,
        public ?string $destinationName = null,
        public ?float $minPrice = null,
        public ?float $maxPrice = null,
    ) {}
}
