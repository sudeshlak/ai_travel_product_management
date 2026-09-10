<?php

namespace App\Infrastructure\OpenAi;

use App\Contracts\Integrations\ProductDescriptionPolisherInterface;
use App\DataTransferObjects\ProductDescriptionPolishInput;
use App\Exceptions\IntegrationException;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Rewrites a travel product description into clearer, grammatically correct
 * copy using the OpenAI Chat Completions API.
 */
class OpenAiProductDescriptionPolisher implements ProductDescriptionPolisherInterface
{
    private const SYSTEM_PROMPT = <<<'PROMPT'
        You rewrite travel product descriptions into clearer, more accurate,
        and grammatically correct marketing copy.

        Rules:
        - Preserve the original meaning and facts. Do not invent amenities,
          prices, destinations, inclusions, or claims that are not implied.
        - Improve grammar, spelling, clarity, and flow.
        - Keep a professional travel-product tone.
        - Optional product name and category are context only; do not force them
          into the copy if they do not fit naturally.
        - Respond with a single JSON object and nothing else:
          { "description": "..." }
        - The description value must be a non-empty string.
        PROMPT;

    public function polish(ProductDescriptionPolishInput $input): string
    {
        $userPayload = [
            'description' => $input->description,
            'product_name' => $input->productName,
            'category' => $input->category,
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
            throw new IntegrationException('OpenAI description polish request failed.', previous: $exception);
        }

        if ($response->failed()) {
            throw new IntegrationException('OpenAI description polish request failed.');
        }

        $content = $response->json('choices.0.message.content');

        if (! is_string($content) || $content === '') {
            throw new IntegrationException('OpenAI returned an empty description polish response.');
        }

        $decoded = json_decode($content, true);

        if (! is_array($decoded)) {
            throw new IntegrationException('OpenAI returned an unparsable description polish response.');
        }

        $description = $decoded['description'] ?? null;

        if (! is_string($description)) {
            throw new IntegrationException('OpenAI returned a description polish response without a description.');
        }

        $trimmed = trim($description);

        if ($trimmed === '') {
            throw new IntegrationException('OpenAI returned an empty polished description.');
        }

        return $trimmed;
    }
}
