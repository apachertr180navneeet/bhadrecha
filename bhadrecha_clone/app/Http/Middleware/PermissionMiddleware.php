<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, $permission)
    {
        if (!auth()->check()) {
            return redirect()->route('admin.login');
        }

        $permissions = explode('|', $permission);

        $hasPermission = false;
        foreach ($permissions as $perm) {
            if (auth()->user()->can(trim($perm))) {
                $hasPermission = true;
                break;
            }
        }

        if (!$hasPermission) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Unauthorized. You do not have permission to access this resource.'], 403);
            }

            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
