<?php

namespace Tests\Unit\Models;

use App\Models\Locale;
use App\Models\Tag;
use App\Models\Translation;
use App\Models\TranslationValue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TranslationTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes()
    {
        $translation = new Translation();
        $this->assertEquals(['key'], $translation->getFillable());
    }

    public function test_values_relationship()
    {
        $translation = Translation::factory()->create();
        $value = TranslationValue::factory()->create(['translation_id' => $translation->id]);

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Relations\HasMany', $translation->values());
        $this->assertTrue($translation->values->contains($value));
    }

    public function test_tags_relationship()
    {
        $translation = Translation::factory()->create();
        $tag = Tag::factory()->create();
        $translation->tags()->attach($tag);

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Relations\BelongsToMany', $translation->tags());
        $this->assertTrue($translation->tags->contains($tag));
    }

    public function test_create_translation_with_content_and_tags()
    {
        $data = [
            'key' => 'test.key',
            'content' => [
                'en' => 'Hello',
                'es' => 'Hola'
            ],
            'tags' => ['api', 'greeting']
        ];

        $translationModel = new Translation();
        $translation = $translationModel->createTranslation($data);

        $this->assertEquals('test.key', $translation->key);
        $this->assertCount(2, $translation->values);
        $this->assertCount(2, $translation->tags);
        $this->assertDatabaseHas('locales', ['code' => 'en']);
        $this->assertDatabaseHas('locales', ['code' => 'es']);
        $this->assertDatabaseHas('tags', ['name' => 'api']);
        $this->assertDatabaseHas('tags', ['name' => 'greeting']);
    }

    public function test_update_translation_content_upserts()
    {
        $translation = Translation::factory()->create(['key' => 'old.key']);
        $locale = Locale::factory()->create(['code' => 'en']);
        TranslationValue::factory()->create([
            'translation_id' => $translation->id,
            'locale_id' => $locale->id,
            'value' => 'Old Value'
        ]);

        $data = [
            'key' => 'new.key',
            'content' => [
                'en' => 'New Value',
                'fr' => 'Bonjour'
            ]
        ];

        $translation->updateTranslation($data);

        $this->assertEquals('new.key', $translation->key);
        $this->assertEquals('New Value', $translation->values()->where('locale_id', $locale->id)->first()->value);
        $this->assertDatabaseHas('locales', ['code' => 'fr']);
    }

    public function test_cache_invalidation_on_save()
    {
        Cache::shouldReceive('forget')->withAnyArgs()->atLeast()->once();
        Cache::shouldReceive('increment')->with('translations_version')->once();

        Translation::factory()->create();
    }

    public function test_index_translation_search_by_key()
    {
        Translation::factory()->create(['key' => 'search.me']);
        Translation::factory()->create(['key' => 'ignore.me']);

        $translationModel = new Translation();
        $results = $translationModel->indexTranslation(['search' => 'search']);

        $this->assertCount(1, $results);
        $this->assertEquals('search.me', $results[0]->key);
    }
}
