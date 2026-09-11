<?php

namespace App\Infrastructure\OpenAi;

use App\Contracts\Integrations\ProductGeneratorInterface;
use App\DataTransferObjects\GeneratedProductData;
use App\DataTransferObjects\ProductGenerateInput;
use App\Exceptions\IntegrationException;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Turns a natural-language product prompt into a name, description, and
 * category using the OpenAI Chat Completions API.
 */
class OpenAiProductGenerator implements ProductGeneratorInterface
{
    private const SYSTEM_PROMPT = <<<'PROMPT'
        You create travel product catalog copy from a merchant's natural-language
        prompt. Respond with a single JSON object and nothing else:

        {
          "product_name": string,
          "description": string,
          "category_id": number
        }

        Rules:
        - product_name is a concise catalog title derived from the prompt.
        - description is a single string of professional travel-product copy.
          Embed Highlights, Inclusions, and Tags as labeled sections inside
          that description. Do not return those as separate JSON keys.
        - category_id must be one of the category ids provided in the user
          message. Never invent an id.
        - Use only facts implied by the prompt. Do not invent prices,
          amenities, destinations, or claims that are not implied.
        - Do not generate destinations, dates, price, inventory, or status.
        PROMPT;

    public function generate(ProductGenerateInput $input): GeneratedProductData
    {
        $userPayload = [
            'prompt' => $input->prompt,
            'categories' => $input->categories,
        ];

        try {
            $response = Http::baseUrl('https://api.openai.com/v1')
                ->withToken((string) config('services.openai.api_key'))
                ->timeout(15)
                ->post('chat/completions', [
                    'model' => config('services.openai.model'),
                    'temperature' => 0.4,
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        ['role' => 'system', 'content' => self::SYSTEM_PROMPT],
                        ['role' => 'user', 'content' => json_encode($userPayload, JSON_THROW_ON_ERROR)],
                    ],
                ]);
        } catch (Throwable $exception) {
            throw new IntegrationException('OpenAI product generation request failed.', previous: $exception);
        }

        if ($response->failed()) {
            throw new IntegrationException('OpenAI product generation request failed.');
        }

        $content = $response->json('choices.0.message.content');

        if (! is_string($content) || $content === '') {
            throw new IntegrationException('OpenAI returned an empty product generation response.');
        }

        $decoded = json_decode($content, true);

        if (! is_array($decoded)) {
            throw new IntegrationException('OpenAI returned an unparsable product generation response.');
        }

        $productName = $this->requiredString($decoded['product_name'] ?? null, 'product_name');
        $description = $this->requiredString($decoded['description'] ?? null, 'description');
        $categoryId = $this->requiredInt($decoded['category_id'] ?? null);

        return new GeneratedProductData(
            productName: $productName,
            description: $description,
            categoryId: $categoryId,
        );
    }

    private function requiredString(mixed $value, string $field): string
    {
        if (! is_string($value)) {
            throw new IntegrationException("OpenAI returned a product generation response without a {$field}.");
        }

        $trimmed = trim($value);

        if ($trimmed === '') {
            throw new IntegrationException("OpenAI returned an empty {$field}.");
        }

        return $trimmed;
    }

    private function requiredInt(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && is_numeric($value) && (string) (int) $value === $value) {
            return (int) $value;
        }

        throw new IntegrationException('OpenAI returned a product generation response without a category_id.');
    }
}
