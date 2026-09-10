<?php

namespace App\Contracts\Integrations;

use App\DataTransferObjects\ProductDescriptionPolishInput;
use App\Exceptions\IntegrationException;

interface ProductDescriptionPolisherInterface
{
    /**
     * @throws IntegrationException
     */
    public function polish(ProductDescriptionPolishInput $input): string;
}
