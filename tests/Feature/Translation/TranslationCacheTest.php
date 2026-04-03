<?php

namespace Tests\Feature\Translation;

use App\Models\Translation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TranslationCacheTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_show_is_cached()
    {
        $translation = Translation::factory()->create();

        $this->actingAs($this->user)->getJson("/api/translations/{$translation->id}");
        
        $this->assertTrue(Cache::has("translation_{$translation->id}"));
    }

    public function test_cache_is_invalidated_on_update()
    {
        $translation = Translation::factory()->create();
        Cache::put("translation_{$translation->id}", 'cached data');

        $this->actingAs($this->user)
            ->putJson("/api/translations/{$translation->id}", [
                'key' => 'updated.key'
            ]);

        $this->assertFalse(Cache::has("translation_{$translation->id}"));
    }
}
