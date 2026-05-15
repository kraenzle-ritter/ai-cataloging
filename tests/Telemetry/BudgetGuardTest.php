<?php

declare(strict_types=1);

namespace KraenzleRitter\AiCataloging\Tests\Telemetry;

use KraenzleRitter\AiCataloging\Telemetry\BudgetGuard;
use KraenzleRitter\AiCataloging\Telemetry\BudgetStatus;
use PHPUnit\Framework\TestCase;

final class BudgetGuardTest extends TestCase
{
    public function test_ok_when_projected_below_soft_threshold(): void
    {
        $guard = new BudgetGuard();
        // Default soft = 0.80; budget 100, spent 50, call 20 → projected 70 (0.70 ratio)
        $this->assertSame(BudgetStatus::Ok, $guard->check(100.0, 50.0, 20.0));
    }

    public function test_soft_warn_at_default_eighty_percent(): void
    {
        $guard = new BudgetGuard();
        // Budget 100, spent 75, call 5 → projected 80 (0.80 ratio, exactly at threshold)
        $this->assertSame(BudgetStatus::SoftWarn, $guard->check(100.0, 75.0, 5.0));
    }

    public function test_hard_block_at_exactly_hundred_percent(): void
    {
        $guard = new BudgetGuard();
        // Budget 100, spent 90, call 10 → projected 100 (1.0 ratio)
        $this->assertSame(BudgetStatus::HardBlock, $guard->check(100.0, 90.0, 10.0));
    }

    public function test_hard_block_when_already_over(): void
    {
        $guard = new BudgetGuard();
        // Budget 100, spent 105, call 1 → projected 106 → over
        $this->assertSame(BudgetStatus::HardBlock, $guard->check(100.0, 105.0, 1.0));
    }

    public function test_custom_soft_threshold(): void
    {
        $guard = new BudgetGuard(softWarnThreshold: 0.50);
        $this->assertSame(BudgetStatus::SoftWarn, $guard->check(100.0, 50.0, 0.0));
        $this->assertSame(BudgetStatus::Ok, $guard->check(100.0, 49.0, 0.0));
    }

    public function test_soft_threshold_out_of_range_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new BudgetGuard(softWarnThreshold: 1.5);
    }

    public function test_zero_budget_rejected_call_should_not_reach_guard(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new BudgetGuard())->check(0.0, 0.0, 5.0);
    }

    public function test_negative_spend_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new BudgetGuard())->check(100.0, -1.0, 0.0);
    }
}
