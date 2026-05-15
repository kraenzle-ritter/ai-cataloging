<?php

declare(strict_types=1);

namespace KraenzleRitter\AiCataloging\Tests\Profile;

use KraenzleRitter\AiCataloging\Profile\EffectiveProfile;
use KraenzleRitter\AiCataloging\Profile\FieldConstraint;
use KraenzleRitter\AiCataloging\Profile\VideoStrategy;
use PHPUnit\Framework\TestCase;

final class EffectiveProfileTest extends TestCase
{
    public function test_constructs_with_minimal_valid_input(): void
    {
        $profile = $this->makeProfile();

        $this->assertSame('anton-default', $profile->slug);
        $this->assertSame(1, $profile->version);
        $this->assertSame('openai', $profile->primaryProvider());
        $this->assertSame(['anthropic'], $profile->fallbackProviders());
    }

    public function test_empty_provider_list_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new EffectiveProfile(
            slug: 'x',
            version: 1,
            providers: [],
            model: 'gpt-4o',
            promptTemplate: 'describe this',
            fields: [],
            contextConfig: [],
            languageHint: null,
            videoStrategy: VideoStrategy::Direct,
            videoCapSeconds: 1800,
            frameIntervalSeconds: 5,
            sceneDetectionEnabled: true,
            auditEnabled: true,
            auditThreshold: 100,
        );
    }

    public function test_zero_video_cap_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new EffectiveProfile(
            slug: 'x',
            version: 1,
            providers: ['openai'],
            model: 'gpt-4o',
            promptTemplate: 'describe',
            fields: [],
            contextConfig: [],
            languageHint: null,
            videoStrategy: VideoStrategy::Direct,
            videoCapSeconds: 0,
            frameIntervalSeconds: 5,
            sceneDetectionEnabled: true,
            auditEnabled: true,
            auditThreshold: 100,
        );
    }

    public function test_fallbacks_empty_when_only_one_provider(): void
    {
        $profile = new EffectiveProfile(
            slug: 'solo',
            version: 1,
            providers: ['openai'],
            model: 'gpt-4o',
            promptTemplate: '',
            fields: [],
            contextConfig: [],
            languageHint: null,
            videoStrategy: VideoStrategy::Direct,
            videoCapSeconds: 1800,
            frameIntervalSeconds: 5,
            sceneDetectionEnabled: false,
            auditEnabled: false,
            auditThreshold: 0,
        );
        $this->assertSame([], $profile->fallbackProviders());
    }

    private function makeProfile(): EffectiveProfile
    {
        return new EffectiveProfile(
            slug: 'anton-default',
            version: 1,
            providers: ['openai', 'anthropic'],
            model: 'gpt-4o',
            promptTemplate: 'Describe this archival record.',
            fields: [
                'title' => new FieldConstraint(required: true),
                'creation_date_start' => new FieldConstraint(required: false),
            ],
            contextConfig: ['cap_seconds' => 1800],
            languageHint: 'de',
            videoStrategy: VideoStrategy::Direct,
            videoCapSeconds: 1800,
            frameIntervalSeconds: 5,
            sceneDetectionEnabled: true,
            auditEnabled: true,
            auditThreshold: 100,
        );
    }
}
