<?php

namespace App\Http\Middleware;

use App\Support\Locales;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * /{locale}/... prefiksindən dili təyin edir. Deaktiv dil → default dilə yönləndirmə.
 * Prefikssiz sorğularda (AJAX, ödəniş qayıdışı) sessiyadakı dil istifadə olunur.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');

        if ($locale !== null && ! Locales::isSupported($locale)) {
            $segments = $request->segments();
            $segments[0] = Locales::default();

            return redirect('/'.implode('/', $segments), 301);
        }

        $locale ??= $request->session()->get('locale');
        if (! Locales::isSupported($locale)) {
            $locale = Locales::default();
        }

        app()->setLocale($locale);
        Carbon::setLocale($locale);
        URL::defaults(['locale' => $locale]);
        $request->session()->put('locale', $locale);

        // route parametri kimi controller-lərə ötürülməsin
        $request->route()?->forgetParameter('locale');

        return $next($request);
    }
}
