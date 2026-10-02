<?php

namespace App\Models\Concerns;

use App\Support\Locales;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Hər dil üçün ayrıca slug saxlayır (JSON sütun). Boş qalan slug-lar
 * həmin dildəki addan (və ya default dildəki addan) avtomatik yaradılır və
 * cədvəl daxilində unikal edilir.
 */
trait HasTranslatedSlug
{
    public static function bootHasTranslatedSlug(): void
    {
        static::saving(function ($model) {
            $source = $model->slugSourceField();
            $slugs = [];

            foreach (Locales::codes() as $locale) {
                $slug = trim((string) $model->getTranslation('slug', $locale, false));
                if ($slug === '') {
                    $slug = (string) ($model->getTranslation($source, $locale, false)
                        ?: $model->getTranslation($source, Locales::default(), false));
                }
                $slug = Str::slug($slug) ?: Str::lower(Str::random(6));
                $slugs[$locale] = $model->uniqueSlug($slug, $locale);
            }

            $model->setTranslations('slug', $slugs);
        });
    }

    protected function slugSourceField(): string
    {
        return property_exists($this, 'slugSource') ? $this->slugSource : 'name';
    }

    protected function uniqueSlug(string $slug, string $locale): string
    {
        $candidate = $slug;
        $i = 2;
        while (static::query()
            ->where("slug->{$locale}", $candidate)
            ->when($this->exists, fn ($q) => $q->whereKeyNot($this->getKey()))
            ->exists()) {
            $candidate = $slug.'-'.$i++;
        }

        return $candidate;
    }

    public function scopeWhereSlug(Builder $query, string $slug, ?string $locale = null): Builder
    {
        $locale ??= app()->getLocale();

        return $query->where("slug->{$locale}", $slug);
    }
}
