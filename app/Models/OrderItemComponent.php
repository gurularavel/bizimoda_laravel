<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemComponent extends Model
{
    public $timestamps = false;

    protected $fillable = ['order_item_id', 'product_id', 'name', 'quantity', 'unit_price', 'total'];

    protected $casts = ['unit_price' => 'decimal:2', 'total' => 'decimal:2'];
}
