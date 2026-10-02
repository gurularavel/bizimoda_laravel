<?php

namespace App\Http\Controllers\Admin;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BlogPostController extends Controller
{
    protected array $translatable = ['title', 'slug', 'excerpt', 'content', 'meta_title', 'meta_description', 'tags'];

    public function index(Request $request)
    {
        $locale = app()->getLocale();
        $posts = BlogPost::query()->with('category')
            ->when($q = $request->query('q'), fn ($w) => $w->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(title, '$.\"{$locale}\"'))) LIKE ?", ['%'.mb_strtolower($q).'%']))
            ->latest('published_at')->latest('id')->paginate(30)->withQueryString();

        return view('admin.blog-posts.index', compact('posts'));
    }

    public function create()
    {
        return $this->form(new BlogPost(['is_active' => true, 'published_at' => now()]));
    }

    public function edit(BlogPost $blogPost)
    {
        return $this->form($blogPost);
    }

    protected function form(BlogPost $post)
    {
        return view('admin.blog-posts.form', [
            'post' => $post,
            'categories' => BlogCategory::query()->orderBy('sort')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $post = new BlogPost(['admin_id' => auth('admin')->id()]);
        $this->save($request, $post);

        return redirect()->route('admin.blog-posts.edit', $post)->with('success', 'Yazı yaradıldı.');
    }

    public function update(Request $request, BlogPost $blogPost)
    {
        $this->save($request, $blogPost);

        return redirect()->route('admin.blog-posts.edit', $blogPost)->with('success', 'Yadda saxlanıldı.');
    }

    protected function save(Request $request, BlogPost $post): void
    {
        $request->validate($this->translationRules($this->translatable, ['title']) + [
            'blog_category_id' => 'nullable|exists:blog_categories,id',
            'published_at' => 'nullable|date',
        ]);

        $post->fill($this->transMany($request, $this->translatable) + [
            'blog_category_id' => $request->integer('blog_category_id') ?: null,
            'cover' => $this->image($request, 'cover', $post->cover, 'blog'),
            'is_active' => $request->boolean('is_active'),
            'published_at' => $request->filled('published_at') ? Carbon::parse($request->input('published_at')) : null,
        ])->save();
    }

    public function destroy(BlogPost $blogPost)
    {
        $blogPost->delete();

        return redirect()->route('admin.blog-posts.index')->with('success', 'Yazı silindi.');
    }
}
