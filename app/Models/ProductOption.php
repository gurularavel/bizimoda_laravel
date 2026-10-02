<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductOption extends Model
{
    public $timestamps = false;

    protected $fillable = ['product_id', 'option_id', 'is_required', 'sort'];

    protected $casts = ['is_required' => 'boolean'];

    public function option()
    {
        return $this->belongsTo(Option::class);
    }

    public function values()
    {
        return $this->hasMany(ProductOptionValue::class)->orderBy('sort');
    }
}
