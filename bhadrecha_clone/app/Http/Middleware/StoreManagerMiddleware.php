<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StoreManagerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('admin.login');
        }

        $user = auth()->user();
        if (!$user->hasRole('Admin') && !$user->hasRole('Manager') && !$user->hasRole('Store Manager')) {
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}
