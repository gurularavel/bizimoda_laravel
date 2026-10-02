<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatedSlug;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class BlogCategory extends Model
{
    use HasTranslatedSlug, HasTranslations;

    public array $translatable = ['name', 'slug', 'description', 'meta_title', 'meta_description'];

    protected $fillable = ['name', 'slug', 'description', 'meta_title', 'meta_description', 'is_active', 'sort'];

    protected $casts = ['is_active' => 'boolean'];

    public function posts()
    {
        return $this->hasMany(BlogPost::class);
    }

    public function url(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return route('front.blog.category', ['locale' => $locale, 'slug' => $this->getTranslation('slug', $locale)]);
    }
}
