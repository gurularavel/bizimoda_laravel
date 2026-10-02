<?php

namespace App\Http\Controllers\Admin;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\CategoryFilter;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    protected array $translatable = ['name', 'slug', 'description', 'meta_title', 'meta_description'];

    public static function options(?Category $except = null): array
    {
        $exclude = $except ? Category::descendantsAndSelf($except->id)->pluck('id')->all() : [];

        return Category::query()->withDepth()->defaultOrder()->get()
            ->reject(fn ($c) => in_array($c->id, $exclude, true))
            ->mapWithKeys(fn ($c) => [$c->id => str_repeat('— ', $c->depth).$c->name])->all();
    }

    public function index()
    {
        $tree = Category::query()->withCount(['products', 'mainProducts'])->defaultOrder()->get()->toTree();

        return view('admin.categories.index', compact('tree'));
    }

    public function tree()
    {
        return response()->json(self::options());
    }

    /** Drag-drop: [{id, children: [...]}, ...] */
    public function reorder(Request $request)
    {
        $tree = json_decode((string) $request->input('tree'), true);
        abort_unless(is_array($tree), 422);

        DB::transaction(function () use ($tree) {
            $walk = function (array $nodes, ?int $parentId) use (&$walk) {
                foreach (array_values($nodes) as $i => $node) {
                    Category::query()->whereKey($node['id'])->update(['parent_id' => $parentId, 'sort' => $i]);
                    $walk($node['children'] ?? [], (int) $node['id']);
                }
            };
            $walk($tree, null);
            $this->rebuildOrder();
        });

        return response()->json(['ok' => true]);
    }

    public function create(Request $request)
    {
        return $this->form(new Category(['is_active' => true, 'show_in_filter' => true, 'filter_inherit' => true, 'parent_id' => $request->integer('parent_id') ?: null]));
    }

    public function edit(Category $category)
    {
        return $this->form($category->load('filters'));
    }

    protected function form(Category $category)
    {
        return view('admin.categories.form', [
            'category' => $category,
            'parents' => self::options($category->exists ? $category : null),
            'attributes' => Attribute::query()->orderBy('sort')->get(),
            'options' => Option::query()->orderBy('sort')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $category = new Category;
        $this->save($request, $category);

        return redirect()->route('admin.categories.edit', $category)->with('success', 'Kateqoriya yaradıldı.');
    }

    public function update(Request $request, Category $category)
    {
        $this->save($request, $category);

        return redirect()->route('admin.categories.edit', $category)->with('success', 'Yadda saxlanıldı.');
    }

    protected function save(Request $request, Category $category): void
    {
        $request->validate($this->translationRules($this->translatable, ['name']) + [
            'parent_id' => ['nullable', Rule::exists('categories', 'id')],
            'sort' => 'nullable|integer',
            'filters' => 'array',
            'filters.*.type' => ['nullable', Rule::in(array_keys(CategoryFilter::TYPES))],
            'filters.*.ref_id' => 'nullable|integer',
        ]);

        $parentId = $request->integer('parent_id') ?: null;
        if ($category->exists && $parentId && Category::descendantsAndSelf($category->id)->pluck('id')->contains($parentId)) {
            $parentId = $category->parent_id; // özünün alt kateqoriyasına köçürülə bilməz
        }

        DB::transaction(function () use ($request, $category, $parentId) {
            $category->fill($this->transMany($request, $this->translatable) + [
                'image' => $this->image($request, 'image', $category->image, 'categories'),
                'banner' => $this->image($request, 'banner', $category->banner, 'categories'),
                'is_active' => $request->boolean('is_active'),
                'show_in_filter' => $request->boolean('show_in_filter'),
                'filter_inherit' => $request->boolean('filter_inherit'),
                'sort' => $request->integer('sort'),
            ]);
            $category->parent_id = $parentId;
            $category->save();

            // Filtr ayarları (hansı filtrlər bu kateqoriyada görünsün)
            $category->filters()->delete();
            $seen = [];
            foreach (array_values((array) $request->input('filters', [])) as $i => $row) {
                $type = $row['type'] ?? null;
                if (! $type) {
                    continue;
                }
                $ref = in_array($type, ['attribute', 'option'], true) ? ((int) ($row['ref_id'] ?? 0) ?: null) : null;
                if (in_array($type, ['attribute', 'option'], true) && ! $ref) {
                    continue;
                }
                $key = $type.':'.$ref;
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;
                $category->filters()->create(['type' => $type, 'ref_id' => $ref, 'sort' => $i]);
            }

            $this->rebuildOrder();
        });
    }

    public function destroy(Category $category)
    {
        if ($category->children()->exists()) {
            return back()->with('error', 'Əvvəlcə alt kateqoriyaları silin və ya köçürün.');
        }
        if ($category->mainProducts()->exists()) {
            return back()->with('error', 'Bu kateqoriya bəzi məhsulların əsas kateqoriyasıdır. Əvvəlcə həmin məhsulların əsas kateqoriyasını dəyişin.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Kateqoriya silindi.');
    }

    /** parent_id + sort əsasında nested set (_lft/_rgt) dəyərlərini yeniləyir */
    protected function rebuildOrder(): void
    {
        $rows = Category::query()->orderBy('sort')->orderBy('id')->get(['id', 'parent_id']);
        $children = $rows->groupBy(fn ($r) => (int) $r->parent_id);
        $counter = 1;
        $updates = [];

        $walk = function (int $parentId) use (&$walk, $children, &$counter, &$updates) {
            foreach ($children[$parentId] ?? [] as $row) {
                $left = $counter++;
                $walk($row->id);
                $updates[$row->id] = [$left, $counter++];
            }
        };
        $walk(0);

        foreach ($updates as $id => [$lft, $rgt]) {
            DB::table('categories')->where('id', $id)->update(['_lft' => $lft, '_rgt' => $rgt]);
        }
    }
}
