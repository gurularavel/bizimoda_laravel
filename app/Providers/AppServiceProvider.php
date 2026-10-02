<?php

namespace App\Providers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use App\Models\Redirect;
use App\Services\CartService;
use App\Services\MenuBuilder;
use App\Services\SettingsRepository;
use App\Services\SetPricingService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingsRepository::class);
        $this->app->singleton(\App\Services\DiscountService::class);
        $this->app->singleton(SetPricingService::class);
        $this->app->scoped(CartService::class);
    }

    public function boot(): void
    {
        Paginator::useBootstrapThree();

        ResetPassword::createUrlUsing(fn ($user, string $token) => route('front.password.reset', [
            'locale' => app()->getLocale(), 'token' => $token, 'email' => $user->getEmailForPasswordReset(),
        ]));

        $this->applyRuntimeSettings();

        // Menyu keşində kateqoriya/səhifə adları və slug-ları saxlanılır
        foreach ([Category::class, Page::class, Product::class, BlogCategory::class, BlogPost::class] as $model) {
            $model::saved(fn () => MenuBuilder::flush());
            $model::deleted(fn () => MenuBuilder::flush());
        }
        Redirect::saved(fn () => Cache::forget('redirects.map'));
        Redirect::deleted(fn () => Cache::forget('redirects.map'));

        View::composer(['front.layouts.app', 'front.layouts.popup'], \App\Http\View\FrontComposer::class);
    }

    /**
     * Admin → Parametrlər bölməsində saxlanılan SMTP və sosial giriş açarlarını
     * runtime konfiqurasiyasına yazır.
     */
    protected function applyRuntimeSettings(): void
    {
        try {
            $settings = $this->app->make(SettingsRepository::class);

            if ($host = $settings->get('mail.host')) {
                config([
                    'mail.default' => $settings->get('mail.mailer', 'smtp'),
                    'mail.mailers.smtp.host' => $host,
                    'mail.mailers.smtp.port' => (int) $settings->get('mail.port', 587),
                    'mail.mailers.smtp.username' => $settings->get('mail.username'),
                    'mail.mailers.smtp.password' => $settings->get('mail.password'),
                    'mail.mailers.smtp.scheme' => $settings->get('mail.encryption') === 'ssl' ? 'smtps' : null,
                ]);
            }
            if ($from = $settings->get('mail.from_address')) {
                config(['mail.from.address' => $from, 'mail.from.name' => $settings->get('mail.from_name', config('app.name'))]);
            }

            foreach (['google', 'facebook'] as $provider) {
                if ($id = $settings->get("social.{$provider}_client_id")) {
                    config([
                        "services.{$provider}.client_id" => $id,
                        "services.{$provider}.client_secret" => $settings->get("social.{$provider}_client_secret"),
                    ]);
                }
            }
        } catch (Throwable) {
            // quraşdırma mərhələsində (cədvəl yoxdur) səssiz keç
        }
    }
}
