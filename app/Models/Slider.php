<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $fillable = ['key', 'name', 'width', 'height'];

    public function slides()
    {
        return $this->hasMany(Slide::class)->orderBy('sort');
    }

    public function activeSlides()
    {
        return $this->hasMany(Slide::class)->where('is_active', true)->orderBy('sort');
    }
}
