<?php

namespace App\Contracts\Integrations;

use App\DataTransferObjects\GeneratedContent;
use App\Exceptions\IntegrationException;

interface ContentGeneratorInterface
{
    /**
     * @throws IntegrationException
     */
    public function generate(string $prompt): GeneratedContent;
}
