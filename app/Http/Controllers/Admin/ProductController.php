<?php

namespace App\Http\Controllers\Admin;

use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Option;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductOption;
use App\Services\ImageService;
use App\Services\SetPricingService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    protected array $translatable = ['name', 'slug', 'short_description', 'description', 'dimensions', 'meta_title', 'meta_description', 'label'];

    public function __construct(protected SetPricingService $pricing, protected ImageService $images) {}

    public function index(Request $request)
    {
        $locale = app()->getLocale();
        $query = Product::query()->with(['images', 'mainCategory'])->withCount('setItems');

        if ($q = trim((string) $request->query('q'))) {
            $query->where(fn (Builder $w) => $w->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"{$locale}\"'))) LIKE ?", ['%'.mb_strtolower($q).'%'])
                ->orWhere('sku', 'like', "%{$q}%")->orWhere('id', (int) $q));
        }
        if ($categoryId = $request->integer('category')) {
            $ids = Category::descendantsAndSelf($categoryId)->pluck('id');
            $query->whereHas('categories', fn ($c) => $c->whereIn('categories.id', $ids));
        }
        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->query('status') === '1');
        }
        if ($request->query('stock') === 'out') {
            $query->where('stock_status', 'out_of_stock');
        }

        $sort = $request->query('sort', 'latest');
        match ($sort) {
            'name' => $query->orderByRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"{$locale}\"'))"),
            'price' => $query->orderBy('computed_price'),
            'price_desc' => $query->orderByDesc('computed_price'),
            default => $query->latest('id'),
        };

        return view('admin.products.index', [
            'products' => $query->paginate(30)->withQueryString(),
            'categories' => CategoryController::options(),
        ]);
    }

    /** Select2 AJAX axtarışı (dəst modulları, oxşar məhsullar, menyu) */
    public function search(Request $request)
    {
        $q = mb_strtolower(trim((string) $request->query('q')));
        $locale = app()->getLocale();

        $products = Product::query()
            ->when($request->query('type') === 'component', fn ($w) => $w->where('type', '!=', 'set'))
            ->where(fn ($w) => $w->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"{$locale}\"'))) LIKE ?", ["%{$q}%"])->orWhere('sku', 'like', "%{$q}%"))
            ->orderBy('type')->limit(30)->get();

        return response()->json($products->map(fn (Product $p) => [
            'id' => $p->id,
            'text' => $p->name.($p->sku ? " ({$p->sku})" : '').' — '.money($p->price).' ['.(Product::TYPES[$p->type] ?? $p->type).']',
        ]));
    }

    public function create()
    {
        return $this->form(new Product([
            'type' => request('type', 'simple'), 'is_active' => true, 'sold_separately' => true,
            'stock_status' => 'in_stock', 'min_qty' => 1,
        ]));
    }

    public function edit(Product $product)
    {
        return $this->form($product->load(['images', 'categories', 'productOptions.values', 'setItems.component', 'attributeValues', 'related']));
    }

    protected function form(Product $product)
    {
        return view('admin.products.form', [
            'product' => $product,
            'categories' => CategoryController::options(),
            'brands' => Brand::query()->orderBy('name')->pluck('name', 'id'),
            'options' => Option::query()->with('values')->orderBy('sort')->get(),
            'attributes' => Attribute::query()->with(['values', 'group'])->orderBy('sort')->get(),
            'partOfSets' => $product->exists ? $product->partOfSets()->with('set')->get() : collect(),
        ]);
    }

    public function store(Request $request)
    {
        $product = new Product;
        $this->save($request, $product);

        return redirect()->route('admin.products.edit', $product)->with('success', 'Məhsul yaradıldı.');
    }

    public function update(Request $request, Product $product)
    {
        $this->save($request, $product);

        return redirect()->to(route('admin.products.edit', $product).($request->input('_tab') ? '#'.$request->input('_tab') : ''))
            ->with('success', 'Yadda saxlanıldı.');
    }

    protected function save(Request $request, Product $product): void
    {
        $request->validate($this->translationRules($this->translatable, ['name']) + [
            'type' => ['required', Rule::in(array_keys(Product::TYPES))],
            'sku' => 'nullable|string|max:64',
            'main_category_id' => 'required|exists:categories,id',
            'categories' => 'array',
            'categories.*' => 'integer|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'price' => 'nullable|numeric|min:0',
            'old_price' => 'nullable|numeric|min:0',
            'stock_qty' => 'nullable|integer',
            'stock_status' => ['required', Rule::in(array_keys(Product::STOCK_STATUSES))],
            'min_qty' => 'nullable|integer|min:1',
            'sort' => 'nullable|integer',
            'set_items' => 'array',
            'set_items.*.component_id' => 'nullable|integer|exists:products,id',
            'set_items.*.default_qty' => 'nullable|integer|min:0',
            'set_items.*.min_qty' => 'nullable|integer|min:1',
            'set_items.*.max_qty' => 'nullable|integer|min:1',
            'set_items.*.price_override' => 'nullable|numeric|min:0',
            'set_items.*.old_price_override' => 'nullable|numeric|min:0',
            'new_images.*' => 'image|max:8192',
        ], [
            'main_category_id.required' => 'Əsas kateqoriya mütləq seçilməlidir.',
        ]);

        $this->validateSetItems($request);

        DB::transaction(function () use ($request, $product) {
            $isSet = $request->input('type') === 'set';

            $product->fill($this->transMany($request, $this->translatable) + [
                'type' => $request->input('type'),
                'sku' => $request->input('sku'),
                'main_category_id' => $request->integer('main_category_id'),
                'brand_id' => $request->integer('brand_id') ?: null,
                'price' => $isSet ? 0 : (float) $request->input('price', 0),
                'old_price' => $isSet ? null : ($request->filled('old_price') ? (float) $request->input('old_price') : null),
                'stock_qty' => $request->integer('stock_qty'),
                'stock_status' => $request->input('stock_status'),
                'min_qty' => max(1, $request->integer('min_qty', 1)),
                'is_active' => $request->boolean('is_active'),
                'is_featured' => $request->boolean('is_featured'),
                'sold_separately' => $request->input('type') === 'module' ? $request->boolean('sold_separately') : true,
                'sort' => $request->integer('sort'),
            ]);
            $product->save();

            // Kateqoriyalar: əsas kateqoriya həmişə daxildir
            $categoryIds = collect($request->input('categories', []))->map(fn ($id) => (int) $id)
                ->push($product->main_category_id)->unique()->values()->all();
            $product->categories()->sync($categoryIds);

            $this->saveImages($request, $product);
            $this->saveOptions($request, $product);
            $product->attributeValues()->sync(collect($request->input('attributes', []))->flatten()->filter()->map(fn ($v) => (int) $v)->all());
            $product->related()->sync(collect($request->input('related', []))->map(fn ($v) => (int) $v)->reject(fn ($v) => $v === $product->id)->all());

            if ($isSet) {
                $this->saveSetItems($request, $product);
            } else {
                $product->setItems()->delete();
            }

            $this->pricing->refreshComputed($product->fresh());
        });
    }

    /** Dəst modulları üçün məntiqi yoxlamalar */
    protected function validateSetItems(Request $request): void
    {
        if ($request->input('type') !== 'set') {
            return;
        }

        $rows = collect($request->input('set_items', []))->filter(fn ($r) => ! empty($r['component_id']));
        $errors = [];

        if ($rows->isEmpty()) {
            $errors['set_items'] = 'Dəstə ən azı bir modul əlavə edin.';
        }
        if ($rows->pluck('component_id')->duplicates()->isNotEmpty()) {
            $errors['set_items'] = 'Eyni modul dəstə iki dəfə əlavə edilib.';
        }
        foreach ($rows as $i => $row) {
            $min = max(1, (int) ($row['min_qty'] ?? 1));
            $max = $row['max_qty'] ?? null;
            $default = (int) ($row['default_qty'] ?? $min);
            if ($max !== null && $max !== '' && (int) $max < $min) {
                $errors["set_items.{$i}.max_qty"] = 'Maksimum say minimum saydan az ola bilməz.';
            }
            if ($default > 0 && $default < $min) {
                $errors["set_items.{$i}.default_qty"] = "Standart say ({$default}) minimum saydan ({$min}) az ola bilməz (0 — standartda seçilməyib).";
            }
            if (! empty($row['is_required']) && $default === 0) {
                $errors["set_items.{$i}.default_qty"] = 'Məcburi modulun standart sayı 0 ola bilməz.';
            }
            if ((int) $row['component_id'] && Product::query()->whereKey($row['component_id'])->where('type', 'set')->exists()) {
                $errors["set_items.{$i}.component_id"] = 'Dəstin içinə başqa dəst əlavə etmək olmaz.';
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }

    protected function saveSetItems(Request $request, Product $product): void
    {
        $product->setItems()->delete();
        foreach (array_values($request->input('set_items', [])) as $i => $row) {
            if (empty($row['component_id'])) {
                continue;
            }
            $min = max(1, (int) ($row['min_qty'] ?? 1));
            $product->setItems()->create([
                'component_id' => (int) $row['component_id'],
                'default_qty' => (int) ($row['default_qty'] ?? $min),
                'min_qty' => $min,
                'max_qty' => isset($row['max_qty']) && $row['max_qty'] !== '' ? (int) $row['max_qty'] : null,
                'is_required' => ! empty($row['is_required']),
                'price_override' => isset($row['price_override']) && $row['price_override'] !== '' ? (float) $row['price_override'] : null,
                'old_price_override' => isset($row['old_price_override']) && $row['old_price_override'] !== '' ? (float) $row['old_price_override'] : null,
                'sort' => $i,
            ]);
        }
    }

    protected function saveImages(Request $request, Product $product): void
    {
        foreach ((array) $request->input('images', []) as $id => $data) {
            $image = $product->images()->find($id);
            if (! $image) {
                continue;
            }
            if (! empty($data['delete'])) {
                $this->images->delete($image->path);
                $image->delete();
                continue;
            }
            $image->update([
                'sort' => (int) ($data['sort'] ?? 0),
                'option_value_id' => ! empty($data['option_value_id']) ? (int) $data['option_value_id'] : null,
            ]);
        }

        $next = (int) $product->images()->max('sort') + 1;
        foreach ((array) $request->file('new_images', []) as $file) {
            $product->images()->create(['path' => $this->images->store($file, 'products'), 'sort' => $next++]);
        }
    }

    protected function saveOptions(Request $request, Product $product): void
    {
        $keep = [];
        foreach ((array) $request->input('options', []) as $optionId => $data) {
            if (empty($data['enabled'])) {
                continue;
            }
            $productOption = ProductOption::query()->updateOrCreate(
                ['product_id' => $product->id, 'option_id' => (int) $optionId],
                ['is_required' => ! empty($data['required']), 'sort' => (int) ($data['sort'] ?? 0)]
            );
            $keep[] = $productOption->id;

            $productOption->values()->delete();
            $defaultValue = $data['default'] ?? null;
            foreach ((array) ($data['values'] ?? []) as $valueId => $v) {
                if (empty($v['enabled'])) {
                    continue;
                }
                $productOption->values()->create([
                    'product_id' => $product->id,
                    'option_value_id' => (int) $valueId,
                    'price_modifier' => (float) ($v['price_modifier'] ?? 0),
                    'modifier_type' => ($v['modifier_type'] ?? 'fixed') === 'percent' ? 'percent' : 'fixed',
                    'stock_qty' => isset($v['stock_qty']) && $v['stock_qty'] !== '' ? (int) $v['stock_qty'] : null,
                    'is_default' => (string) $defaultValue === (string) $valueId,
                    'sort' => (int) ($v['sort'] ?? 0),
                ]);
            }
        }
        $product->productOptions()->whereNotIn('id', $keep)->delete();
    }

    public function duplicate(Product $product)
    {
        $product->load(['images', 'categories', 'productOptions.values', 'setItems', 'attributeValues']);

        $copy = DB::transaction(function () use ($product) {
            $copy = $product->replicate(['views', 'computed_price', 'computed_old_price']);
            $copy->setTranslations('name', collect($product->getTranslations('name'))->map(fn ($n) => $n.' (kopya)')->all());
            $copy->setTranslations('slug', []);
            $copy->is_active = false;
            $copy->sku = $product->sku ? $product->sku.'-COPY' : null;
            $copy->save();
            $copy->categories()->sync($product->categories->pluck('id'));
            $copy->attributeValues()->sync($product->attributeValues->pluck('id'));
            foreach ($product->images as $img) {
                $copy->images()->create(['path' => $img->path, 'sort' => $img->sort, 'option_value_id' => $img->option_value_id]);
            }
            foreach ($product->setItems as $item) {
                $copy->setItems()->create($item->only(['component_id', 'default_qty', 'min_qty', 'max_qty', 'is_required', 'price_override', 'old_price_override', 'sort']));
            }
            foreach ($product->productOptions as $po) {
                $newPo = $copy->productOptions()->create($po->only(['option_id', 'is_required', 'sort']));
                foreach ($po->values as $v) {
                    $newPo->values()->create(['product_id' => $copy->id] + $v->only(['option_value_id', 'price_modifier', 'modifier_type', 'stock_qty', 'is_default', 'sort']));
                }
            }
            $this->pricing->refreshComputed($copy);

            return $copy;
        });

        return redirect()->route('admin.products.edit', $copy)->with('success', 'Məhsulun surəti yaradıldı (deaktiv vəziyyətdə).');
    }

    public function bulk(Request $request)
    {
        $ids = array_map('intval', (array) $request->input('ids', []));
        $action = $request->input('action');
        if (! $ids) {
            return back()->with('error', 'Heç bir məhsul seçilməyib.');
        }

        match ($action) {
            'activate' => Product::query()->whereIn('id', $ids)->update(['is_active' => true]),
            'deactivate' => Product::query()->whereIn('id', $ids)->update(['is_active' => false]),
            'delete' => Product::query()->whereIn('id', $ids)->get()->each->delete(),
            default => null,
        };

        return back()->with('success', 'Əməliyyat yerinə yetirildi.');
    }

    public function destroy(Product $product)
    {
        $sets = $product->partOfSets()->with('set')->get();
        if ($sets->isNotEmpty()) {
            return back()->with('error', 'Bu məhsul dəstlərdə modul kimi istifadə olunur: '.$sets->map(fn ($i) => $i->set?->name)->filter()->implode(', ').'. Əvvəlcə dəstlərdən çıxarın.');
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Məhsul silindi.');
    }
}
