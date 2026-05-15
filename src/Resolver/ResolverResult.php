<?php

declare(strict_types=1);

namespace KraenzleRitter\AiCataloging\Resolver;

/**
 * Outcome of a dry-run authority lookup.
 *
 *  - `matched`: existing record found (consumer can show "use this").
 *  - `wouldCreate`: nothing matches (consumer asks operator to confirm
 *    creation, or to discard the suggestion).
 *  - `ambiguous`: multiple candidates (operator picks one in the modal).
 *
 * `$candidates` carries the consumer-side reference shape (Anton uses
 * `[['id' => int, 'label' => string]]`); the package treats them as
 * opaque arrays.
 */
final readonly class ResolverResult
{
    public const MATCHED = 'matched';
    public const WOULD_CREATE = 'would_create';
    public const AMBIGUOUS = 'ambiguous';

    /**
     * @param  list<array<string, mixed>>  $candidates
     */
    private function __construct(
        public string $outcome,
        public array $candidates,
    ) {}

    /**
     * @param  array<string, mixed>  $candidate
     */
    public static function matched(array $candidate): self
    {
        return new self(self::MATCHED, [$candidate]);
    }

    public static function wouldCreate(): self
    {
        return new self(self::WOULD_CREATE, []);
    }

    /**
     * @param  list<array<string, mixed>>  $candidates
     */
    public static function ambiguous(array $candidates): self
    {
        return new self(self::AMBIGUOUS, $candidates);
    }
}
