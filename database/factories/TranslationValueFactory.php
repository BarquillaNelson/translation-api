<?php

namespace Database\Factories;

use App\Models\Locale;
use App\Models\Translation;
use App\Models\TranslationValue;
use Illuminate\Database\Eloquent\Factories\Factory;

class TranslationValueFactory extends Factory
{
    protected $model = TranslationValue::class;

    public function definition(): array
    {
        return [
            'translation_id' => Translation::factory(),
            'locale_id' => Locale::factory(),
            'value' => $this->faker->sentence,
        ];
    }
}
