<?php

namespace App\DataTransferObjects;

readonly class ProductGenerateInput
{
    /**
     * @param  list<array{id: int, name: string}>  $categories
     */
    public function __construct(
        public string $prompt,
        public array $categories,
    ) {}
}
