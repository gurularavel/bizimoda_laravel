<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryFilter;
use App\Models\Product;
use App\Services\ProductFilterService;
use App\Services\ProductSearch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CatalogController extends Controller
{
    public function __construct(protected ProductFilterService $filters) {}

    public function category(Request $request, Category $category)
    {
        $ids = Category::descendantsAndSelf($category->id)->where('is_active', true)->pluck('id');
        $base = Product::query()->visible()->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $ids));

        $alternates = [];
        foreach (array_keys(locales()) as $code) {
            $alternates[$code] = $category->url($code);
        }

        return $this->listing($request, $base, $category->effectiveFilters(), [
            'category' => $category,
            'heading' => $category->name,
            'breadcrumbs' => $this->categoryCrumbs($category),
            'metaTitle' => $category->meta_title ?: $category->name,
            'metaDescription' => $category->meta_description,
            'alternates' => $alternates,
            'htmlClass' => 'route-product-category category-'.$category->id.' layout-3 one-column column-left',
        ]);
    }

    public function search(Request $request, ProductSearch $search)
    {
        $term = mb_substr(trim((string) $request->query('search', '')), 0, 100);
        $base = $search->apply(Product::query()->visible(), $term);

        $heading = $term !== '' ? __('Axtarış').' - '.$term : __('Axtarış');

        return $this->listing($request, $base, $this->defaultFilters(), [
            'heading' => $heading,
            'breadcrumbs' => [['title' => __('Axtarış'), 'url' => lroute('front.search', ['search' => $term])]],
            'metaTitle' => $heading,
            'searchTerm' => $term,
            'searchCategories' => $term !== '' ? $search->categories($term, 6) : collect(),
            'rank' => fn (Builder $q) => $search->rank($q, $term),
            'htmlClass' => 'route-product-search layout-13 one-column column-left',
        ]);
    }

    public function specials(Request $request)
    {
        return $this->listing($request, Product::query()->visible()->onSale(), $this->defaultFilters(), [
            'heading' => __('Xüsusi təkliflər'),
            'breadcrumbs' => [['title' => __('Xüsusi təkliflər'), 'url' => lroute('front.specials')]],
            'metaTitle' => __('Xüsusi təkliflər'),
            'htmlClass' => 'route-product-special layout-13 one-column column-left',
        ]);
    }

    public function compare()
    {
        $ids = session('compare', []);
        $products = Product::query()->active()->whereIn('id', $ids)
            ->with(['images', 'mainCategory', 'attributeValues.attribute.group', 'brand'])->get();

        $attributes = $products->flatMap(fn ($p) => $p->attributeValues->map->attribute)->unique('id')->sortBy('sort');

        return view('front.catalog.compare', [
            'products' => $products,
            'attributes' => $attributes,
            'htmlClass' => 'route-product-compare layout-14',
        ]);
    }

    public function compareRemove(Product $product)
    {
        session(['compare' => array_values(array_diff(session('compare', []), [$product->id]))]);

        return redirect()->route('front.compare')->with('success', __('Məhsul müqayisədən silindi.'));
    }

    protected function listing(Request $request, Builder $base, Collection $filters, array $data)
    {
        $sort = array_key_exists($request->query('sort'), ProductFilterService::SORTS) ? $request->query('sort') : 'default';
        $limit = in_array((int) $request->query('limit'), ProductFilterService::LIMITS, true) ? (int) $request->query('limit') : ProductFilterService::LIMITS[0];

        $query = $this->filters->apply((clone $base), $request, $filters);
        // Axtarışda "Əsas" sıralama = uyğunluq
        if ($sort === 'default' && isset($data['rank'])) {
            ($data['rank'])($query);
        }
        unset($data['rank']);
        $this->filters->sort($query, $sort);

        $products = $query->forListing()->paginate($limit)->withQueryString();
        $panel = $this->filters->panel($base, $filters, $data['category'] ?? null, $request);

        // Journal filterBase: filtr parametrləri olmadan cari URL (sort/limit qalır)
        $keep = collect($request->query())->filter(fn ($v, $k) => in_array($k, ['sort', 'limit', 'search'], true))->all();
        $filterBase = $request->url().($keep ? '?'.http_build_query($keep) : '');

        $bottom = Product::query()->visible()->onSale()->forListing()->orderBy('sort')->limit(12)->get();

        return view('front.catalog.listing', $data + [
            'products' => $products,
            'panel' => $panel,
            'sort' => $sort,
            'limit' => $limit,
            'bottomProducts' => $bottom,
            'hasActiveFilters' => $this->filters->hasActiveFilters($request),
            'journalExtra' => ['filterBase' => $filterBase],
        ]);
    }

    protected function defaultFilters(): Collection
    {
        return collect(['price', 'stock'])->map(fn ($t, $i) => new CategoryFilter(['type' => $t, 'sort' => $i]));
    }

    protected function categoryCrumbs(Category $category): array
    {
        return $category->ancestors()->defaultOrder()->get()->push($category)
            ->map(fn (Category $c) => ['title' => $c->name, 'url' => $c->url()])->all();
    }
}
