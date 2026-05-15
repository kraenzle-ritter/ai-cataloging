<?php

declare(strict_types=1);

namespace KraenzleRitter\AiCataloging\Profile;

/**
 * Consumer contract for resolving the effective profile for a target.
 *
 * `$context` is intentionally typed as `array<string, mixed>` so each
 * consumer can pass its own discriminator (Anton: AntonObject id,
 * objectform id; agate: SIP id, target collection). The package
 * never inspects the array — it only carries it back to the consumer
 * via the loader call.
 *
 * Anton's implementation walks subtree → objectform → tenant → global
 * profile rows from `ai_profiles`, fills every null field from the
 * parent. agate's implementation will follow the same pattern against
 * agate's own profile store (TBD).
 */
interface ProfileLoader
{
    /**
     * @param  array<string, mixed>  $context
     *
     * @throws ProfileNotFound  when no profile resolves for the context
     */
    public function loadEffectiveProfile(array $context): EffectiveProfile;
}
