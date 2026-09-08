<?php

namespace App\DataTransferObjects;

use App\Enums\ProductStatus;
use Illuminate\Support\Carbon;

readonly class ProductData
{
    /**
     * @param  list<int>  $destinationIds
     */
    public function __construct(
        public string $productName,
        public int $categoryId,
        public string $description,
        public float $price,
        public int $inventoryCount,
        public Carbon $validFrom,
        public Carbon $validUntil,
        public ProductStatus $status,
        public array $destinationIds = [],
    ) {}
}
