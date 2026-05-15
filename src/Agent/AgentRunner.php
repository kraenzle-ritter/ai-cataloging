<?php

declare(strict_types=1);

namespace KraenzleRitter\AiCataloging\Agent;

use KraenzleRitter\AiCataloging\Output\OutputMapper;
use Laravel\Ai\AnonymousAgent;
use Laravel\Ai\Lab;

/**
 * Drives a single agent call.
 *
 * Builds an `AnonymousAgent` from the profile (instructions =
 * prompt template), invokes `prompt()` with the provided attachments
 * and the per-call user prompt, then routes the raw text through
 * the `OutputMapper` for validation against anton-import-format.
 *
 * Provider failover is wired via the SDK: `prompt()` takes a
 * `Lab|array` second arg — we hand it the profile's
 * `providers` list which already has primary first and fallbacks
 * after.
 *
 * The runner itself does NOT persist usage. That's a consumer
 * concern — Anton listens on `Laravel\Ai\Events\AgentPrompted`
 * (Phase 2 task) and writes its own `ai_usage_log` row. Keeping
 * persistence out of the runner keeps the package framework-light
 * and the consumer in charge of its own logging schema.
 */
final class AgentRunner
{
    public function __construct(
        private readonly OutputMapper $mapper,
    ) {}

    public function run(AgentRequest $request): AgentResult
    {
        $agent = new AnonymousAgent(
            instructions: $request->profile->promptTemplate,
            messages: [],
            tools: [],
        );

        $providerArg = $this->buildProviderArg($request);

        $response = $agent->prompt(
            prompt: $request->prompt,
            attachments: $request->attachments,
            provider: $providerArg,
            model: $request->profile->model,
        );

        $rawText = $response->text;
        $mapped = $this->mapper->toRecordEntry($rawText);

        // AgentResponse exposes meta (provider + model) and usage
        // (promptTokens, completionTokens) on the SDK's response data
        // classes. The invocationId is the request UUID — the
        // closest thing to a provider-side response id we can pin in
        // our usage log.
        return new AgentResult(
            rawText: $rawText,
            structured: $mapped['entry'],
            validationErrors: $mapped['errors'],
            provider: $this->stringOr($response->meta->provider, $request->profile->primaryProvider()),
            model: $this->stringOr($response->meta->model, $request->profile->model),
            tokensIn: $response->usage->promptTokens,
            tokensOut: $response->usage->completionTokens,
            responseId: $response->invocationId !== '' ? $response->invocationId : null,
        );
    }

    /**
     * Resolve the SDK's provider arg from the profile's provider list.
     *
     * One provider → plain string (fastest path, no failover overhead).
     * Multiple → array form picks primary then fallbacks. The SDK
     * accepts both shapes.
     *
     * @return string|array<int,string>
     */
    private function buildProviderArg(AgentRequest $request): string|array
    {
        $providers = $request->profile->providers;
        if (count($providers) === 1) {
            return $providers[0];
        }

        return $providers;
    }

    private function stringOr(mixed $value, string $fallback): string
    {
        return is_string($value) && $value !== '' ? $value : $fallback;
    }
}
