<?php

declare(strict_types=1);

namespace KraenzleRitter\AiCataloging\Tests\Output;

use KraenzleRitter\AiCataloging\Output\OutputMapper;
use PHPUnit\Framework\TestCase;

final class OutputMapperTest extends TestCase
{
    private OutputMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new OutputMapper(tenantSlug: 'kr', generator: 'ai-cataloging@test');
    }

    public function test_parses_valid_record_entry(): void
    {
        $raw = json_encode([
            'type' => 'record',
            'uuid' => '11111111-1111-1111-1111-111111111111',
            'title' => ['de' => 'Inventardurchsicht 2026'],
            'level_of_description' => 'item',
        ], JSON_THROW_ON_ERROR);

        $result = $this->mapper->toRecordEntry($raw);

        $this->assertSame([], $result['errors']);
        $this->assertIsArray($result['entry']);
        $this->assertSame('record', $result['entry']['type']);
        $this->assertSame('Inventardurchsicht 2026', $result['entry']['title']['de']);
    }

    public function test_strips_json_code_fence(): void
    {
        $raw = "```json\n".json_encode([
            'type' => 'record',
            'uuid' => '11111111-1111-1111-1111-111111111111',
            'title' => ['de' => 'Mit Fence'],
        ], JSON_THROW_ON_ERROR)."\n```";

        $result = $this->mapper->toRecordEntry($raw);
        $this->assertSame([], $result['errors']);
        $this->assertNotNull($result['entry']);
        $this->assertSame('Mit Fence', $result['entry']['title']['de']);
    }

    public function test_strips_unlabeled_code_fence(): void
    {
        $raw = "```\n".json_encode([
            'type' => 'record',
            'uuid' => '11111111-1111-1111-1111-111111111111',
            'title' => ['de' => 'Fence ohne lang'],
        ], JSON_THROW_ON_ERROR)."\n```";

        $result = $this->mapper->toRecordEntry($raw);
        $this->assertSame([], $result['errors']);
    }

    public function test_malformed_json_reports_error(): void
    {
        $result = $this->mapper->toRecordEntry('not json at all { ');

        $this->assertNull($result['entry']);
        $this->assertNotEmpty($result['errors']);
        $this->assertSame('json', $result['errors'][0]['keyword']);
    }

    public function test_non_object_root_reports_error(): void
    {
        $result = $this->mapper->toRecordEntry('"plain string"');

        $this->assertNull($result['entry']);
        $this->assertSame('type', $result['errors'][0]['keyword']);
    }

    public function test_missing_required_fields_reports_error(): void
    {
        // record entry without `title` — required by schema
        $raw = json_encode([
            'type' => 'record',
            'uuid' => '11111111-1111-1111-1111-111111111111',
        ], JSON_THROW_ON_ERROR);

        $result = $this->mapper->toRecordEntry($raw);

        $this->assertNull($result['entry']);
        $this->assertNotEmpty($result['errors']);
    }
}
