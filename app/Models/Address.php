<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = ['user_id', 'first_name', 'last_name', 'phone', 'city', 'address', 'is_default'];

    protected $casts = ['is_default' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFullAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}, {$this->city}, {$this->address}", ', ');
    }
}
