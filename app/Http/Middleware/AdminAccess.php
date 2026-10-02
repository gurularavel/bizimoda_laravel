<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin panel bölməsinə giriş hüququ: Route::middleware('admin.can:catalog')
 */
class AdminAccess
{
    public function handle(Request $request, Closure $next, string $section): Response
    {
        $admin = Auth::guard('admin')->user();

        if (! $admin || ! $admin->is_active || ! $admin->canAccess($section)) {
            abort(403, 'Bu bölməyə giriş icazəniz yoxdur.');
        }

        return $next($request);
    }
}
