<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Option extends Model
{
    use HasTranslations;

    public const TYPES = ['color' => 'Rəng (swatch)', 'select' => 'Seçim siyahısı', 'radio' => 'Radio düymələr', 'image' => 'Şəkilli seçim'];

    public array $translatable = ['name'];

    protected $fillable = ['name', 'type', 'sort'];

    public function values()
    {
        return $this->hasMany(OptionValue::class)->orderBy('sort');
    }
}
