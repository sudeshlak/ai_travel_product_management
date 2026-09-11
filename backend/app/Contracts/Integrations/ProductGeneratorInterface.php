<?php

namespace App\Contracts\Integrations;

use App\DataTransferObjects\GeneratedProductData;
use App\DataTransferObjects\ProductGenerateInput;
use App\Exceptions\IntegrationException;

interface ProductGeneratorInterface
{
    /**
     * @throws IntegrationException
     */
    public function generate(ProductGenerateInput $input): GeneratedProductData;
}
