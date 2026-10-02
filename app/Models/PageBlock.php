<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Səhifə qurucusunun bloku. data JSON-da dil açarları ilə saxlanılır:
 *  text:     {"content": {"az": "...", "ru": "..."}}
 *  html:     {"html": "..."}
 *  image:    {"image": "path", "link": "...", "alt": {...}}
 *  banner:   {"items": [{"image": "...", "link": "..."}]}
 *  products: {"title": {...}, "source": "featured|category|special|new|ids", "category_id": 1, "ids": [..], "limit": 10}
 *  faq:      {"items": [{"q": {...}, "a": {...}}]}
 *  form:     {"title": {...}}
 */
class PageBlock extends Model
{
    public const TYPES = [
        'text' => 'Mətn (redaktor)',
        'image' => 'Şəkil',
        'banner' => 'Banner(lər)',
        'products' => 'Məhsul karuseli',
        'faq' => 'Sual-cavab',
        'html' => 'HTML kod',
        'form' => 'Əlaqə forması',
    ];

    protected $fillable = ['page_id', 'type', 'data', 'sort'];

    protected $casts = ['data' => 'array'];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    /** data içindəki tərcümə olunan dəyəri cari dildə qaytarır */
    public function t(string $key, mixed $source = null): ?string
    {
        $value = $source ?? data_get($this->data, $key);
        if (is_array($value)) {
            return $value[app()->getLocale()] ?? $value[config('app.fallback_locale')] ?? null;
        }

        return $value;
    }
}
