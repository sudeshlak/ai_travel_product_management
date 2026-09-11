<?php

namespace App\Http\Requests;

use App\Services\ProductGenerationService;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class GenerateProductRequest extends FormRequest
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
            'prompt' => [
                'required',
                'string',
                'max:'.ProductGenerationService::MAX_DESCRIPTION_LENGTH,
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! is_string($value)) {
                        return;
                    }

                    $trimmed = trim($value);
                    $words = $trimmed === '' ? [] : preg_split('/\s+/', $trimmed, -1, PREG_SPLIT_NO_EMPTY);

                    if ($words === false || count($words) < 4) {
                        $fail('The prompt must contain at least 4 words.');
                    }
                },
            ],
        ];
    }
}
