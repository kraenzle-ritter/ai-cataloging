# kraenzle-ritter/ai-cataloging

[![License](https://img.shields.io/github/license/kraenzle-ritter/ai-cataloging)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue)](https://www.php.net/)

Provider-agnostic core for AI-assisted archival cataloging.

Consumed by [Anton](https://github.com/kraenzle-ritter/anton) (archive
database) and [agate](https://github.com/kraenzle-ritter/agate)
(SIP-Ingest pipeline). Designed to keep all provider-specific and
framework-specific bits out — the consumer wires its own profile
loader, authority resolver, persistence and UI on top of the
interfaces this package exposes.

Output shape is always a valid
[`kraenzle-ritter/anton-import-format`](https://github.com/kraenzle-ritter/anton-import-format)
v0.2+ `RecordEntry`, so the same AI-generated metadata can flow
through any of the existing import paths (Excel, SIP, archive-upload).

## Scope

This package owns:

- Profile DTO (`EffectiveProfile`) and the `ProfileLoader` contract
- JSON-schema builder that narrows the import-format `RecordEntry`
  shape per profile field selection
- Agent builder around the chosen `laravel/ai` driver
- Provider router (primary + fallbacks) and modality strategies
  (image / PDF / video direct / video frames + STT / audio)
- Telemetry-pure calculators (pricing, margin, budget guard)
- Output mapper from raw AI JSON to the validated `RecordEntry`
- The `ResolverBridge` contract for authority lookups

It does *not* own:

- Eloquent models / database migrations — that's per-consumer.
- Provider API keys / config storage — passed in via the profile.
- UI, modals, Livewire components — consumer wiring.
- Authority resolver implementation — consumer wires its own via
  the `ResolverBridge` interface.

## Status

Pre-v0.1 — interfaces and DTOs landing first, implementations follow.
Track the broader picture in Anton's openspec change
`add-ai-assisted-description`.

## Requirements

- PHP ≥ 8.2
- `kraenzle-ritter/anton-import-format` ^0.2.1 (for the
  `RecordEntry` / `Note.tracks` shape)

## Install

```bash
composer require kraenzle-ritter/ai-cataloging
```

Run tests + static analysis:

```bash
composer test
composer phpstan
```

## License

MIT.
