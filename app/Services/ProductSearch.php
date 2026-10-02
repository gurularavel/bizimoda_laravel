<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Məhsul/kateqoriya axtarışı (axtarış səhifəsi və header-dəki canlı təkliflər).
 *
 * Azərbaycan hərfləri normallaşdırılır (ə→e, ç→c, ş→s, ı→i ...), ona görə "carpayi"
 * yazanda "Çarpayı" da tapılır. Sorğu sözlərə bölünür, hər söz ayrıca uyğun gəlməlidir.
 */
class ProductSearch
{
    protected const MAP = ['İ' => 'i', 'I' => 'i', 'ı' => 'i', 'ə' => 'e', 'Ə' => 'e', 'ö' => 'o', 'Ö' => 'o', 'ü' => 'u', 'Ü' => 'u',
        'ş' => 's', 'Ş' => 's', 'ç' => 'c', 'Ç' => 'c', 'ğ' => 'g', 'Ğ' => 'g'];

    public static function normalize(string $text): string
    {
        return mb_strtolower(strtr($text, self::MAP));
    }

    /** @return string[] normallaşdırılmış axtarış sözləri */
    public static function words(string $term): array
    {
        $words = preg_split('/\s+/u', self::normalize(trim($term)), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_slice(array_values(array_unique($words)), 0, 6);
    }

    /**
     * Uyğunluq sıralaması: adı sorğu ilə başlayanlar əvvəl, sonra sözün əvvəlində uyğun gələnlər.
     * Filtr facet-ləri (group by) üçün klonlanan baza sorğusuna deyil, yalnız siyahı sorğusuna tətbiq edilməlidir.
     */
    public function rank(Builder $query, string $term, ?string $locale = null): Builder
    {
        $words = self::words($term);
        if (! $words) {
            return $query;
        }

        $name = $this->sqlNormalize($this->jsonField('name', $locale ?? app()->getLocale()));
        $phrase = $this->escapeLike(implode(' ', $words));

        return $query->orderByRaw("CASE WHEN {$name} LIKE ? THEN 0 WHEN {$name} LIKE ? THEN 1 ELSE 2 END", [$phrase.'%', '% '.$phrase.'%']);
    }

    /** Məhsul sorğusuna axtarış şərtlərini əlavə edir (hər söz ad və ya SKU-da olmalıdır) */
    public function apply(Builder $query, string $term, ?string $locale = null): Builder
    {
        $words = self::words($term);
        if (! $words) {
            return $query->whereRaw('1 = 0');
        }

        $locale ??= app()->getLocale();
        $name = $this->sqlNormalize($this->jsonField('name', $locale));
        $nameAz = $this->sqlNormalize($this->jsonField('name', 'az'));
        $sku = $this->sqlNormalize('COALESCE(sku, \'\')');

        foreach ($words as $word) {
            $like = '%'.$this->escapeLike($word).'%';
            $query->where(fn (Builder $q) => $q->whereRaw("{$name} LIKE ?", [$like])
                ->orWhereRaw("{$nameAz} LIKE ?", [$like])
                ->orWhereRaw("{$sku} LIKE ?", [$like]));
        }

        return $query;
    }

    /** Header təklifləri üçün uyğun kateqoriyalar */
    public function categories(string $term, int $limit = 4, ?string $locale = null): Collection
    {
        $words = self::words($term);
        if (! $words) {
            return collect();
        }

        $locale ??= app()->getLocale();
        $name = $this->sqlNormalize($this->jsonField('name', $locale));
        $query = Category::query()->active()->with('parent');

        foreach ($words as $word) {
            $query->whereRaw("{$name} LIKE ?", ['%'.$this->escapeLike($word).'%']);
        }

        return $query->withCount(['products' => fn ($q) => $q->visible()])
            ->having('products_count', '>', 0)
            ->orderByRaw("CASE WHEN {$name} LIKE ? THEN 0 ELSE 1 END", [$this->escapeLike($words[0]).'%'])
            ->orderByDesc('products_count')
            ->limit($limit)
            ->get();
    }

    protected function jsonField(string $column, string $locale): string
    {
        $locale = preg_replace('/[^a-z_-]/i', '', $locale);

        return "COALESCE(JSON_UNQUOTE(JSON_EXTRACT({$column}, '$.\"{$locale}\"')), '')";
    }

    /** SQL tərəfində eyni normallaşdırma: LOWER + hərf əvəzləmələri */
    protected function sqlNormalize(string $expr): string
    {
        $sql = $expr;
        foreach (['İ', 'I', 'Ə', 'Ö', 'Ü', 'Ş', 'Ç', 'Ğ'] as $upper) {
            $sql = "REPLACE({$sql}, '{$upper}', '".self::MAP[$upper]."')";
        }
        $sql = "LOWER({$sql})";
        foreach (['ı', 'ə', 'ö', 'ü', 'ş', 'ç', 'ğ'] as $lower) {
            $sql = "REPLACE({$sql}, '{$lower}', '".self::MAP[$lower]."')";
        }

        return $sql;
    }

    protected function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $value);
    }
}
