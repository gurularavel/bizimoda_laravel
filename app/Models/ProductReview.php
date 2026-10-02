<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductReview extends Model
{
    protected $fillable = ['product_id', 'user_id', 'author', 'rating', 'text', 'is_approved'];

    protected $casts = ['is_approved' => 'boolean'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
