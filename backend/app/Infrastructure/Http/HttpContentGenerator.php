<?php

namespace App\Infrastructure\Http;

use App\Contracts\Integrations\ContentGeneratorInterface;
use App\DataTransferObjects\GeneratedContent;

/**
 * Adapts an external content-generation provider to the application's
 * own ContentGeneratorInterface contract.
 */
class HttpContentGenerator implements ContentGeneratorInterface
{
    public function generate(string $prompt): GeneratedContent
    {
        // TODO: implement
    }
}
