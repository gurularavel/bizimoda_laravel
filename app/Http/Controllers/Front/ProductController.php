<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductReview;
use App\Services\SetPricingService;
use App\Support\Locales;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function __construct(protected SetPricingService $pricing) {}

    public function show(Request $request, string $category, string $product)
    {
        $locale = app()->getLocale();
        $model = $this->findBySlug($product, $locale);

        if (! $model) {
            // Başqa dildəki slug → düzgün URL
            foreach (Locales::codes() as $other) {
                if ($other !== $locale && ($found = $this->findBySlug($product, $other))) {
                    return redirect($found->url($locale), 301);
                }
            }
            abort(404);
        }

        $model->load([
            'images', 'mainCategory', 'brand',
            'productOptions.option', 'productOptions.values.optionValue',
            'setItems.component.images',
            'attributeValues.attribute.group',
        ]);

        $canonical = $model->url($locale);
        if ($model->mainCategory && $category !== $model->mainCategory->getTranslation('slug', $locale)) {
            return redirect($canonical, 301);
        }

        Product::query()->whereKey($model->id)->increment('views');

        $config = $this->pricing->defaultConfiguration($model);
        $price = $this->pricing->price($model, $config);

        $related = $model->related()->visible()->forListing()->limit(12)->get();
        if ($related->isEmpty()) {
            $related = Product::query()->visible()->forListing()
                ->where('main_category_id', $model->main_category_id)->whereKeyNot($model->id)
                ->inRandomOrder()->limit(12)->get();
        }

        $alternates = [];
        foreach (Locales::codes() as $code) {
            $alternates[$code] = $model->url($code);
        }

        $crumbs = $model->mainCategory
            ? $model->mainCategory->ancestors()->defaultOrder()->get()->push($model->mainCategory)
                ->map(fn (Category $c) => ['title' => $c->name, 'url' => $c->url()])->all()
            : [];
        $crumbs[] = ['title' => $model->name, 'url' => $canonical];

        $attributeGroups = $model->attributeValues->groupBy(fn ($v) => $v->attribute->group?->name ?? '')
            ->map(fn ($values) => $values->groupBy('attribute_id'));

        $popup = in_array($request->query('popup'), ['quickview', 'options'], true);

        return view($popup ? 'front.product.quickview' : 'front.product.show', [
            'product' => $model,
            'config' => $config,
            'price' => $price,
            'related' => $related,
            'reviews' => $model->reviews()->where('is_approved', true)->latest()->get(),
            'attributeGroups' => $attributeGroups,
            'breadcrumbs' => $crumbs,
            'alternates' => $alternates,
            'canonical' => $canonical,
            'sidebarCategories' => Category::query()->active()->whereIsRoot()->orderBy('sort')
                ->with(['children' => fn ($q) => $q->active()->orderBy('sort'), 'children.children' => fn ($q) => $q->active()->orderBy('sort')])->get(),
            'htmlClass' => $popup
                ? 'route-product-product product-'.$model->id.' layout-2'
                : 'route-product-product product-'.$model->id.' layout-2 one-column column-left',
            'popupClass' => 'popup-quickview',
        ]);
    }

    /** Konfiqurasiyaya görə canlı qiymət (məhsul səhifəsi JS-i) */
    public function price(Request $request, Product $product)
    {
        abort_unless($product->is_active, 404);
        $quantity = max(1, (int) $request->input('quantity', 1));

        try {
            $config = $this->pricing->validate($product, $request->only(['components', 'options']));
            $error = null;
        } catch (ValidationException $e) {
            $config = [
                'components' => array_map('intval', (array) $request->input('components', [])),
                'options' => array_map('intval', array_filter((array) $request->input('options', []))),
            ];
            $error = collect($e->errors())->flatten()->first();
        }

        $price = $this->pricing->price($product, $config);

        return response()->json([
            'error' => $error,
            'errors' => isset($e) ? $e->errors() : [],
            'unit_price' => money($price['unit_price']),
            'unit_old_price' => $price['unit_old_price'] ? money($price['unit_old_price']) : null,
            'total' => money($price['unit_price'] * $quantity),
            'old_total' => $price['unit_old_price'] ? money($price['unit_old_price'] * $quantity) : null,
            'unit_price_raw' => $price['unit_price'],
        ]);
    }

    public function review(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|min:2|max:64',
            'text' => 'required|string|min:10|max:2000',
            'rating' => 'required|integer|between:1,5',
        ], [], ['name' => __('Adınız'), 'text' => __('Rəyiniz'), 'rating' => __('Reytinq')]);

        ProductReview::query()->create([
            'product_id' => $product->id,
            'user_id' => auth('web')->id(),
            'author' => $data['name'],
            'text' => $data['text'],
            'rating' => $data['rating'],
            'is_approved' => false,
        ]);

        return response()->json(['success' => __('Rəyiniz üçün təşəkkür edirik! Moderator təsdiqindən sonra dərc olunacaq.')]);
    }

    protected function findBySlug(string $slug, string $locale): ?Product
    {
        return Product::query()->visible()->whereSlug($slug, $locale)->first();
    }
}
