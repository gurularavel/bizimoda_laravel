<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatedSlug;
use App\Services\SetPricingService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Product extends Model
{
    use HasTranslatedSlug, HasTranslations, SoftDeletes;

    public const TYPES = [
        'simple' => 'Sadə məhsul',
        'set' => 'Dəst (modullardan ibarət)',
        'module' => 'Modul (dəstin hissəsi)',
    ];

    public const STOCK_STATUSES = [
        'in_stock' => 'Anbarda var',
        'out_of_stock' => 'Anbarda yoxdur',
        'preorder' => 'Sifarişlə',
    ];

    public array $translatable = ['name', 'slug', 'short_description', 'description', 'dimensions', 'meta_title', 'meta_description', 'label'];

    protected $fillable = ['external_id', 'type', 'sku', 'name', 'slug', 'short_description', 'description', 'dimensions', 'meta_title', 'meta_description',
        'main_category_id', 'brand_id', 'price', 'old_price', 'stock_qty', 'stock_status', 'min_qty', 'label',
        'is_active', 'is_featured', 'sold_separately', 'sort'];

    protected $casts = [
        'price' => 'decimal:2',
        'old_price' => 'decimal:2',
        'computed_price' => 'decimal:2',
        'computed_old_price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sold_separately' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            if ($product->type !== 'set') {
                $product->computed_price = $product->price;
                $product->computed_old_price = $product->old_price && $product->old_price > $product->price ? $product->old_price : null;
            }
        });

        static::saved(function (Product $product) {
            // Əsas kateqoriya həmişə kateqoriyalar siyahısında da olsun
            $product->categories()->syncWithoutDetaching([$product->main_category_id]);

            // Siyahı qiyməti endirim kampaniyaları nəzərə alınmaqla
            if ($product->type !== 'set') {
                app(SetPricingService::class)->refreshComputed($product);
            }

            // Modulun qiyməti dəyişibsə, onu ehtiva edən dəstlərin hesablanmış qiymətini yenilə
            if ($product->wasChanged(['price', 'old_price']) && $product->type !== 'set') {
                app(SetPricingService::class)->refreshSetsContaining($product);
            }
        });
    }

    // ---- Əlaqələr ----

    public function mainCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'main_category_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort');
    }

    public function productOptions(): HasMany
    {
        return $this->hasMany(ProductOption::class)->orderBy('sort');
    }

    public function optionValues(): HasMany
    {
        return $this->hasMany(ProductOptionValue::class);
    }

    public function setItems(): HasMany
    {
        return $this->hasMany(ProductSetItem::class, 'set_id')->orderBy('sort');
    }

    public function partOfSets(): HasMany
    {
        return $this->hasMany(ProductSetItem::class, 'component_id');
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class);
    }

    public function related(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_related', 'product_id', 'related_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    // ---- Scope-lar ----

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    /** Vitrin: aktiv və ayrıca satıla bilən (modul olsa belə sold_separately=true) */
    public function scopeVisible(Builder $q): Builder
    {
        return $q->where('is_active', true)
            ->where(fn ($w) => $w->where('type', '!=', 'module')->orWhere('sold_separately', true));
    }

    /** Məhsul kartı üçün lazım olan əlaqələr */
    public function scopeForListing(Builder $q): Builder
    {
        return $q->with(['images', 'mainCategory'])
            ->withAvg(['reviews' => fn ($r) => $r->where('is_approved', true)], 'rating');
    }

    public function scopeOnSale(Builder $q): Builder
    {
        return $q->whereNotNull('computed_old_price')->whereColumn('computed_old_price', '>', 'computed_price');
    }

    // ---- Köməkçilər ----

    public function isSet(): bool
    {
        return $this->type === 'set';
    }

    public function url(?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $category = $this->relationLoaded('mainCategory') ? $this->mainCategory : $this->mainCategory()->first();

        return route('front.product', [
            'locale' => $locale,
            'category' => $category?->getTranslation('slug', $locale) ?? 'p',
            'product' => $this->getTranslation('slug', $locale),
        ]);
    }

    public function mainImage(): ?string
    {
        $images = $this->relationLoaded('images') ? $this->images : $this->images()->get();

        return $images->first()?->path;
    }

    public function hasDiscount(): bool
    {
        return $this->computed_old_price !== null && (float) $this->computed_old_price > (float) $this->computed_price;
    }

    public function discountPercent(): int
    {
        if (! $this->hasDiscount()) {
            return 0;
        }

        return (int) round((1 - (float) $this->computed_price / (float) $this->computed_old_price) * 100);
    }

    public function inStock(): bool
    {
        return $this->stock_status !== 'out_of_stock';
    }

    public function requiresConfiguration(): bool
    {
        return $this->productOptions()->where('is_required', true)->exists();
    }

    public function averageRating(): float
    {
        return (float) $this->reviews()->where('is_approved', true)->avg('rating');
    }
}
