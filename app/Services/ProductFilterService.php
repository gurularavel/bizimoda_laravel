<?php

namespace App\Services;

use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CategoryFilter;
use App\Models\Option;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Journal3 filter.js parametrləri:
 *   fc=12            alt kateqoriya
 *   fq=1             stokda var
 *   fm=1,2           brend
 *   fa{attrId}=3,4   xüsusiyyət dəyərləri
 *   fo{optId}=5,6    opsiyon dəyərləri (rəng və s.)
 *   fmin/fmax        qiymət aralığı
 */
class ProductFilterService
{
    public const SORTS = [
        'default' => ['sort', 'asc', 'Əsas'],
        'name_asc' => ['name', 'asc', 'Ad (A - Z)'],
        'name_desc' => ['name', 'desc', 'Ad (Z - A)'],
        'price_asc' => ['computed_price', 'asc', 'Qiymət (Aşağıdan > Yuxarıya)'],
        'price_desc' => ['computed_price', 'desc', 'Qiymət (Yuxarıdan > Aşağıya)'],
        'newest' => ['created_at', 'desc', 'Ən yenilər'],
        'popular' => ['views', 'desc', 'Ən çox baxılanlar'],
    ];

    public const LIMITS = [12, 25, 50, 75, 100];

    protected function csv(Request $request, string $key): array
    {
        $raw = $request->query($key);
        if ($raw === null || $raw === '') {
            return [];
        }

        return array_values(array_filter(array_map('intval', explode(',', (string) $raw))));
    }

