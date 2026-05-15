<?php

declare(strict_types=1);

namespace KraenzleRitter\AiCataloging\Tests\Telemetry;

use KraenzleRitter\AiCataloging\Telemetry\Pricing;
use KraenzleRitter\AiCataloging\Telemetry\PricingCalculator;
use PHPUnit\Framework\TestCase;

final class PricingCalculatorTest extends TestCase
{
    private Pricing $gpt4o;

    protected function setUp(): void
    {
        $this->gpt4o = new Pricing(
            provider: 'openai',
            model: 'gpt-4o',
            inputPer1kChf: 0.0025,
            outputPer1kChf: 0.010,
            validFrom: new \DateTimeImmutable('2026-01-01'),
        );
    }

    public function test_provider_cost_sums_input_and_output(): void
    {
        // 10000 input tokens at 0.0025 / 1k = 0.025
        // 2000 output tokens at 0.010 / 1k = 0.020
        // Total = 0.045 CHF
        $this->assertEqualsWithDelta(
            0.045,
            PricingCalculator::providerCost($this->gpt4o, 10000, 2000),
            0.0001,
        );
    }

    public function test_zero_tokens_means_zero_cost(): void
    {
        $this->assertSame(0.0, PricingCalculator::providerCost($this->gpt4o, 0, 0));
    }

    public function test_negative_token_count_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        PricingCalculator::providerCost($this->gpt4o, -1, 100);
    }

    public function test_billable_cost_applies_margin(): void
    {
        $this->assertEqualsWithDelta(
            0.130,
            PricingCalculator::billableCost(0.10, 1.30),
            0.0001,
        );
    }

    public function test_billable_cost_rejects_zero_margin(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        PricingCalculator::billableCost(0.10, 0.0);
    }

    public function test_pricing_rejects_negative_price(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Pricing('openai', 'gpt-4o', -0.001, 0.010, new \DateTimeImmutable());
    }
}
