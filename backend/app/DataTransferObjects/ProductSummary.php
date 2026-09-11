<?php

namespace App\DataTransferObjects;

readonly class ProductSummary
{
    public function __construct(
        public int $totalProducts,
        public int $activeProducts,
        public int $expiredProducts,
    ) {}
}