    /** Filtr parametrlərini sorğuya tətbiq edir */
    public function apply(Builder $query, Request $request, Collection $filters): Builder
    {
        foreach ($filters as $filter) {
            switch ($filter->type) {
                case 'price':
                    if (is_numeric($request->query('fmin'))) {
                        $query->where('computed_price', '>=', (float) $request->query('fmin'));
                    }
                    if (is_numeric($request->query('fmax'))) {
                        $query->where('computed_price', '<=', (float) $request->query('fmax'));
                    }
                    break;
                case 'subcategory':
                    if ($ids = $this->csv($request, 'fc')) {
                        $categoryIds = Category::query()->whereIn('id', $ids)->get()
                            ->flatMap(fn (Category $c) => Category::descendantsAndSelf($c->id)->pluck('id'))->unique()->all();
                        $query->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $categoryIds));
                    }
                    break;
                case 'stock':
                    if ($request->query('fq')) {
                        $query->where('stock_status', '!=', 'out_of_stock');
                    }
                    break;
                case 'brand':
                    if ($ids = $this->csv($request, 'fm')) {
                        $query->whereIn('brand_id', $ids);
                    }
                    break;
                case 'attribute':
                    if ($ids = $this->csv($request, 'fa'.$filter->ref_id)) {
                        $query->whereHas('attributeValues', fn ($q) => $q->whereIn('attribute_values.id', $ids));
                    }
                    break;
                case 'option':
                    if ($ids = $this->csv($request, 'fo'.$filter->ref_id)) {
                        $query->whereHas('optionValues', fn ($q) => $q->whereIn('option_value_id', $ids));
                    }
                    break;
            }
        }

        return $query;
    }

    public function sort(Builder $query, string $sort): Builder
    {
        [$column, $direction] = self::SORTS[$sort] ?? self::SORTS['default'];
        if ($column === 'name') {
            $locale = app()->getLocale();
            $query->orderByRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"{$locale}\"')) {$direction}");
        } else {
            $query->orderBy($column, $direction);
        }

        return $query->orderByDesc('id');
    }

    /**
     * Sol paneldə göstəriləcək filtr qrupları (dəyərlər + məhsul sayı).
     * $base — filtrsiz kateqoriya sorğusu.
     */
    public function panel(Builder $base, Collection $filters, ?Category $category, Request $request): array
    {
        $groups = [];
        $productIds = (clone $base)->pluck('products.id');

        foreach ($filters as $filter) {
            switch ($filter->type) {
                case 'price':
                    $min = (float) (clone $base)->min('computed_price');
                    $max = (float) (clone $base)->max('computed_price');
                    if ($max > 0) {
                        $groups[] = [
                            'key' => 'p', 'type' => 'price', 'title' => __('Qiymət aralığı'),
                            'min' => (int) floor($min), 'max' => (int) ceil($max),
                            'from' => is_numeric($request->query('fmin')) ? (int) $request->query('fmin') : (int) floor($min),
                            'to' => is_numeric($request->query('fmax')) ? (int) $request->query('fmax') : (int) ceil($max),
                        ];
                    }
                    break;

                case 'subcategory':
                    if (! $category) {
                        break;
                    }
                    $selected = $this->csv($request, 'fc');
                    $items = $category->children()->active()->where('show_in_filter', true)->orderBy('sort')->get()
                        ->map(function (Category $child) use ($productIds, $selected) {
                            $ids = Category::descendantsAndSelf($child->id)->pluck('id');
                            $count = DB::table('category_product')->whereIn('category_id', $ids)->whereIn('product_id', $productIds)->distinct()->count('product_id');

                            return ['value' => $child->id, 'label' => $child->name, 'image' => $child->image, 'count' => $count, 'checked' => in_array($child->id, $selected)];
                        })->filter(fn ($i) => $i['count'] > 0)->values();
                    if ($items->isNotEmpty()) {
                        $groups[] = ['key' => 'c', 'type' => 'radio', 'name' => 'c', 'title' => __('Altbaşlıqlar'), 'items' => $items, 'images' => true];
                    }
                    break;

                case 'stock':
                    $count = (clone $base)->where('stock_status', '!=', 'out_of_stock')->count();
                    $groups[] = ['key' => 'q', 'type' => 'checkbox', 'name' => 'q', 'title' => __('Mövcudluğu'),
                        'items' => collect([['value' => 1, 'label' => __('Stokda var'), 'count' => $count, 'checked' => (bool) $request->query('fq')]])];
                    break;

                case 'brand':
                    $selected = $this->csv($request, 'fm');
                    $counts = (clone $base)->whereNotNull('brand_id')->select('brand_id', DB::raw('count(*) as c'))->groupBy('brand_id')->pluck('c', 'brand_id');
                    $items = Brand::query()->whereIn('id', $counts->keys())->orderBy('sort')->get()
                        ->map(fn ($b) => ['value' => $b->id, 'label' => $b->name, 'image' => $b->logo, 'count' => $counts[$b->id], 'checked' => in_array($b->id, $selected)]);
                    if ($items->isNotEmpty()) {
                        $groups[] = ['key' => 'm', 'type' => 'checkbox', 'name' => 'm', 'title' => __('Brend'), 'items' => $items];
                    }
                    break;

                case 'attribute':
                    $attribute = Attribute::query()->with('values')->find($filter->ref_id);
                    if (! $attribute) {
                        break;
                    }
                    $selected = $this->csv($request, 'fa'.$attribute->id);
                    $counts = DB::table('attribute_value_product')->whereIn('product_id', $productIds)
                        ->whereIn('attribute_value_id', $attribute->values->pluck('id'))
                        ->select('attribute_value_id', DB::raw('count(*) as c'))->groupBy('attribute_value_id')->pluck('c', 'attribute_value_id');
                    $items = $attribute->values->filter(fn ($v) => isset($counts[$v->id]))
                        ->map(fn ($v) => ['value' => $v->id, 'label' => $v->value, 'count' => $counts[$v->id], 'checked' => in_array($v->id, $selected)])->values();
                    if ($items->isNotEmpty()) {
                        $groups[] = ['key' => 'a'.$attribute->id, 'type' => 'checkbox', 'name' => 'a'.$attribute->id, 'title' => $attribute->name, 'items' => $items];
                    }
                    break;

                case 'option':
                    $option = Option::query()->with('values')->find($filter->ref_id);
                    if (! $option) {
                        break;
                    }
                    $selected = $this->csv($request, 'fo'.$option->id);
                    $counts = DB::table('product_option_values')->whereIn('product_id', $productIds)
                        ->whereIn('option_value_id', $option->values->pluck('id'))
                        ->select('option_value_id', DB::raw('count(distinct product_id) as c'))->groupBy('option_value_id')->pluck('c', 'option_value_id');
                    $items = $option->values->filter(fn ($v) => isset($counts[$v->id]))
                        ->map(fn ($v) => ['value' => $v->id, 'label' => $v->name, 'color' => $v->color, 'image' => $v->image, 'count' => $counts[$v->id], 'checked' => in_array($v->id, $selected)])->values();
                    if ($items->isNotEmpty()) {
                        $groups[] = ['key' => 'o'.$option->id, 'type' => 'checkbox', 'name' => 'o'.$option->id, 'title' => $option->name,
                            'items' => $items, 'swatch' => $option->type === 'color'];
                    }
                    break;
            }
        }

        return $groups;
    }

    public function hasActiveFilters(Request $request): bool
    {
        foreach (array_keys($request->query()) as $key) {
            if (preg_match('/^f(c|q|m|min|max|a\d+|o\d+)$/', $key)) {
                return true;
            }
        }

        return false;
    }
}
