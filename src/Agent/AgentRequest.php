<?php

declare(strict_types=1);

namespace KraenzleRitter\AiCataloging\Agent;

use KraenzleRitter\AiCataloging\Profile\EffectiveProfile;

/**
 * Bundle of inputs for one cataloging agent call.
 *
 * Built by the consumer side from the effective profile + the
 * file(s) on the AntonObject (or agate-SIP). Passed to `AgentRunner`
 * which delegates the actual SDK call.
 *
 * `$attachments` is intentionally typed as `array<mixed>` because the
 * concrete type depends on the modality (`Laravel\Ai\Files\Image`,
 * `…\Document`, …) — the runner narrows per profile's video_strategy
 * and the file mime types.
 */
final readonly class AgentRequest
{
    /**
     * @param  array<int,mixed>     $attachments  Laravel\Ai\Files\* instances
     * @param  array<string,string> $targetReference  consumer-defined target id for telemetry
     */
    public function __construct(
        public EffectiveProfile $profile,
        public string $prompt,
        public array $attachments,
        public array $targetReference = [],
    ) {}
}
