<?php

declare(strict_types=1);

namespace KraenzleRitter\AiCataloging\Profile;

/**
 * Resolved AI-cataloging profile after inheritance flattening.
 *
 * Consumer side (Anton: `ai_profiles` table; agate: TBD) walks the
 * inheritance chain (subtree → objectform → tenant → global) and
 * fills every null field from its parent. The result is this
 * immutable DTO — every value here is final, no further lookups
 * required for the agent build.
 *
 * Null is not allowed once a profile is "effective" — that's why
 * the constructor parameters are typed without `?` for the values
 * that flow into agent invocation. The exception is
 * `language_hint`, which is genuinely optional (auto-detect path).
 */
final readonly class EffectiveProfile
{
    /**
     * @param  list<string>                        $providers     primary first, fallbacks after
     * @param  array<string, FieldConstraint>      $fields        field name → constraint
     * @param  array<string, scalar|array<mixed>|null> $contextConfig free-form profile-side hints (e.g. video cap, frame interval)
     */
    public function __construct(
        public string $slug,
        public int $version,
        public array $providers,
        public string $model,
        public string $promptTemplate,
        public array $fields,
        public array $contextConfig,
        public ?string $languageHint,
        public VideoStrategy $videoStrategy,
        public int $videoCapSeconds,
        public int $frameIntervalSeconds,
        public bool $sceneDetectionEnabled,
        public bool $auditEnabled,
        public int $auditThreshold,
    ) {
        if ($providers === []) {
            throw new \InvalidArgumentException('EffectiveProfile requires at least one provider.');
        }
        if ($videoCapSeconds <= 0) {
            throw new \InvalidArgumentException('videoCapSeconds must be > 0.');
        }
    }

    public function primaryProvider(): string
    {
        return $this->providers[0];
    }

    /**
     * @return list<string>
     */
    public function fallbackProviders(): array
    {
        return array_slice($this->providers, 1);
    }
}
