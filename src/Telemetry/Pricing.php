<?php

declare(strict_types=1);

namespace KraenzleRitter\AiCataloging\Telemetry;

/**
 * Single pricing record for a given provider + model at a given point
 * in time. Pricing changes are versioned by `validFrom` so consumers
 * can keep historical accuracy in their usage log.
 *
 * Prices are CHF per 1000 tokens to keep the storage decimal small
 * and the unit aligned with how providers quote (cents per 1k).
 */
final readonly class Pricing
{
    public function __construct(
        public string $provider,
        public string $model,
        public float $inputPer1kChf,
        public float $outputPer1kChf,
        public \DateTimeImmutable $validFrom,
    ) {
        if ($inputPer1kChf < 0 || $outputPer1kChf < 0) {
            throw new \InvalidArgumentException('Token prices must be non-negative.');
        }
    }
}
