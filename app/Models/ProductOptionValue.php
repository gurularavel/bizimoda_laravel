<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductOptionValue extends Model
{
    public $timestamps = false;

    protected $fillable = ['product_option_id', 'product_id', 'option_value_id', 'price_modifier', 'modifier_type', 'stock_qty', 'is_default', 'sort'];

    protected $casts = ['price_modifier' => 'decimal:2', 'is_default' => 'boolean'];

    public function optionValue()
    {
        return $this->belongsTo(OptionValue::class);
    }

    public function productOption()
    {
        return $this->belongsTo(ProductOption::class);
    }

    /** Verilmiş baza qiymətə tətbiq olunan əlavə məbləğ */
    public function modifierFor(float $base): float
    {
        return $this->modifier_type === 'percent'
            ? round($base * (float) $this->price_modifier / 100, 2)
            : (float) $this->price_modifier;
    }
}
