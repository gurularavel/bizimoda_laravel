<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public const STATUSES = [
        'new' => 'Yeni',
        'confirmed' => 'Təsdiqləndi',
        'shipping' => 'Çatdırılır',
        'delivered' => 'Çatdırıldı',
        'cancelled' => 'Ləğv edildi',
    ];

    public const PAYMENT_STATUSES = [
        'pending' => 'Gözləyir',
        'paid' => 'Ödənilib',
        'failed' => 'Uğursuz',
        'refunded' => 'Geri qaytarılıb',
    ];

    public const PAYMENT_METHODS = [
        'cod' => 'Qapıda nağd ödəniş',
        'kapital' => 'Kapital Bank kartı ilə',
    ];

    protected $fillable = ['number', 'user_id', 'locale', 'first_name', 'last_name', 'email', 'phone', 'city', 'address', 'comment',
        'subtotal', 'discount', 'delivery_fee', 'total', 'payment_method', 'payment_status', 'status', 'source', 'ip', 'user_agent'];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function histories()
    {
        return $this->hasMany(OrderHistory::class)->latest();
    }

    public function payments()
    {
        return $this->hasMany(Payment::class)->latest();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getCustomerNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function statusLabel(): string
    {
        return __(self::STATUSES[$this->status] ?? $this->status);
    }

    public function paymentMethodLabel(): string
    {
        return __(self::PAYMENT_METHODS[$this->payment_method] ?? $this->payment_method);
    }
}
