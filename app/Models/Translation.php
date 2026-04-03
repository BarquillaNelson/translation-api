<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;

class Translation extends Model
{
    use HasFactory;
    protected $fillable = ['key'];

    protected static function booted()
    {
        static::saved(function ($translation) {
            Cache::forget("translation_{$translation->id}");
            Cache::forget('translations_export');
            Cache::increment('translations_version'); 
        });

        static::deleted(function ($translation) {
            Cache::forget("translation_{$translation->id}");
            Cache::forget('translations_export');
            Cache::increment('translations_version');
        });
    }

    public function values(): HasMany
    {
        return $this->hasMany(TranslationValue::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function indexTranslation(array $data)
    {
        $pages = $data['pages'] ?? 20;
        $search = $data['search'] ?? '';
        
        $cursor = request()->get('cursor', 'first_page'); 
        $version = Cache::get('translations_version', 1);
        $searchHash = $search ? md5($search) : 'no_search';
        
        $cacheKey = "translations_c_{$cursor}_s_{$searchHash}_size_{$pages}_v{$version}";

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($pages, $search) {
            return $this->select(['id', 'key'])
            ->with([
                'values:id,translation_id,locale_id,value',
                'values.locale:id,code',
                'tags:id,name'
            ])
            ->when($search, function ($query, $search) {
                $query->where(function ($groupedQuery) use ($search) {
                    $groupedQuery->where('key', 'like', "%{$search}%")
                    ->orWhereHas('values', function ($valueQuery) use ($search) {
                        $valueQuery->where('value', 'like', "%{$search}%");
                    })
                    ->orWhereHas('tags', function ($tagQuery) use ($search) {
                        $tagQuery->where('name', 'like', "%{$search}%");
                    });
                }); 
            })
            ->orderBy('id', 'asc')
            ->cursorPaginate($pages);
        });
    }

    public function showTranslation($id)
    {
        $cacheKey = "translation_{$id}";

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($id) {
            return $this->select(['id', 'key'])
            ->with([
                'values:id,translation_id,locale_id,value',
                'values.locale:id,code',
                'tags:id,name'
            ])
            ->findOrFail($id);
        });
    }

    public function createTranslation($data)
    {
        $trans = $this->create(['key' => $data['key']]);

        if (!empty($data['tags'])) {
            $tagIds = $this->getOrCreateTags($data['tags']);
            $trans->tags()->sync($tagIds);
        }

        if (!empty($data['content'])) {
            $localeIds = $this->getOrCreateLocales(array_keys($data['content']));
            $valuesToInsert = [];
            $now = now();
            
            foreach ($data['content'] as $localeCode => $text) {
                $valuesToInsert[] = [
                    'translation_id' => $trans->id,
                    'locale_id'      => $localeIds[$localeCode],
                    'value'          => $text,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ];
            }

            if (!empty($valuesToInsert)) {
                TranslationValue::insert($valuesToInsert);
            }
        }

        return $trans->load([
            'values:id,translation_id,locale_id,value',
            'values.locale:id,code',
            'tags:id,name'
        ]);
    }

    public function updateTranslation($data)
    {
        if (array_key_exists('key', $data)) {
            $this->update(['key' => $data['key']]);
        }

        if (array_key_exists('tags', $data)) {
            $tagIds = $this->getOrCreateTags($data['tags']);
            $this->tags()->sync($tagIds);
        }

        if (array_key_exists('content', $data)) {
            $localeIds = $this->getOrCreateLocales(array_keys($data['content']));
            $upsertData = [];
            
            foreach ($data['content'] as $localeCode => $text) {
                $upsertData[] = [
                    'translation_id' => $this->id,
                    'locale_id'      => $localeIds[$localeCode],
                    'value'          => $text,
                ];
            }

            if (!empty($upsertData)) {
                TranslationValue::upsert(
                    $upsertData,
                    ['translation_id', 'locale_id'],
                    ['value'] 
                );
            }
        }

        return $this->load([
            'values:id,translation_id,locale_id,value',
            'values.locale:id,code',
            'tags:id,name'
        ]);
    }

    private function getOrCreateTags(array $tagNames): array
    {
        $existingTags = Tag::whereIn('name', $tagNames)->pluck('id', 'name');
        $missingTags = array_diff($tagNames, $existingTags->keys()->toArray());

        if (!empty($missingTags)) {
            $insertData = array_map(fn($name) => ['name' => $name], $missingTags);
            Tag::insert($insertData);
            
            $existingTags = Tag::whereIn('name', $tagNames)->pluck('id', 'name');
        }

        return $existingTags->values()->toArray();
    }

    private function getOrCreateLocales(array $localeCodes): array
    {
        $existingLocales = Locale::whereIn('code', $localeCodes)->pluck('id', 'code');
        $missingLocales = array_diff($localeCodes, $existingLocales->keys()->toArray());

        if (!empty($missingLocales)) {
            $insertData = array_map(fn($code) => ['code' => $code], $missingLocales);
            Locale::insert($insertData);
            
            $existingLocales = Locale::whereIn('code', $localeCodes)->pluck('id', 'code');
        }

        return $existingLocales->toArray();
    }
}