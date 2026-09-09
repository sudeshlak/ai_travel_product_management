<?php

namespace App\Contracts\Integrations;

use App\DataTransferObjects\ProductSearchFilters;
use App\Exceptions\IntegrationException;

interface SearchQueryInterpreterInterface
{
    /**
     * @throws IntegrationException
     */
    public function interpret(string $query): ProductSearchFilters;
}
