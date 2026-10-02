<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'product_id', 'type', 'name', 'sku', 'quantity', 'unit_price', 'unit_old_price', 'unit_discount', 'discount_name', 'total', 'options'];

    protected $casts = ['options' => 'array', 'unit_price' => 'decimal:2', 'unit_old_price' => 'decimal:2', 'unit_discount' => 'decimal:2', 'total' => 'decimal:2'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function components()
    {
        return $this->hasMany(OrderItemComponent::class);
    }
}
