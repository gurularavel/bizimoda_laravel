<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['order_id', 'provider', 'provider_order_id', 'provider_password', 'amount', 'currency', 'status', 'request', 'response'];

    protected $casts = ['request' => 'array', 'response' => 'array', 'amount' => 'decimal:2', 'provider_password' => 'encrypted'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
