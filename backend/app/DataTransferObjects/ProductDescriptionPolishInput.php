<?php

namespace App\DataTransferObjects;

readonly class ProductDescriptionPolishInput
{
    public function __construct(
        public string $description,
        public ?string $productName = null,
        public ?string $category = null,
    ) {}
}
