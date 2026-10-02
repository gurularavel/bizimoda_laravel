<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Slide extends Model
{
    use HasTranslations;

    public array $translatable = ['title'];

    protected $fillable = ['slider_id', 'title', 'image', 'image_mobile', 'link', 'is_active', 'sort'];

    protected $casts = ['is_active' => 'boolean'];

    public function slider()
    {
        return $this->belongsTo(Slider::class);
    }
}
