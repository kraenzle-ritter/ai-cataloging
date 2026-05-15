<?php

declare(strict_types=1);

namespace KraenzleRitter\AiCataloging\Telemetry;

/**
 * Budget-guard verdict.
 *
 *  - `Ok`: spend below the soft-warning threshold (default < 80 %).
 *  - `SoftWarn`: spend ≥ 80 % of the monthly budget. UI shows a
 *    yellow banner but the call still proceeds.
 *  - `HardBlock`: spend ≥ 100 %. Consumer MUST refuse the call and
 *    show the operator the budget editor.
 */
enum BudgetStatus: string
{
    case Ok = 'ok';
    case SoftWarn = 'soft_warn';
    case HardBlock = 'hard_block';
}
