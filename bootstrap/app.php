<?php

use App\Http\Middleware\AdminAccess;
use App\Http\Middleware\AdminLocale;
use App\Http\Middleware\HandleRedirects;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')->prefix('admin')->name('admin.')->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'locale' => SetLocale::class,
            'admin.can' => AdminAccess::class,
            'admin.locale' => AdminLocale::class,
        ]);

        $middleware->web(append: [HandleRedirects::class]);

        // Bank qayıdışı POST ilə gələ bilər
        $middleware->validateCsrfTokens(except: ['payment/kapital/return*']);

        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('admin', 'admin/*')) {
                return route('admin.login');
            }

            return route('front.login', ['locale' => app()->getLocale()]);
        });

        $middleware->redirectUsersTo(function (Request $request) {
            if ($request->is('admin', 'admin/*')) {
                return route('admin.dashboard');
            }

            return route('front.account', ['locale' => app()->getLocale()]);
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
