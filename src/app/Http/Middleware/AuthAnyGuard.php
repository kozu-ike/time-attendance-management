<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AuthAnyGuard
{
    public function handle($request, Closure $next)
    {
        if (! Auth::guard('web')->check() && ! Auth::guard('admin')->check()) {
            if ($request->is('admin/*')) {
                return redirect()->route('admin.login');
            }

            return redirect()->route('login');
        }

        return $next($request);
    }
}
