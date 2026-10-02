<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryFilter extends Model
{
    public $timestamps = false;

    public const TYPES = [
        'price' => 'Qiymət aralığı',
        'subcategory' => 'Altbaşlıqlar',
        'stock' => 'Mövcudluğu',
        'brand' => 'Brend',
        'attribute' => 'Xüsusiyyət',
        'option' => 'Opsiyon (rəng, ölçü...)',
    ];

    public const DEFAULTS = ['price', 'subcategory', 'stock'];

    protected $fillable = ['category_id', 'type', 'ref_id', 'sort'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function attribute()
    {
        return $this->belongsTo(Attribute::class, 'ref_id');
    }

    public function option()
    {
        return $this->belongsTo(Option::class, 'ref_id');
    }
}
