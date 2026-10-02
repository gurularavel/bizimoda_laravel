<?php

namespace App\Models;

use App\Services\MenuBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

class MenuItem extends Model
{
    use HasTranslations;

    public const TYPES = [
        'category' => 'Kateqoriya',
        'product' => 'Məhsul',
        'page' => 'Səhifə',
        'blog' => 'Bloq (ana səhifə)',
        'blog_category' => 'Bloq kateqoriyası',
        'blog_post' => 'Bloq yazısı',
        'route' => 'Daxili link (sayt daxili yol)',
        'url' => 'Xarici link (tam URL)',
        'none' => 'Linksiz (yalnız başlıq)',
    ];

    public const DISPLAYS = [
        'link' => 'Adi link',
        'flyout' => 'Açılan siyahı (flyout) — uşaqları sol siyahıda',
        'mega' => 'Meqa menyu — uşaqları sütunlarda',
        'group' => 'Meqa menyu sütun qrupu (başlıq + linklər)',
    ];

    // Daxili linklər üçün qısa adlar → route adı
    public const ROUTE_SHORTCUTS = [
        'home' => 'front.home',
        'cart' => 'front.cart',
        'checkout' => 'front.checkout',
        'specials' => 'front.specials',
        'search' => 'front.search',
        'blog' => 'front.blog',
        'account' => 'front.account',
        'login' => 'front.login',
        'register' => 'front.register',
        'wishlist' => 'front.wishlist',
        'compare' => 'front.compare',
        'contact' => 'front.contact',
    ];

    public array $translatable = ['title'];

    protected $fillable = ['menu_id', 'parent_id', 'title', 'type', 'linkable_id', 'url', 'target_blank', 'display', 'column',
        'auto_children', 'icon', 'image', 'banner_image', 'banner_url', 'css_class', 'is_active', 'sort'];

    protected $casts = ['target_blank' => 'boolean', 'auto_children' => 'boolean', 'is_active' => 'boolean'];

    protected static function booted(): void
    {
        static::saved(fn () => MenuBuilder::flush());
        static::deleted(fn () => MenuBuilder::flush());
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent()
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('sort');
    }

    /** Hədəf obyektin modeli (category/product/page/...) */
    public function linkableModel(): ?string
    {
        return [
            'category' => Category::class,
            'product' => Product::class,
            'page' => Page::class,
            'blog_category' => BlogCategory::class,
            'blog_post' => BlogPost::class,
        ][$this->type] ?? null;
    }

    public function resolveUrl(?Model $linkable, string $locale): ?string
    {
        switch ($this->type) {
            case 'none':
                return null;
            case 'url':
                return $this->url;
            case 'blog':
                return route('front.blog', ['locale' => $locale]);
            case 'route':
                $path = trim((string) $this->url);
                if (isset(self::ROUTE_SHORTCUTS[$path])) {
                    return route(self::ROUTE_SHORTCUTS[$path], ['locale' => $locale]);
                }
                if (Str::startsWith($path, ['http://', 'https://', '#', 'javascript:', 'tel:', 'mailto:'])) {
                    return $path;
                }

                return url($locale.'/'.ltrim($path, '/'));
            default:
                return $linkable && method_exists($linkable, 'url') ? $linkable->url($locale) : null;
        }
    }
}
