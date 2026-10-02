<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Support\Locales;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    protected function sidebar(): array
    {
        return [
            'blogCategories' => BlogCategory::query()->where('is_active', true)->orderBy('sort')->get(),
            'latestPosts' => BlogPost::query()->published()->latest('published_at')->limit(5)->get(),
        ];
    }

    public function index(Request $request)
    {
        $query = BlogPost::query()->published()->with('category')->latest('published_at');
        if ($tag = $request->query('tag')) {
            $query->where('tags->'.app()->getLocale(), 'like', '%'.$tag.'%');
        }

        return view('front.blog.index', $this->sidebar() + [
            'posts' => $query->paginate(12)->withQueryString(),
            'heading' => setting_t('blog.title') ?: __('Bloq'),
            'category' => null,
            'htmlClass' => 'route-journal3-blog layout-15 one-column column-right',
        ]);
    }

    public function category(string $slug)
    {
        $category = BlogCategory::query()->where('is_active', true)->whereSlug($slug)->firstOrFail();
        $alternates = [];
        foreach (Locales::codes() as $code) {
            $alternates[$code] = $category->url($code);
        }

        return view('front.blog.index', $this->sidebar() + [
            'posts' => $category->posts()->published()->latest('published_at')->paginate(12),
            'heading' => $category->name,
            'category' => $category,
            'alternates' => $alternates,
            'htmlClass' => 'route-journal3-blog layout-15 one-column column-right',
        ]);
    }

    public function show(string $slug)
    {
        $post = BlogPost::query()->published()->whereSlug($slug)->with('category', 'author')->firstOrFail();
        BlogPost::query()->whereKey($post->id)->increment('views');

        $alternates = [];
        foreach (Locales::codes() as $code) {
            $alternates[$code] = $post->url($code);
        }

        return view('front.blog.show', $this->sidebar() + [
            'post' => $post,
            'related' => BlogPost::query()->published()->whereKeyNot($post->id)
                ->when($post->blog_category_id, fn ($q) => $q->where('blog_category_id', $post->blog_category_id))
                ->latest('published_at')->limit(3)->get(),
            'alternates' => $alternates,
            'htmlClass' => 'route-journal3-blog route-journal3-blog-post layout-16 one-column column-right',
        ]);
    }
}
