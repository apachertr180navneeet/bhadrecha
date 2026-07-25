<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('admin.login');
        }

        $user = Auth::user();

        if ($user->roles->count() > 0 || $user->role === 'admin') {
            return $next($request);
        }

        return redirect()->back()->with('error', 'Unauthorized access.');
    }
}
