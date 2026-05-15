<?php

declare(strict_types=1);

namespace KraenzleRitter\AiCataloging\Telemetry;

/**
 * Pure pricing calculator — no DB, no time of day, just arithmetic.
 *
 * Consumer side loads the matching pricing row (largest
 * `validFrom <= $at` for the given provider+model) and hands it to
 * this calculator. Keeps the math out of the consumer's persistence
 * layer; same calculator usable from a CLI usage-rebuild script.
 */
final class PricingCalculator
{
    /**
     * Provider-side cost in CHF for one agent call.
     */
    public static function providerCost(
        Pricing $pricing,
        int $tokensIn,
        int $tokensOut,
    ): float {
        if ($tokensIn < 0 || $tokensOut < 0) {
            throw new \InvalidArgumentException('Token counts must be non-negative.');
        }

        $inputCost = ($tokensIn / 1000.0) * $pricing->inputPer1kChf;
        $outputCost = ($tokensOut / 1000.0) * $pricing->outputPer1kChf;

        return $inputCost + $outputCost;
    }

    /**
     * Billable cost = provider cost × tenant margin (default 1.30).
     *
     * Margin lives per-tenant in consumer config; the calculator
     * just multiplies. Negative or zero margin would silently zero
     * out billing — guard against accidental misconfiguration.
     */
    public static function billableCost(float $providerCost, float $margin): float
    {
        if ($margin <= 0) {
            throw new \InvalidArgumentException('Margin must be > 0.');
        }
        if ($providerCost < 0) {
            throw new \InvalidArgumentException('providerCost must be >= 0.');
        }

        return $providerCost * $margin;
    }
}
