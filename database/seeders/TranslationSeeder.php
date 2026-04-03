<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Locale;
use App\Models\Tag;
use Illuminate\Support\Str;

class TranslationSeeder extends Seeder
{
    public function run(): void
    {
        $totalRecords = 100000;
        $chunkSize = 1000;

        $this->command->info("Preparing to seed {$totalRecords} translations...");

        // 1. Ensure basic Locales and Tags exist first
        $locales = ['en', 'fr', 'es'];
        $localeIds = [];
        foreach ($locales as $code) {
            $localeIds[$code] = Locale::firstOrCreate(['code' => $code])->id;
        }

        $tags = ['web', 'mobile', 'desktop'];
        $tagIds = [];
        foreach ($tags as $name) {
            $tagIds[] = Tag::firstOrCreate(['name' => $name])->id;
        }

        // Setup Progress Bar
        $bar = $this->command->getOutput()->createProgressBar($totalRecords);
        $bar->start();

        // Get the starting ID for translations
        $startTranslationId = DB::table('translations')->max('id') + 1;

        for ($i = 0; $i < $totalRecords; $i += $chunkSize) {
            $translations = [];
            $translationValues = [];
            $tagPivots = [];

            $limit = min($chunkSize, $totalRecords - $i);

            for ($j = 0; $j < $limit; $j++) {
                $currentId = $startTranslationId++;
                $key = 'test.key.' . Str::random(8) . '_' . $currentId;
                $now = now();

                // Build Translation row
                $translations[] = [
                    'id'         => $currentId,
                    'key'        => $key,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                // Build Translation Values (1 for each locale)
                foreach ($localeIds as $code => $localeId) {
                    $translationValues[] = [
                        'translation_id' => $currentId,
                        'locale_id'      => $localeId,
                        'value'          => "Value for {$key} in {$code}",
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ];
                }

                // Build Tag Pivots (Assign 1 random tag per translation)
                $tagPivots[] = [
                    'translation_id' => $currentId,
                    'tag_id'         => $tagIds[array_rand($tagIds)],
                ];
            }

            // Execute raw bulk inserts for massive performance gains
            DB::table('translations')->insert($translations);
            DB::table('translation_values')->insert($translationValues);
            DB::table('tag_translation')->insert($tagPivots);

            $bar->advance($limit);
        }

        $bar->finish();
        $this->command->newLine(2);
        $this->command->info("Successfully seeded {$totalRecords} translations!");
    }
}