<?php

declare(strict_types=1);

namespace KraenzleRitter\AiCataloging\Output;

use KraenzleRitter\AntonImportFormat\Validator;

/**
 * Converts the raw agent text response into a validated
 * anton-import-format RecordEntry payload.
 *
 * The agent is instructed to emit JSON strict against the profile-
 * derived schema. We:
 *
 *   1. Strip the obvious code-fence wrapper LLMs sometimes add
 *      (```json … ``` or just ``` … ```).
 *   2. JSON-decode.
 *   3. Wrap the single entry into a temporary v0.2 wrapper so the
 *      existing `kraenzle-ritter/anton-import-format` validator can
 *      check it — that's the contract Anton's import paths already
 *      enforce, so producing the same shape keeps the AI output
 *      drop-in compatible with the existing AuthorityResolver +
 *      NewImporter pipeline.
 *
 * Returns `null` for `entry` if parsing or validation failed; the
 * `errors` list carries the details so the consumer can render
 * them in the modal-stack instead of silently dropping the call.
 *
 * @phpstan-type ValidationError array{path: string, keyword: string, message: string}
 */
final class OutputMapper
{
    public function __construct(
        private readonly Validator $validator = new Validator(),
        private readonly string $tenantSlug = '',
        private readonly string $generator = 'ai-cataloging',
    ) {}

    /**
     * @return array{entry: array<mixed>|null, errors: list<ValidationError>}
     */
    public function toRecordEntry(string $rawText): array
    {
        $cleaned = $this->stripCodeFence(trim($rawText));

        try {
            $decoded = json_decode($cleaned, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return [
                'entry' => null,
                'errors' => [[
                    'path' => '/',
                    'keyword' => 'json',
                    'message' => 'Agent response is not valid JSON: '.$e->getMessage(),
                ]],
            ];
        }

        if (! is_array($decoded)) {
            return [
                'entry' => null,
                'errors' => [[
                    'path' => '/',
                    'keyword' => 'type',
                    'message' => 'Agent response is not a JSON object.',
                ]],
            ];
        }

        // The schema requires `type` and `uuid` per entry, but LLMs
        // often produce free-form JSON without those wire-format
        // hints. Backfill the deterministic-enough defaults so a
        // perfectly good content payload doesn't get rejected over
        // missing envelope fields. Operator validates the content,
        // not the protocol scaffolding.
        if (! isset($decoded['type'])) {
            $decoded['type'] = 'record';
        }
        if (! isset($decoded['uuid'])) {
            // Use a v4-style uuid generated from random bytes; we
            // depend only on PHP core so the package stays Laravel-free.
            $bytes = random_bytes(16);
            $bytes[6] = chr((ord($bytes[6]) & 0x0F) | 0x40);
            $bytes[8] = chr((ord($bytes[8]) & 0x3F) | 0x80);
            $hex = bin2hex($bytes);
            $decoded['uuid'] = sprintf('%s-%s-%s-%s-%s',
                substr($hex, 0, 8),
                substr($hex, 8, 4),
                substr($hex, 12, 4),
                substr($hex, 16, 4),
                substr($hex, 20, 12),
            );
        }

        // Wrap as v0.2 single-entry document so the existing validator
        // can check it. The wrapper fields are package-mandated; the
        // entry itself is what the consumer will hand to the resolver
        // and NewImporter.
        $wrapper = [
            'version' => '0.2',
            'tenant' => $this->tenantSlug,
            'generator' => $this->generator,
            'entries' => [$decoded],
        ];

        $result = $this->validator->validate($wrapper);
        if (! $result->valid) {
            return [
                'entry' => null,
                'errors' => array_map(
                    static fn ($e): array => $e->toArray(),
                    $result->errors,
                ),
            ];
        }

        return ['entry' => $decoded, 'errors' => []];
    }

    private function stripCodeFence(string $text): string
    {
        if (! str_starts_with($text, '```')) {
            return $text;
        }

        // Drop the leading fence (```json or just ```), keep the body,
        // drop the trailing fence. Anything outside the fences (LLM
        // chitchat) is discarded.
        $stripped = preg_replace('/^```[a-zA-Z]*\n?/', '', $text);
        $stripped = preg_replace('/\n?```\s*$/', '', (string) $stripped);

        return (string) $stripped;
    }
}
