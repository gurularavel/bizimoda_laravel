<?php

namespace App\Models;

use App\Support\Locales;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = ['code', 'name', 'is_active', 'is_default', 'sort'];

    protected $casts = ['is_active' => 'boolean', 'is_default' => 'boolean'];

    protected static function booted(): void
    {
        static::saved(fn () => Locales::flush());
        static::deleted(fn () => Locales::flush());
    }
}
