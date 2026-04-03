<?php

namespace Tests\Feature\Translation;

use App\Models\Translation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranslationCrudTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_unauthenticated_user_cannot_access_translations()
    {
        $this->getJson('/api/translations')->assertStatus(401);
    }

    public function test_can_create_translation()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/translations', [
                'key' => 'api.success',
                'content' => ['en' => 'Success'],
                'tags' => ['api']
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('result.key', 'api.success');

        $this->assertDatabaseHas('translations', ['key' => 'api.success']);
    }

    public function test_can_list_translations()
    {
        Translation::factory()->count(3)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/translations');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'result.data');
    }

    public function test_can_show_translation()
    {
        $translation = Translation::factory()->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/translations/{$translation->id}");

        $response->assertStatus(200)
            ->assertJsonPath('result.id', $translation->id);
    }

    public function test_can_delete_translation()
    {
        $translation = Translation::factory()->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/translations/{$translation->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('translations', ['id' => $translation->id]);
    }
}
