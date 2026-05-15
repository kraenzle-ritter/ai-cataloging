<?php

declare(strict_types=1);

namespace KraenzleRitter\AiCataloging\Telemetry;

/**
 * Single row's worth of telemetry from an AI cataloging call.
 *
 * Returned from the Agent invocation; consumer writes its own
 * persistence row (Anton: `ai_usage_log`). The package never
 * persists — it just hands the shape over.
 *
 * `targetReference` is consumer-defined (Anton uses
 * `['type' => 'anton-object', 'id' => 42]`; agate would use
 * something like `['type' => 'sip', 'uuid' => '…']`). Package
 * never inspects it.
 *
 * `status` is one of:
 *   - `ok` — call completed, agent emitted a parsable response
 *   - `validation_failed` — agent response didn't validate against
 *     the schema; consumer may show diff to operator
 *   - `provider_error` — upstream returned non-2xx after all
 *     fallbacks
 *   - `budget_block` — call refused before dispatch by BudgetGuard
 */
final readonly class UsageRecord
{
    public const STATUS_OK = 'ok';
    public const STATUS_VALIDATION_FAILED = 'validation_failed';
    public const STATUS_PROVIDER_ERROR = 'provider_error';
    public const STATUS_BUDGET_BLOCK = 'budget_block';

    /**
     * @param  array<string, mixed>  $targetReference
     */
    public function __construct(
        public \DateTimeImmutable $at,
        public string $profileSlug,
        public int $profileVersion,
        public string $provider,
        public string $model,
        public int $tokensIn,
        public int $tokensOut,
        public float $providerCostChf,
        public float $billableCostChf,
        public string $status,
        public ?string $responseId,
        public array $targetReference,
    ) {
        if (! in_array($status, [
            self::STATUS_OK,
            self::STATUS_VALIDATION_FAILED,
            self::STATUS_PROVIDER_ERROR,
            self::STATUS_BUDGET_BLOCK,
        ], true)) {
            throw new \InvalidArgumentException('Unknown UsageRecord status: '.$status);
        }
    }
}
