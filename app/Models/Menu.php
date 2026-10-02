<?php

namespace App\Models;

use App\Services\MenuBuilder;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    public const LOCATIONS = [
        'main' => 'Əsas meqa menyu',
        'top' => 'Yuxarı zolaq (sol)',
        'account' => 'Header: qeydiyyat/giriş linkləri',
        'mobile_top' => 'Mobil yuxarı zolaq',
        'footer_1' => 'Footer — 1-ci sütun',
        'footer_2' => 'Footer — 2-ci sütun',
        'footer_3' => 'Footer — 3-cü sütun',
        'social' => 'Footer — sosial şəbəkələr',
    ];

    protected $fillable = ['key', 'name'];

    protected static function booted(): void
    {
        static::saved(fn () => MenuBuilder::flush());
        static::deleted(fn () => MenuBuilder::flush());
    }

    public function items()
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort');
    }

    public function rootItems()
    {
        return $this->hasMany(MenuItem::class)->whereNull('parent_id')->orderBy('sort');
    }
}
