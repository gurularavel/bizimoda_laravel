<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/**
 * Ana səhifə bölməsi. data:
 *  slider:   {"slider_id": 1}
 *  products: {"source": "featured|special|new|popular|category|ids", "category_id": 1, "ids": [], "limit": 12}
 *  banners:  {"items": [{"image": "...", "link": "..."}]}
 *  html:     {"html": {"az": "...", ...}}
 *  blog:     {"limit": 4}
 */
class HomeSection extends Model
{
    use HasTranslations;

    public const TYPES = [
        'slider' => 'Slayder',
        'products' => 'Məhsul karuseli',
        'banners' => 'Bannerlər',
        'html' => 'HTML / mətn',
        'blog' => 'Son bloq yazıları',
    ];

    public const PRODUCT_SOURCES = [
        'featured' => 'Seçilmiş məhsullar',
        'special' => 'Endirimli məhsullar',
        'new' => 'Yeni məhsullar',
        'popular' => 'Ən çox baxılanlar',
        'category' => 'Kateqoriyadan',
        'ids' => 'Əl ilə seçilmiş',
    ];

    public array $translatable = ['title'];

    protected $fillable = ['type', 'title', 'data', 'module_class', 'is_active', 'sort'];

    protected $casts = ['data' => 'array', 'is_active' => 'boolean'];
}
