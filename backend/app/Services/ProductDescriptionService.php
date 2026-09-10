<?php

namespace App\Services;

use App\Contracts\Integrations\ProductDescriptionPolisherInterface;
use App\DataTransferObjects\ProductDescriptionPolishInput;
use App\Exceptions\IntegrationException;

class ProductDescriptionService
{
    public const MAX_DESCRIPTION_LENGTH = 2000;

    public function __construct(
        private readonly ProductDescriptionPolisherInterface $polisher,
    ) {}

    /**
     * @throws IntegrationException
     */
    public function generate(ProductDescriptionPolishInput $input): string
    {
        $description = $this->polisher->polish($input);

        if (mb_strlen($description) > self::MAX_DESCRIPTION_LENGTH) {
            return mb_substr($description, 0, self::MAX_DESCRIPTION_LENGTH);
        }

        return $description;
    }
}
