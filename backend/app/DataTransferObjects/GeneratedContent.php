<?php

namespace App\DataTransferObjects;

readonly class GeneratedContent
{
    /**
     * @param  list<string>  $highlights
     * @param  list<string>  $tags
     */
    public function __construct(
        public string $title,
        public string $description,
        public array $highlights = [],
        public array $tags = [],
        public ?string $suggestedCategory = null,
    ) {}
}
