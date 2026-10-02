<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Dəstin bir modulu (məs. "Aypara tumba"): standart say, minimum/maksimum say.
 * min_qty = 2 olarsa müştəri bu moduldan 1 ədəd seçə bilməz (0 yalnız modul məcburi deyilsə).
 */
class ProductSetItem extends Model
{
    public $timestamps = false;

    protected $fillable = ['set_id', 'component_id', 'default_qty', 'min_qty', 'max_qty', 'is_required', 'price_override', 'old_price_override', 'sort'];

    protected $casts = ['is_required' => 'boolean', 'price_override' => 'decimal:2', 'old_price_override' => 'decimal:2'];

    public function set()
    {
        return $this->belongsTo(Product::class, 'set_id');
    }

    public function component()
    {
        return $this->belongsTo(Product::class, 'component_id')->withTrashed();
    }

    public function unitPrice(): float
    {
        return (float) ($this->price_override ?? $this->component->price);
    }

    public function unitOldPrice(): ?float
    {
        $old = $this->old_price_override ?? ($this->price_override === null ? $this->component->old_price : null);

        return $old !== null && (float) $old > $this->unitPrice() ? (float) $old : null;
    }
}
