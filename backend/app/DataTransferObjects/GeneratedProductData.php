<?php

namespace App\DataTransferObjects;

readonly class GeneratedProductData
{
    public function __construct(
        public string $productName,
        public string $description,
        public int $categoryId,
    ) {}
}
