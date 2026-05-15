<?php

declare(strict_types=1);

namespace KraenzleRitter\AiCataloging\Agent;

/**
 * Outcome of an agent call.
 *
 * `$rawText` is the unparsed text the SDK returned; `$structured`
 * is the result of running it through the `OutputMapper`
 * (anton-import-format `RecordEntry`-shape after JSON-decode +
 * schema validation). When `$structured` is `null`, the consumer
 * should look at `$validationErrors` to render the diff to the
 * operator.
 *
 * Tokens, provider+model, response-id are filled by the runner so
 * the consumer can build a `UsageRecord` and write its log row.
 */
final readonly class AgentResult
{
    /**
     * @param  array<mixed>|null              $structured       parsed anton-import-format RecordEntry payload
     * @param  list<array<string,string>>     $validationErrors when validation failed
     */
    public function __construct(
        public string $rawText,
        public ?array $structured,
        public array $validationErrors,
        public string $provider,
        public string $model,
        public int $tokensIn,
        public int $tokensOut,
        public ?string $responseId,
    ) {}

    public function ok(): bool
    {
        return $this->structured !== null && $this->validationErrors === [];
    }
}
