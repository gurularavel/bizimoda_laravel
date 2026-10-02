<?php

namespace App\Http\Controllers\Admin;

use App\Models\BlogCategory;
use Illuminate\Http\Request;

class BlogCategoryController extends Controller
{
    protected array $translatable = ['name', 'slug', 'description', 'meta_title', 'meta_description'];

    public function index()
    {
        return view('admin.blog-categories.index', ['categories' => BlogCategory::query()->withCount('posts')->orderBy('sort')->get()]);
    }

    public function create()
    {
        return view('admin.blog-categories.form', ['category' => new BlogCategory(['is_active' => true])]);
    }

    public function edit(BlogCategory $blogCategory)
    {
        return view('admin.blog-categories.form', ['category' => $blogCategory]);
    }

    public function store(Request $request)
    {
        $category = new BlogCategory;
        $this->save($request, $category);

        return redirect()->route('admin.blog-categories.index')->with('success', 'Kateqoriya yaradıldı.');
    }

    public function update(Request $request, BlogCategory $blogCategory)
    {
        $this->save($request, $blogCategory);

        return redirect()->route('admin.blog-categories.index')->with('success', 'Yadda saxlanıldı.');
    }

    protected function save(Request $request, BlogCategory $category): void
    {
        $request->validate($this->translationRules($this->translatable, ['name']));
        $category->fill($this->transMany($request, $this->translatable) + [
            'is_active' => $request->boolean('is_active'),
            'sort' => $request->integer('sort'),
        ])->save();
    }

    public function destroy(BlogCategory $blogCategory)
    {
        $blogCategory->delete();

        return back()->with('success', 'Silindi.');
    }
}
