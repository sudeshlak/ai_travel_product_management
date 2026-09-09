<?php

namespace App\Infrastructure\OpenAi;

use App\Contracts\Integrations\SearchQueryInterpreterInterface;
use App\DataTransferObjects\ProductSearchFilters;
use App\Exceptions\IntegrationException;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Turns a free-text product search query into structured filters using
 * the OpenAI Chat Completions API. This class only ever produces a
 * ProductSearchFilters DTO; it has no concept of status/validity, so it
 * structurally cannot influence those business rules.
 */
class OpenAiSearchQueryInterpreter implements SearchQueryInterpreterInterface
{
    private const SYSTEM_PROMPT = <<<'PROMPT'
        You extract structured search filters from a traveler's natural-language
        product search query for a travel product catalog. Respond with a single
        JSON object and nothing else, using exactly these keys:

        {
          "keyword": string|null,      // short free-text term matching product name/description, or null
          "category": string|null,     // a category name mentioned or implied, or null
          "destination": string|null,  // a destination/location name mentioned, or null
          "min_price": number|null,    // minimum price if a lower bound is implied, or null
          "max_price": number|null     // maximum price if an upper bound is implied, or null
        }

        Only include values you are confident about; use null for anything not
        clearly present in the query. Do not invent category or destination names
        that are not implied by the text. Never include any field other than the
        five listed above.
        PROMPT;

    public function interpret(string $query): ProductSearchFilters
    {
        try {
            $response = Http::baseUrl('https://api.openai.com/v1')
                ->withToken((string) config('services.openai.api_key'))
                ->timeout(10)
                ->post('chat/completions', [
                    'model' => config('services.openai.model'),
                    'temperature' => 0,
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        ['role' => 'system', 'content' => self::SYSTEM_PROMPT],
                        ['role' => 'user', 'content' => $query],
                    ],
                ]);
        } catch (Throwable $exception) {
            throw new IntegrationException('OpenAI search interpretation request failed.', previous: $exception);
        }

        if ($response->failed()) {
            throw new IntegrationException('OpenAI search interpretation request failed.');
        }

        $content = $response->json('choices.0.message.content');

        if (! is_string($content) || $content === '') {
            throw new IntegrationException('OpenAI returned an empty search interpretation response.');
        }

        $decoded = json_decode($content, true);

        if (! is_array($decoded)) {
            throw new IntegrationException('OpenAI returned an unparsable search interpretation response.');
        }

        return new ProductSearchFilters(
            keyword: $this->nullableString($decoded['keyword'] ?? null),
            categoryName: $this->nullableString($decoded['category'] ?? null),
            destinationName: $this->nullableString($decoded['destination'] ?? null),
            minPrice: $this->nullableFloat($decoded['min_price'] ?? null),
            maxPrice: $this->nullableFloat($decoded['max_price'] ?? null),
        );
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function nullableFloat(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }
}
