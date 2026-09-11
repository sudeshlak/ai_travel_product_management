<?php

namespace App\Http\Resources;

use App\DataTransferObjects\GeneratedProductData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read GeneratedProductData $resource
 */
class GeneratedProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'product_name' => $this->resource->productName,
            'description' => $this->resource->description,
            'category_id' => $this->resource->categoryId,
        ];
    }
}
