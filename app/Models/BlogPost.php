<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatedSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class BlogPost extends Model
{
    use HasTranslatedSlug, HasTranslations;

    protected string $slugSource = 'title';

    public array $translatable = ['title', 'slug', 'excerpt', 'content', 'meta_title', 'meta_description', 'tags'];

    protected $fillable = ['blog_category_id', 'admin_id', 'title', 'slug', 'excerpt', 'content', 'meta_title', 'meta_description',
        'tags', 'cover', 'is_active', 'published_at'];

    protected $casts = ['is_active' => 'boolean', 'published_at' => 'datetime'];

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function author()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('is_active', true)->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function url(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return route('front.blog.post', ['locale' => $locale, 'slug' => $this->getTranslation('slug', $locale)]);
    }

    public function tagList(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $this->tags))));
    }
}
