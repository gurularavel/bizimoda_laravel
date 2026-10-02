<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatedSlug;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Page extends Model
{
    use HasTranslatedSlug, HasTranslations;

    public const TEMPLATES = [
        'default' => 'Standart (sağ sütunla)',
        'full-width' => 'Tam en',
        'contact' => 'Əlaqə səhifəsi (info blokları + forma)',
    ];

    protected string $slugSource = 'title';

    public array $translatable = ['title', 'slug', 'content', 'meta_title', 'meta_description'];

    protected $fillable = ['title', 'slug', 'content', 'meta_title', 'meta_description', 'template', 'is_active', 'sort'];

    protected $casts = ['is_active' => 'boolean'];

    public function blocks()
    {
        return $this->hasMany(PageBlock::class)->orderBy('sort');
    }

    public function url(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return route('front.slug', ['locale' => $locale, 'slug' => $this->getTranslation('slug', $locale)]);
    }
}
