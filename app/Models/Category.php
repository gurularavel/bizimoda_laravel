<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatedSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Kalnoy\Nestedset\NodeTrait;
use Spatie\Translatable\HasTranslations;

class Category extends Model
{
    use HasTranslatedSlug, HasTranslations, NodeTrait;

    public array $translatable = ['name', 'slug', 'description', 'meta_title', 'meta_description'];

    protected $fillable = ['parent_id', 'name', 'slug', 'description', 'meta_title', 'meta_description', 'image', 'banner',
        'is_active', 'show_in_filter', 'filter_inherit', 'sort'];

    protected $casts = ['is_active' => 'boolean', 'show_in_filter' => 'boolean', 'filter_inherit' => 'boolean'];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    public function mainProducts(): HasMany
    {
        return $this->hasMany(Product::class, 'main_category_id');
    }

    public function filters(): HasMany
    {
        return $this->hasMany(CategoryFilter::class)->orderBy('sort');
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function url(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return route('front.slug', ['locale' => $locale, 'slug' => $this->getTranslation('slug', $locale)]);
    }

    /**
     * Bu kateqoriyada göstəriləcək filtrlər. Öz ayarı yoxdursa (və miras aktivdirsə)
     * ən yaxın valideynin ayarı, heç biri yoxdursa standart dəst.
     */
    public function effectiveFilters()
    {
        $node = $this;
        while ($node) {
            $filters = $node->filters;
            if ($filters->isNotEmpty()) {
                return $filters;
            }
            if (! $node->filter_inherit) {
                break;
            }
            $node = $node->parent;
        }

        return collect(CategoryFilter::DEFAULTS)->map(fn ($type, $i) => new CategoryFilter(['type' => $type, 'sort' => $i]));
    }
}
