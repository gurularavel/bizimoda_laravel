<?php

namespace App\Http\Controllers\Admin;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Product;
use App\Services\ImageService;
use App\Services\MenuBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MenuController extends Controller
{
    public function index()
    {
        // Bütün yerlər üçün menyu mövcud olsun
        foreach (Menu::LOCATIONS as $key => $name) {
            Menu::query()->firstOrCreate(['key' => $key], ['name' => $name]);
        }

        return view('admin.menus.index', ['menus' => Menu::query()->withCount('items')->get()->sortBy(fn ($m) => array_search($m->key, array_keys(Menu::LOCATIONS)))]);
    }

    public function edit(Menu $menu)
    {
        $items = $menu->items()->get();

        return view('admin.menus.edit', [
            'menu' => $menu,
            'tree' => $this->tree($items, null),
            'items' => $items,
            'labels' => $this->linkableLabels($items),
        ]);
    }

    protected function tree($items, ?int $parentId)
    {
        return $items->where('parent_id', $parentId)->sortBy('sort')->values()
            ->map(fn (MenuItem $i) => tap($i, fn ($x) => $x->setRelation('children', $this->tree($items, $i->id))));
    }

    /** Hər elementin hədəf obyektinin adı (siyahıda göstərmək üçün) */
    protected function linkableLabels($items): array
    {
        $labels = [];
        foreach ($items->groupBy('type') as $type => $group) {
            $model = $group->first()->linkableModel();
            if (! $model) {
                continue;
            }
            $model::query()->whereIn('id', $group->pluck('linkable_id')->filter())->get()
                ->each(function ($m) use (&$labels, $type) {
                    $labels[$type][$m->id] = $m->name ?? $m->title;
                });
        }

        return $labels;
    }

    /** Link tipinə görə seçim siyahısı (Select2) */
    public function linkables(Request $request)
    {
        $q = mb_strtolower(trim((string) $request->query('q', '')));
        $locale = app()->getLocale();
        $like = fn ($field) => fn ($w) => $q === '' ? $w : $w->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT({$field}, '$.\"{$locale}\"'))) LIKE ?", ["%{$q}%"]);

        $results = match ($request->query('type')) {
            'category' => collect(CategoryController::options())
                ->filter(fn ($label) => $q === '' || str_contains(mb_strtolower($label), $q))
                ->map(fn ($label, $id) => ['id' => $id, 'text' => $label])->values(),
            'product' => Product::query()->where($like('name'))->limit(30)->get()->map(fn ($p) => ['id' => $p->id, 'text' => $p->name]),
            'page' => Page::query()->where($like('title'))->orderBy('sort')->get()->map(fn ($p) => ['id' => $p->id, 'text' => $p->title]),
            'blog_category' => BlogCategory::query()->where($like('name'))->get()->map(fn ($p) => ['id' => $p->id, 'text' => $p->name]),
            'blog_post' => BlogPost::query()->where($like('title'))->latest()->limit(30)->get()->map(fn ($p) => ['id' => $p->id, 'text' => $p->title]),
            default => collect(),
        };

        return response()->json(['results' => $results]);
    }

    public function storeItem(Request $request, Menu $menu)
    {
        $item = new MenuItem(['menu_id' => $menu->id, 'sort' => (int) $menu->items()->max('sort') + 1]);
        $this->saveItem($request, $menu, $item);

        return redirect()->route('admin.menus.edit', $menu)->with('success', 'Menyu elementi əlavə edildi.');
    }

    public function updateItem(Request $request, Menu $menu, MenuItem $item)
    {
        abort_unless($item->menu_id === $menu->id, 404);
        $this->saveItem($request, $menu, $item);

        return redirect()->route('admin.menus.edit', $menu)->with('success', 'Yadda saxlanıldı.');
    }

    protected function saveItem(Request $request, Menu $menu, MenuItem $item): void
    {
        $request->validate([
            'type' => ['required', Rule::in(array_keys(MenuItem::TYPES))],
            'display' => ['required', Rule::in(array_keys(MenuItem::DISPLAYS))],
            'linkable_id' => 'nullable|integer',
            'url' => 'nullable|string|max:500',
            'parent_id' => 'nullable|integer',
            'column' => 'nullable|integer|min:1|max:8',
            'title' => 'array',
        ]);

        $type = $request->input('type');
        if (in_array($type, ['category', 'product', 'page', 'blog_category', 'blog_post'], true) && ! $request->integer('linkable_id')) {
            back()->withErrors(['linkable_id' => 'Link tipi üçün obyekt seçin.'])->throwResponse();
        }
        if ($type === 'url' && ! $request->filled('url')) {
            back()->withErrors(['url' => 'Xarici link üçün URL daxil edin.'])->throwResponse();
        }
        if (in_array($type, ['none', 'route', 'url', 'blog'], true) && ! array_filter((array) $request->input('title', []))) {
            back()->withErrors(['title' => 'Bu link tipi üçün başlıq daxil edin.'])->throwResponse();
        }

        $parentId = $request->integer('parent_id') ?: null;
        if ($parentId && (! MenuItem::query()->where('menu_id', $menu->id)->whereKey($parentId)->exists() || $parentId === $item->id)) {
            $parentId = null;
        }

        $item->fill([
            'parent_id' => $parentId,
            'title' => $this->trans($request, 'title'),
            'type' => $type,
            'linkable_id' => in_array($type, ['category', 'product', 'page', 'blog_category', 'blog_post'], true) ? $request->integer('linkable_id') : null,
            'url' => in_array($type, ['route', 'url'], true) ? $request->input('url') : null,
            'target_blank' => $request->boolean('target_blank'),
            'display' => $request->input('display'),
            'column' => max(1, $request->integer('column', 1)),
            'auto_children' => $request->boolean('auto_children'),
            'css_class' => $request->input('css_class'),
            'is_active' => $request->boolean('is_active'),
            'banner_url' => $request->input('banner_url'),
        ]);
        $item->banner_image = $this->image($request, 'banner_image', $item->banner_image, 'menu');
        $item->save();
    }

    public function destroyItem(Menu $menu, MenuItem $item)
    {
        abort_unless($item->menu_id === $menu->id, 404);
        app(ImageService::class)->delete($item->banner_image);
        $item->delete();

        return redirect()->route('admin.menus.edit', $menu)->with('success', 'Element silindi (alt elementləri ilə birlikdə).');
    }

    /** Drag-drop: [{id, children: [...]}, ...] */
    public function reorder(Request $request, Menu $menu)
    {
        $tree = json_decode((string) $request->input('tree'), true);
        abort_unless(is_array($tree), 422);

        DB::transaction(function () use ($tree, $menu) {
            $walk = function (array $nodes, ?int $parentId) use (&$walk, $menu) {
                foreach (array_values($nodes) as $i => $node) {
                    MenuItem::query()->where('menu_id', $menu->id)->whereKey($node['id'])->update(['parent_id' => $parentId, 'sort' => $i]);
                    $walk($node['children'] ?? [], (int) $node['id']);
                }
            };
            $walk($tree, null);
        });
        MenuBuilder::flush();

        return response()->json(['ok' => true]);
    }
}
