<?php

namespace App\Http\Requests;

use App\Enums\ProductStatus;
use Illuminate\Validation\Rule;

trait ValidatesProductWrite
{
    /**
     * @return array<string, mixed>
     */
    protected function productWriteRules(): array
    {
        return [
            'product_name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'inventory_count' => ['required', 'integer', 'min:0'],
            'valid_from' => ['required', 'date'],
            'valid_until' => ['required', 'date', 'after_or_equal:valid_from'],
            'status' => ['required', Rule::enum(ProductStatus::class)],
            'destination_ids' => ['required', 'array', 'min:1'],
            'destination_ids.*' => ['integer', 'exists:destinations,id'],
        ];
    }
}
