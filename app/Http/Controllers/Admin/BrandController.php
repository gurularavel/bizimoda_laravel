<?php

namespace App\Http\Controllers\Admin;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    public function index()
    {
        return view('admin.brands.index', ['brands' => Brand::query()->withCount('products')->orderBy('sort')->orderBy('name')->get()]);
    }

    public function create()
    {
        return view('admin.brands.form', ['brand' => new Brand(['is_active' => true])]);
    }

    public function edit(Brand $brand)
    {
        return view('admin.brands.form', compact('brand'));
    }

    public function store(Request $request)
    {
        $brand = new Brand;
        $this->save($request, $brand);

        return redirect()->route('admin.brands.index')->with('success', 'Brend yaradıldı.');
    }

    public function update(Request $request, Brand $brand)
    {
        $this->save($request, $brand);

        return redirect()->route('admin.brands.index')->with('success', 'Yadda saxlanıldı.');
    }

    protected function save(Request $request, Brand $brand): void
    {
        $request->validate([
            'name' => 'required|string|max:120',
            'slug' => ['nullable', 'string', 'max:120', Rule::unique('brands', 'slug')->ignore($brand->id)],
        ]);
        $brand->fill([
            'name' => $request->input('name'),
            'slug' => Str::slug($request->input('slug') ?: $request->input('name')),
            'logo' => $this->image($request, 'logo', $brand->logo, 'brands'),
            'is_active' => $request->boolean('is_active'),
            'sort' => $request->integer('sort'),
        ])->save();
    }

    public function destroy(Brand $brand)
    {
        $brand->delete();

        return back()->with('success', 'Brend silindi.');
    }
}
