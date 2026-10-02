<?php

namespace App\Http\Controllers\Admin;

use App\Models\HomeSection;
use App\Models\Product;
use App\Models\Slider;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Ana səhifənin bölmələri: slayder, məhsul karuselləri, bannerlər, HTML, bloq */
class HomeSectionController extends Controller
{
    public const MODULE_CLASSES = [
        'module-products-310' => 'Stil 1 (Xüsusi təkliflər)',
        'module-products-27' => 'Stil 2 (Həftənin təklifi)',
        'module-products-287' => 'Stil 3 (Aktual çeşidlər)',
        'module-products-321' => 'Stil 4 (Ən çox baxılanlar)',
    ];

    public function index()
    {
        return view('admin.home-sections.index', ['sections' => HomeSection::query()->orderBy('sort')->get()]);
    }

    public function create(Request $request)
    {
        return $this->form(new HomeSection(['type' => $request->query('type', 'products'), 'is_active' => true, 'data' => [], 'module_class' => 'module-products-310']));
    }

    public function edit(HomeSection $homeSection)
    {
        return $this->form($homeSection);
    }

    protected function form(HomeSection $section)
    {
        return view('admin.home-sections.form', [
            'section' => $section,
            'sliders' => Slider::query()->pluck('name', 'id'),
            'categories' => CategoryController::options(),
            'selectedProducts' => Product::query()->whereIn('id', $section->data['ids'] ?? [])->get(),
        ]);
    }

    public function store(Request $request)
    {
        $section = new HomeSection(['sort' => (int) HomeSection::query()->max('sort') + 1]);
        $this->save($request, $section);

        return redirect()->route('admin.home-sections.index')->with('success', 'Bölmə əlavə edildi.');
    }

    public function update(Request $request, HomeSection $homeSection)
    {
        $this->save($request, $homeSection);

        return redirect()->route('admin.home-sections.index')->with('success', 'Yadda saxlanıldı.');
    }

    protected function save(Request $request, HomeSection $section): void
    {
        $request->validate([
            'type' => ['required', Rule::in(array_keys(HomeSection::TYPES))],
            'module_class' => ['nullable', Rule::in(array_keys(self::MODULE_CLASSES))],
        ]);
        $type = $request->input('type');
        $data = (array) $request->input('data', []);

        if ($type === 'products') {
            $data['ids'] = array_values(array_map('intval', (array) ($data['ids'] ?? [])));
            $data['limit'] = max(1, (int) ($data['limit'] ?? 12));
        }
        if ($type === 'banners') {
            $images = app(ImageService::class);
            $items = [];
            foreach ((array) ($data['items'] ?? []) as $j => $item) {
                if ($request->hasFile("item_files.{$j}")) {
                    $item['image'] = $images->store($request->file("item_files.{$j}"), 'banners');
                }
                if (! empty($item['image']) && empty($item['remove'])) {
                    $items[] = ['image' => $item['image'], 'link' => $item['link'] ?? null];
                }
            }
            foreach ((array) $request->file('new_files', []) as $file) {
                $items[] = ['image' => $images->store($file, 'banners'), 'link' => null];
            }
            $data['items'] = $items;
        }

        $section->fill([
            'type' => $type,
            'title' => $this->trans($request, 'title'),
            'data' => $data,
            'module_class' => $request->input('module_class'),
            'is_active' => $request->boolean('is_active'),
        ])->save();
    }

    public function reorder(Request $request)
    {
        foreach ((array) $request->input('ids', []) as $i => $id) {
            HomeSection::query()->whereKey($id)->update(['sort' => $i]);
        }

        return response()->json(['ok' => true]);
    }

    public function destroy(HomeSection $homeSection)
    {
        $homeSection->delete();

        return back()->with('success', 'Bölmə silindi.');
    }
}
