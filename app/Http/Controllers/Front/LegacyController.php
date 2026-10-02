<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Front\Concerns\CartResponses;
use App\Models\Product;
use App\Services\CartService;
use App\Services\ProductSearch;
use App\Support\Locales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * "/" ünvanı:
 *  - ?route=... yoxdursa → aktiv dilin ana səhifəsinə yönləndirmə
 *  - Journal3/OpenCart JS-in AJAX sorğuları (/ajax/{route}, köhnə ?route=...) burada emal olunur
 *    (dizayn JS-ini dəyişmədən saxlamaq üçün).
 */
class LegacyController extends Controller
{
    use CartResponses;

    public function __invoke(Request $request, CartService $cart)
    {
        $route = trim((string) ($request->route('path') ?? $request->query('route', '')), '/');

        return match ($route) {
            '' => redirect('/'.app()->getLocale(), 302),
            'common/home' => redirect()->route('front.home'),
            'common/language/language' => $this->language($request),
            'checkout/cart/add' => $this->cartAdd($request, $cart),
            'checkout/cart/edit' => $this->cartEdit($request, $cart),
            'checkout/cart/remove' => $this->cartRemove($request, $cart),
            'common/cart/info' => response()->view('front.partials.cart-dropdown', ['cart' => $cart]),
            'checkout/cart' => redirect()->route('front.cart'),
            'checkout/checkout' => redirect()->route('front.checkout'),
            'account/wishlist/add' => $this->wishlistAdd($request),
            'product/compare/add' => $this->compareAdd($request),
            'product/compare' => redirect()->route('front.compare'),
            'product/special' => redirect()->route('front.specials'),
            'product/search' => redirect()->route('front.search', ['search' => $request->query('search')]),
            'journal3/search' => $this->search($request),
            'journal3/popup/get' => $this->popupGet($request),
            'journal3/popup/page' => response()->view('front.popup.one-click', ['htmlClass' => 'route-journal3-popup-page layout-4']),
            'journal3/product' => $this->productPopup($request),
            'journal3/blog' => redirect()->route('front.blog'),
            'account/login' => redirect()->route('front.login', $request->query('popup') ? ['popup' => 1] : []),
            'account/register' => redirect()->route('front.register', $request->query('popup') ? ['popup' => 1] : []),
            'account/account' => redirect()->route('front.account'),
            'account/forgotten' => redirect()->route('front.password.request'),
            'product/product' => $this->productRedirect($request),
            default => abort(404),
        };
    }

    protected function language(Request $request)
    {
        $code = (string) $request->input('code');
        if (! Locales::isSupported($code)) {
            $code = Locales::default();
        }
        session(['locale' => $code]);

        $target = (string) $request->input('redirect_'.$code, '');
        // Yalnız öz saytımıza yönləndirmə
        if ($target === '' || ! Str::startsWith($target, url('/'))) {
            $target = url($code);
        }

        return redirect($target);
    }

    protected function cartAdd(Request $request, CartService $cart)
    {
        $product = Product::query()->active()->find((int) $request->input('product_id'));
        if (! $product) {
            return response()->json(['error' => ['product' => __('Məhsul tapılmadı.')]]);
        }

        try {
            $input = $request->only(['components', 'options']);
            if (! $request->has('options') && $product->requiresConfiguration()) {
                $input['options'] = app(\App\Services\SetPricingService::class)->defaultConfiguration($product)['options'];
            }
            $cart->add($product, max(1, (int) $request->input('quantity', 1)), $input);
        } catch (ValidationException) {
            // Konfiqurasiya tələb olunur → məhsul səhifəsinə
            return response()->json(['redirect' => $product->url()]);
        }

        return response()->json($this->cartAddedResponse($product, $cart));
    }

    protected function cartEdit(Request $request, CartService $cart)
    {
        $cart->update((int) $request->input('key'), (int) $request->input('quantity'));

        return response()->json(['success' => true] + $this->cartTotals($cart));
    }

    protected function cartRemove(Request $request, CartService $cart)
    {
        $cart->remove((int) $request->input('key'));

        return response()->json(['success' => true] + $this->cartTotals($cart));
    }

