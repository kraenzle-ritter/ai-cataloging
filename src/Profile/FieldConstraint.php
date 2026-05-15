<?php

declare(strict_types=1);

namespace KraenzleRitter\AiCataloging\Profile;

/**
 * Per-field constraint within a profile's `field_schema`.
 *
 * Carried into the runtime JSON schema the agent gets handed (so the
 * LLM stays inside the lane), plus consulted by the consumer side
 * when rendering the per-field part of the modal-stack.
 *
 * The constraint is profile-local — the import-format schema itself
 * is already permissive (additionalProperties: true). Profiles pick
 * a *subset* of importable fields, tighten requirement state, and
 * inject hints.
 */
final readonly class FieldConstraint
{
    /**
     * @param  array<string,scalar>|null  $hints  e.g. ['max_length' => 200, 'allow_html' => false]
     */
    public function __construct(
        public bool $required = false,
        public bool $aiCanSuggest = true,
        public ?array $hints = null,
    ) {}
}
