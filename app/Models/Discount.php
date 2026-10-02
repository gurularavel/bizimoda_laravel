<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Endirim kampaniyası: seçilmiş məhsullara, kateqoriyalara (alt kateqoriyalar daxil) və ya bütün məhsullara
 * faiz (%) və ya sabit məbləğ (₼) endirimi. Bir məhsula bir neçə endirim düşərsə ən sərfəlisi tətbiq olunur.
 */
class Discount extends Model
{
    public const TYPES = ['percent' => 'Faiz (%)', 'fixed' => 'Məbləğ (₼)'];

    public const SCOPES = ['products' => 'Seçilmiş məhsullar', 'categories' => 'Seçilmiş kateqoriyalar', 'all' => 'Bütün məhsullar'];

    protected $fillable = ['name', 'type', 'value', 'applies_to', 'starts_at', 'ends_at', 'is_active'];

    protected $casts = ['value' => 'decimal:2', 'starts_at' => 'datetime', 'ends_at' => 'datetime', 'is_active' => 'boolean'];

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function scopeRunning(Builder $q): Builder
    {
        return $q->where('is_active', true)
            ->where(fn ($w) => $w->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($w) => $w->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    /** Verilmiş qiymətdən çıxılacaq məbləğ (qiymətdən çox ola bilməz) */
    public function amountFor(float $price): float
    {
        $amount = $this->type === 'percent'
            ? round($price * min(100, (float) $this->value) / 100, 2)
            : (float) $this->value;

        return max(0, min($price, $amount));
    }

    public function status(): string
    {
        if (! $this->is_active) {
            return 'disabled';
        }
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return 'scheduled';
        }
        if ($this->ends_at && $this->ends_at->isPast()) {
            return 'expired';
        }

        return 'running';
    }

    public function label(): string
    {
        return $this->type === 'percent'
            ? rtrim(rtrim(number_format((float) $this->value, 2, '.', ''), '0'), '.').'%'
            : money($this->value);
    }
}
