<?php

use App\Http\Controllers\Front\AccountController;
use App\Http\Controllers\Front\AuthController;
use App\Http\Controllers\Front\BlogController;
use App\Http\Controllers\Front\CartController;
use App\Http\Controllers\Front\CatalogController;
use App\Http\Controllers\Front\CheckoutController;
use App\Http\Controllers\Front\FormController;
use App\Http\Controllers\Front\HomeController;
use App\Http\Controllers\Front\LegacyController;
use App\Http\Controllers\Front\PaymentController;
use App\Http\Controllers\Front\ProductController;
use App\Http\Controllers\Front\SitemapController;
use App\Http\Controllers\Front\SlugController;
use App\Http\Controllers\Front\SocialAuthController;
use App\Support\Locales;
use Illuminate\Support\Facades\Route;

/*
| Prefikssiz route-lar.
| "/" — dil prefiksinə yönləndirir.
| "/ajax/{route}" — Journal3 JS-in AJAX sorğuları (səbət, axtarış, popup...) LegacyController-də emal olunur.
| Köhnə "?route=..." formatı da uyğunluq üçün "/" üzərindən qəbul edilir.
*/
Route::match(['get', 'post'], '/', LegacyController::class)->middleware('locale')->name('root');
Route::match(['get', 'post'], '/ajax/{path}', LegacyController::class)->where('path', '[a-z0-9_/]+')->middleware('locale')->name('ajax');
Route::post('/device-detect', fn () => response()->json(['response' => ['reload' => false]]));
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

Route::middleware('locale')->group(function () {
    Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])->whereIn('provider', ['google', 'facebook'])->name('social.redirect');
    Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->whereIn('provider', ['google', 'facebook'])->name('social.callback');
    Route::match(['get', 'post'], '/payment/kapital/return', [PaymentController::class, 'kapitalReturn'])->name('payment.kapital.return');
});

Route::prefix('{locale}')
    ->where(['locale' => Locales::pattern()])
    ->middleware('locale')
    ->name('front.')
    ->group(function () {
        Route::get('/', HomeController::class)->name('home');

        Route::get('/search', [CatalogController::class, 'search'])->name('search');
        Route::get('/specials', [CatalogController::class, 'specials'])->name('specials');

        Route::get('/cart', [CartController::class, 'index'])->name('cart');
        Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
        Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');

        Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
        Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
        Route::get('/checkout/success/{number}', [CheckoutController::class, 'success'])->name('checkout.success');
        Route::get('/checkout/failed/{number}', [CheckoutController::class, 'failed'])->name('checkout.failed');

        Route::post('/form/{type}', [FormController::class, 'send'])->whereIn('type', ['contact', 'one_click'])->name('form.send');

        Route::middleware('guest:web')->group(function () {
            Route::get('/account/login', [AuthController::class, 'showLogin'])->name('login');
            Route::post('/account/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
            Route::get('/account/register', [AuthController::class, 'showRegister'])->name('register');
            Route::post('/account/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
            Route::get('/account/forgotten', [AuthController::class, 'showForgot'])->name('password.request');
            Route::post('/account/forgotten', [AuthController::class, 'sendResetLink'])->middleware('throttle:5,1')->name('password.email');
            Route::get('/account/reset/{token}', [AuthController::class, 'showReset'])->name('password.reset');
            Route::post('/account/reset', [AuthController::class, 'reset'])->name('password.update');
        });

        Route::post('/account/logout', [AuthController::class, 'logout'])->name('logout');

        Route::middleware('auth:web')->group(function () {
            Route::get('/account', [AccountController::class, 'index'])->name('account');
            Route::get('/account/edit', [AccountController::class, 'edit'])->name('account.edit');
            Route::post('/account/edit', [AccountController::class, 'update']);
            Route::get('/account/password', [AccountController::class, 'password'])->name('account.password');
            Route::post('/account/password', [AccountController::class, 'updatePassword']);
            Route::get('/account/addresses', [AccountController::class, 'addresses'])->name('account.addresses');
            Route::post('/account/addresses', [AccountController::class, 'storeAddress']);
            Route::delete('/account/addresses/{address}', [AccountController::class, 'deleteAddress'])->name('account.addresses.delete');
            Route::get('/account/orders', [AccountController::class, 'orders'])->name('account.orders');
            Route::get('/account/orders/{number}', [AccountController::class, 'order'])->name('account.order');
        });

        Route::get('/account/wishlist', [AccountController::class, 'wishlist'])->name('wishlist');
        Route::get('/compare', [CatalogController::class, 'compare'])->name('compare');
        Route::get('/compare/remove/{product}', [CatalogController::class, 'compareRemove'])->name('compare.remove');
        Route::get('/wishlist/remove/{product}', [AccountController::class, 'wishlistRemove'])->name('wishlist.remove');

        Route::get('/blog', [BlogController::class, 'index'])->name('blog');
        Route::get('/blog/category/{slug}', [BlogController::class, 'category'])->name('blog.category');
        Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.post');

        Route::get('/contact', [SlugController::class, 'contact'])->name('contact');

        Route::post('/product/{product}/price', [ProductController::class, 'price'])->name('product.price');
        Route::post('/product/{product}/review', [ProductController::class, 'review'])->middleware('throttle:5,1')->name('product.review');

        Route::get('/{category}/{product}', [ProductController::class, 'show'])->name('product');
        Route::get('/{slug}', SlugController::class)->name('slug');
    });
