<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\BlogPostController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DiscountController;
use App\Http\Controllers\Admin\FormSubmissionController;
use App\Http\Controllers\Admin\HomeSectionController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\OptionController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RedirectController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\TranslationController;
use App\Http\Controllers\Admin\UploadController;
use Illuminate\Support\Facades\Route;

/* Admin panel: /admin (routes "admin." prefiksi ilə bootstrap/app.php-də qeydiyyatdadır) */

Route::middleware('guest:admin')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
});

Route::middleware(['auth:admin', 'admin.locale'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', DashboardController::class)->middleware('admin.can:dashboard')->name('dashboard');
    Route::post('/upload', UploadController::class)->name('upload');

    Route::middleware('admin.can:catalog')->group(function () {
        Route::get('categories/tree', [CategoryController::class, 'tree'])->name('categories.tree');
        Route::post('categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');
        Route::resource('categories', CategoryController::class)->except('show');

        Route::get('products/search', [ProductController::class, 'search'])->name('products.search');
        Route::post('products/{product}/duplicate', [ProductController::class, 'duplicate'])->name('products.duplicate');
        Route::post('products/bulk', [ProductController::class, 'bulk'])->name('products.bulk');
        Route::resource('products', ProductController::class)->except('show');

        Route::resource('options', OptionController::class)->except('show');
        Route::post('attribute-groups', [AttributeController::class, 'storeGroup'])->name('attribute-groups.store');
        Route::delete('attribute-groups/{group}', [AttributeController::class, 'destroyGroup'])->name('attribute-groups.destroy');
        Route::resource('attributes', AttributeController::class)->except('show');
        Route::resource('brands', BrandController::class)->except('show');
        Route::patch('discounts/{discount}/toggle', [DiscountController::class, 'toggle'])->name('discounts.toggle');
        Route::resource('discounts', DiscountController::class)->except('show');
        Route::get('reviews', [ReviewController::class, 'index'])->name('reviews.index');
        Route::patch('reviews/{review}', [ReviewController::class, 'toggle'])->name('reviews.toggle');
        Route::delete('reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    });

    Route::middleware('admin.can:sales')->group(function () {
        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::get('orders/{order}/print', [OrderController::class, 'print'])->name('orders.print');
        Route::post('orders/{order}/status', [OrderController::class, 'status'])->name('orders.status');
        Route::post('orders/{order}/payment-status', [OrderController::class, 'paymentStatus'])->name('orders.payment-status');
        Route::post('orders/{order}/verify-payment', [OrderController::class, 'verifyPayment'])->name('orders.verify-payment');
        Route::delete('orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
    });

    Route::middleware('admin.can:customers')->group(function () {
        Route::resource('customers', CustomerController::class)->only(['index', 'edit', 'update', 'destroy']);
    });

    Route::middleware('admin.can:content')->group(function () {
        Route::resource('pages', PageController::class)->except('show');
        Route::resource('blog-categories', BlogCategoryController::class)->except('show');
        Route::resource('blog-posts', BlogPostController::class)->except('show');
        Route::get('menus', [MenuController::class, 'index'])->name('menus.index');
        Route::get('menus/{menu}', [MenuController::class, 'edit'])->name('menus.edit');
        Route::post('menus/{menu}/items', [MenuController::class, 'storeItem'])->name('menus.items.store');
        Route::put('menus/{menu}/items/{item}', [MenuController::class, 'updateItem'])->name('menus.items.update');
        Route::delete('menus/{menu}/items/{item}', [MenuController::class, 'destroyItem'])->name('menus.items.destroy');
        Route::post('menus/{menu}/reorder', [MenuController::class, 'reorder'])->name('menus.reorder');
        Route::get('menus-linkables', [MenuController::class, 'linkables'])->name('menus.linkables');
        Route::resource('sliders', SliderController::class)->except('show');
        Route::post('home-sections/reorder', [HomeSectionController::class, 'reorder'])->name('home-sections.reorder');
        Route::resource('home-sections', HomeSectionController::class)->except('show');
    });

    Route::middleware('admin.can:forms')->group(function () {
        Route::get('forms', [FormSubmissionController::class, 'index'])->name('forms.index');
        Route::get('forms/{submission}', [FormSubmissionController::class, 'show'])->name('forms.show');
        Route::delete('forms/{submission}', [FormSubmissionController::class, 'destroy'])->name('forms.destroy');
    });

    Route::middleware('admin.can:system')->group(function () {
        Route::get('settings/{group?}', [SettingController::class, 'edit'])->name('settings.edit');
        Route::post('settings/{group}', [SettingController::class, 'update'])->name('settings.update');
        Route::post('settings-test-mail', [SettingController::class, 'testMail'])->name('settings.test-mail');
        Route::resource('languages', LanguageController::class)->except(['show', 'create', 'store', 'destroy']);
        Route::get('translations', [TranslationController::class, 'index'])->name('translations.index');
        Route::post('translations', [TranslationController::class, 'update'])->name('translations.update');
        Route::post('translations/scan', [TranslationController::class, 'scan'])->name('translations.scan');
        Route::resource('admins', AdminUserController::class)->except('show');
        Route::resource('redirects', RedirectController::class)->except('show');
        Route::post('cache-clear', [SettingController::class, 'clearCache'])->name('cache.clear');
    });
});
