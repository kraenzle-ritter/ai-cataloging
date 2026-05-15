<?php

declare(strict_types=1);

namespace KraenzleRitter\AiCataloging\Resolver;

/**
 * Consumer contract for resolving AI-suggested authority entries
 * (Actor / Place / Keyword) against an existing authority store.
 *
 * The package emits inline-form suggestions inside the AI response;
 * the consumer's bridge tells the modal-stack whether each suggestion
 * matches an existing record (and which) or would create a new one.
 *
 * Anton's implementation wraps the existing `AuthorityResolver`
 * service with `dry_run = true`. agate, if it ever needs authority
 * resolution (currently it doesn't — agate has no authority store),
 * can provide a stub bridge that always reports "would create".
 *
 * `$inlineForm` follows the anton-import-format inline-form shape
 * — at minimum a multilingual `label`, optionally `type`,
 * `alternative_names`, `match_by`, `on_not_found`. The bridge MUST
 * NOT mutate the consumer's database; mutation happens after the
 * operator confirms in the modal-stack.
 */
interface ResolverBridge
{
    /**
     * @param  array<string, mixed>  $inlineForm  full inline-form payload
     */
    public function dryRunActor(array $inlineForm): ResolverResult;

    /**
     * @param  array<string, mixed>  $inlineForm
     */
    public function dryRunPlace(array $inlineForm): ResolverResult;

    /**
     * @param  array<string, mixed>  $inlineForm
     */
    public function dryRunKeyword(array $inlineForm): ResolverResult;
}
