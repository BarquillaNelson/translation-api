<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TranslationValue extends Model
{
    use HasFactory;
    protected $fillable = ['translation_id', 'locale_id', 'value'];

    public function translation(): BelongsTo
    {
        return $this->belongsTo(Translation::class);
    }

    public function locale(): BelongsTo
    {
        return $this->belongsTo(Locale::class);
    }
}
