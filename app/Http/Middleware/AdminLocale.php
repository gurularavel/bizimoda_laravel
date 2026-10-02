<?php

namespace App\Http\Middleware;

use App\Support\Locales;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/** Admin paneldə modellər default dildə (az) göstərilir; front URL-ləri də həmin dildə qurulur. */
class AdminLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = Locales::default();
        app()->setLocale($locale);
        URL::defaults(['locale' => $locale]);

        return $next($request);
    }
}
