<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CompanyAccessMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('admin.login');
        }

        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Direct company and branch strictly from auth user for non-Super Admin
        session(['current_company_id' => $user->company_id]);
        if ($user->branch_id) {
            session(['current_branch_id' => $user->branch_id]);
        }

        $companyId = $request->route('company') ?? $request->input('company_id') ?? $user->company_id;

        if ($companyId && !$user->canAccessCompany($companyId)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Unauthorized access to this company.'], 403);
            }

            abort(403, 'You do not have access to this company.');
        }

        return $next($request);
    }
}

