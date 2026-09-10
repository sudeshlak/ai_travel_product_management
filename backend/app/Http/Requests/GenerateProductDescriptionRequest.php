<?php

namespace App\Http\Requests;

use App\Services\ProductDescriptionService;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class GenerateProductDescriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'description' => [
                'required',
                'string',
                'max:'.ProductDescriptionService::MAX_DESCRIPTION_LENGTH,
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! is_string($value)) {
                        return;
                    }

                    $trimmed = trim($value);
                    $words = $trimmed === '' ? [] : preg_split('/\s+/', $trimmed, -1, PREG_SPLIT_NO_EMPTY);

                    if ($words === false || count($words) < 4) {
                        $fail('The description must contain at least 4 words.');
                    }
                },
            ],
            'product_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'category' => ['sometimes', 'nullable', 'string', 'max:100'],
        ];
    }
}
