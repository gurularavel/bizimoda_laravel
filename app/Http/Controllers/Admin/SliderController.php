<?php

namespace App\Http\Controllers\Admin;

use App\Models\Slider;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SliderController extends Controller
{
    public function index()
    {
        return view('admin.sliders.index', ['sliders' => Slider::query()->withCount('slides')->get()]);
    }

    public function create()
    {
        return view('admin.sliders.form', ['slider' => new Slider(['width' => 1590, 'height' => 458])]);
    }

    public function edit(Slider $slider)
    {
        return view('admin.sliders.form', ['slider' => $slider->load('slides')]);
    }

    public function store(Request $request)
    {
        $slider = new Slider;
        $this->save($request, $slider);

        return redirect()->route('admin.sliders.edit', $slider)->with('success', 'Slayder yaradıldı.');
    }

    public function update(Request $request, Slider $slider)
    {
        $this->save($request, $slider);

        return redirect()->route('admin.sliders.edit', $slider)->with('success', 'Yadda saxlanıldı.');
    }

    protected function save(Request $request, Slider $slider): void
    {
        $request->validate([
            'name' => 'required|string|max:120',
            'width' => 'required|integer|min:100',
            'height' => 'required|integer|min:50',
            'new_slides.*' => 'image|max:10240',
        ]);
        $images = app(ImageService::class);

        DB::transaction(function () use ($request, $slider, $images) {
            $slider->fill([
                'name' => $request->input('name'),
                'key' => $slider->key ?: Str::slug($request->input('name')).'-'.Str::lower(Str::random(4)),
                'width' => $request->integer('width'),
                'height' => $request->integer('height'),
            ])->save();

            foreach ((array) $request->input('slides', []) as $id => $row) {
                $slide = $slider->slides()->find($id);
                if (! $slide) {
                    continue;
                }
                if (! empty($row['delete'])) {
                    $images->delete($slide->image);
                    $slide->delete();
                    continue;
                }
                $title = array_filter((array) ($row['title'] ?? []));
                $slide->fill([
                    'title' => $title,
                    'link' => $row['link'] ?? null,
                    'is_active' => ! empty($row['is_active']),
                    'sort' => (int) ($row['sort'] ?? 0),
                ]);
                if ($request->hasFile("slides.{$id}.image")) {
                    $images->delete($slide->image);
                    $slide->image = $images->store($request->file("slides.{$id}.image"), 'slides');
                }
                $slide->save();
            }

            $next = (int) $slider->slides()->max('sort') + 1;
            foreach ((array) $request->file('new_slides', []) as $file) {
                $slider->slides()->create(['image' => $images->store($file, 'slides'), 'sort' => $next++, 'is_active' => true]);
            }
        });
    }

    public function destroy(Slider $slider)
    {
        $slider->delete();

        return redirect()->route('admin.sliders.index')->with('success', 'Slayder silindi.');
    }
}
