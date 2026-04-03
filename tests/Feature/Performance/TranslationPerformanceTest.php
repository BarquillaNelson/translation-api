<?php

namespace Tests\Feature\Performance;

use App\Models\Translation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Benchmark;
use Tests\TestCase;

class TranslationPerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_bulk_create_1000_translations_under_30_seconds()
    {
        $translationsData = [];
        for ($i = 0; $i < 1000; $i++) {
            $translationsData[] = [
                'key' => "key.{$i}",
                'content' => ['en' => "Value {$i}"],
                'tags' => ['perf']
            ];
        }

        $startTime = microtime(true);
        $translationModel = new Translation();
        foreach ($translationsData as $data) {
            $translationModel->createTranslation($data);
        }
        $endTime = microtime(true);

        $executionTime = $endTime - $startTime;
        $this->assertLessThan(30, $executionTime, "Execution time for 1000 translations should be under 30s. Actual: {$executionTime}s");
    }

    public function test_index_with_500_records_responds_under_200ms()
    {
        Translation::factory()->count(500)->create();

        $startTime = microtime(true);
        $response = $this->actingAs($this->user)
            ->getJson('/api/translations');
        $endTime = microtime(true);

        $executionTime = ($endTime - $startTime) * 1000;
        $response->assertStatus(200);
        $this->assertLessThan(200, $executionTime, "Index search with 500 records should be under 200ms. Actual: {$executionTime}ms");
    }

    public function test_show_single_translation_responds_under_100ms()
    {
        $translation = Translation::factory()->create();

        $startTime = microtime(true);
        $response = $this->actingAs($this->user)
            ->getJson("/api/translations/{$translation->id}");
        $endTime = microtime(true);

        $executionTime = ($endTime - $startTime) * 1000;
        $response->assertStatus(200);
        $this->assertLessThan(100, $executionTime, "Show translation should be under 100ms. Actual: {$executionTime}ms");
    }
}
