<?php

declare(strict_types=1);

namespace KraenzleRitter\AiCataloging\Telemetry;

/**
 * Pure budget-check — no DB.
 *
 * Consumer side sums the month-to-date billable cost from its
 * usage log (Anton: `ai_usage_log`), looks up the monthly budget
 * (`ai_budgets`), passes both numbers + the cost estimate for the
 * incoming call. Calculator returns the verdict.
 *
 * Threshold for SoftWarn is a constructor argument so consumers can
 * tune (default 80 % per the openspec design).
 */
final class BudgetGuard
{
    public function __construct(
        private readonly float $softWarnThreshold = 0.80,
    ) {
        if ($softWarnThreshold <= 0 || $softWarnThreshold >= 1) {
            throw new \InvalidArgumentException('softWarnThreshold must be in (0, 1).');
        }
    }

    public function check(
        float $monthBudgetChf,
        float $monthSpentChf,
        float $callEstimateChf,
    ): BudgetStatus {
        if ($monthBudgetChf <= 0) {
            throw new \InvalidArgumentException('monthBudgetChf must be > 0; an unset budget should not call BudgetGuard at all.');
        }
        if ($monthSpentChf < 0 || $callEstimateChf < 0) {
            throw new \InvalidArgumentException('monthSpentChf and callEstimateChf must be >= 0.');
        }

        $projected = $monthSpentChf + $callEstimateChf;
        $ratio = $projected / $monthBudgetChf;

        if ($ratio >= 1.0) {
            return BudgetStatus::HardBlock;
        }
        if ($ratio >= $this->softWarnThreshold) {
            return BudgetStatus::SoftWarn;
        }

        return BudgetStatus::Ok;
    }
}