    protected function wishlistAdd(Request $request)
    {
        $product = Product::query()->visible()->findOrFail((int) $request->input('product_id'));
        $user = Auth::guard('web')->user();

        if ($user) {
            $user->wishlist()->syncWithoutDetaching([$product->id]);
            $count = $user->wishlist()->count();
        } else {
            $ids = array_values(array_unique(array_merge(session('wishlist', []), [$product->id])));
            session(['wishlist' => $ids]);
            $count = count($ids);
        }

        $message = __('<a href=":product">:name</a> <a href=":list">arzu siyahınıza</a> əlavə edildi!', [
            'product' => $product->url(), 'name' => e($product->name), 'list' => lroute('front.wishlist'),
        ]);

        return response()->json([
            'success' => $message,
            'notification' => $this->simpleNotification($product, $message, 'notification-wishlist'),
            'total' => __('Arzu siyahısı (:count)', ['count' => $count]),
            'count' => $count,
        ]);
    }

    protected function compareAdd(Request $request)
    {
        $product = Product::query()->visible()->findOrFail((int) $request->input('product_id'));
        $ids = session('compare', []);
        if (! in_array($product->id, $ids, true)) {
            $ids[] = $product->id;
        }
        $ids = array_slice($ids, -4); // ən çox 4 məhsul
        session(['compare' => $ids]);

        $message = __('<a href=":product">:name</a> <a href=":list">müqayisə siyahısına</a> əlavə edildi!', [
            'product' => $product->url(), 'name' => e($product->name), 'list' => lroute('front.compare'),
        ]);

        return response()->json([
            'success' => $message,
            'notification' => $this->simpleNotification($product, $message, 'notification-compare'),
            'total' => __('Məhsul müqayisəsi (:count)', ['count' => count($ids)]),
            'count' => count($ids),
        ]);
    }

    /** Journal3 typeahead axtarış təklifləri */
    protected function search(Request $request)
    {
        $term = mb_substr(trim((string) $request->query('search', '')), 0, 100);
        $results = [];

        if (mb_strlen($term) >= 2) {
            $search = app(ProductSearch::class);

            // Uyğun kateqoriyalar (təkliflərin yuxarısında)
            foreach ($search->categories($term, 3) as $category) {
                $results[] = [
                    'category' => true,
                    'name' => e($category->name),
                    'parent' => e((string) $category->ancestors()->defaultOrder()->first()?->name),
                    'count' => $category->products_count,
                    'href' => $category->url(),
                ];
            }

            $query = $search->apply(Product::query()->visible(), $term);
            $total = (clone $query)->count();
            $products = $search->rank($query->with(['images', 'mainCategory']), $term)->orderBy('sort')->limit(6)->get();

            foreach ($products as $product) {
                $image = $product->mainImage();
                $results[] = [
                    'name' => e($product->name),
                    'href' => $product->url(),
                    'thumb' => thumb($image, 80, 80),
                    'thumb2' => thumb($image, 160, 160),
                    'category_name' => $product->mainCategory ? e($product->mainCategory->name) : '',
                    'quantity' => $product->inStock() ? 1 : 0,
                    'price_value' => (float) $product->computed_price,
                    'price' => $product->hasDiscount() ? money($product->computed_old_price) : money($product->computed_price),
                    'special' => $product->hasDiscount() ? money($product->computed_price) : false,
                    'discount' => $product->discountPercent(),
                ];
            }

            if ($total > 0) {
                $results[] = ['view_more' => true, 'name' => e(__('Bütün nəticələr (:count)', ['count' => $total])), 'href' => lroute('front.search', ['search' => $term])];
            } elseif (! $results) {
                $results[] = ['no_results' => true, 'name' => e(__('":term" üzrə heç nə tapılmadı', ['term' => $term]))];
            }
        }

        return response()->json(['status' => 'success', 'response' => $results]);
    }

    protected function popupGet(Request $request)
    {
        // Hazırda yeganə popup modulu — "Bir kliklə al" (module_id=22)
        return response()->view('front.popup.wrapper', [
            'src' => 'ajax/journal3/popup/page?module_id=22&popup=module',
        ]);
    }

    protected function productPopup(Request $request)
    {
        $product = Product::query()->visible()->findOrFail((int) $request->query('product_id'));

        return redirect($product->url().'?popup='.urlencode((string) $request->query('popup', 'quickview')));
    }

    protected function productRedirect(Request $request)
    {
        $product = Product::query()->visible()->findOrFail((int) $request->query('product_id'));

        return redirect($product->url(), 301);
    }

    protected function simpleNotification(Product $product, string $message, string $class): array
    {
        $image = $product->mainImage();

        return [
            'className' => $class,
            'position' => 'tr',
            'title' => $product->name,
            'image' => thumb($image, 60, 60),
            'image2x' => thumb($image, 120, 120),
            'message' => $message,
            'buttons' => [],
        ];
    }
}
