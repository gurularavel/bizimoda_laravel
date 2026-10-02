<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin → Redirect-lər bölməsində yazılmış 301/302 yönləndirmələri (köhnə URL-lər üçün).
 */
class HandleRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET') && ! $request->ajax()) {
            $map = Cache::rememberForever('redirects.map', fn () => Redirect::query()->get(['id', 'from_path', 'to_path', 'code'])
                ->keyBy(fn ($r) => '/'.trim(urldecode($r->from_path), '/'))->all());

            $path = '/'.trim(urldecode($request->getPathInfo()), '/');
            $full = $request->getQueryString() ? $path.'?'.urldecode($request->getQueryString()) : null;
            $hit = ($full ? ($map[$full] ?? null) : null) ?? ($map[$path] ?? null);

            if ($hit) {
                Redirect::query()->whereKey($hit->id)->increment('hits');

                return redirect($hit->to_path, (int) $hit->code ?: 301);
            }
        }

        return $next($request);
    }
}
