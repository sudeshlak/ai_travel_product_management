<?php

namespace App\Services;

use App\Contracts\Integrations\ProductGeneratorInterface;
use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\DataTransferObjects\GeneratedProductData;
use App\DataTransferObjects\ProductGenerateInput;
use App\Exceptions\IntegrationException;
use App\Models\Category;

class ProductGenerationService
{
    public const MAX_DESCRIPTION_LENGTH = 2000;

    public const MAX_PRODUCT_NAME_LENGTH = 255;

    public function __construct(
        private readonly CategoryRepositoryInterface $categories,
        private readonly ProductGeneratorInterface $generator,
    ) {}

    /**
     * @throws IntegrationException
     */
    public function generate(string $prompt): GeneratedProductData
    {
        $categories = $this->categories->allOrderedByName();

        if ($categories->isEmpty()) {
            throw new IntegrationException('No categories available for product generation.');
        }

        $allowedIds = $categories
            ->map(static fn (Category $category): int => (int) $category->id)
            ->all();

        $input = new ProductGenerateInput(
            prompt: $prompt,
            categories: $categories
                ->map(static fn (Category $category): array => [
                    'id' => (int) $category->id,
                    'name' => $category->name,
                ])
                ->values()
                ->all(),
        );

        $generated = $this->generator->generate($input);

        if (! in_array($generated->categoryId, $allowedIds, true)) {
            throw new IntegrationException('Generated category is not in the catalog.');
        }

        $description = $generated->description;
        if (mb_strlen($description) > self::MAX_DESCRIPTION_LENGTH) {
            $description = mb_substr($description, 0, self::MAX_DESCRIPTION_LENGTH);
        }

        $productName = $generated->productName;
        if (mb_strlen($productName) > self::MAX_PRODUCT_NAME_LENGTH) {
            $productName = mb_substr($productName, 0, self::MAX_PRODUCT_NAME_LENGTH);
        }

        return new GeneratedProductData(
            productName: $productName,
            description: $description,
            categoryId: $generated->categoryId,
        );
    }
}
