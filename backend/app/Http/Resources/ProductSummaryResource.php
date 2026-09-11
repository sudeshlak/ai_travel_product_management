<?php

namespace App\Http\Resources;

use App\DataTransferObjects\ProductSummary;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ProductSummary
 */
class ProductSummaryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var ProductSummary $summary */
        $summary = $this->resource;

        return [
            'total_products' => $summary->totalProducts,
            'active_products' => $summary->activeProducts,
            'expired_products' => $summary->expiredProducts,
        ];
    }
}
