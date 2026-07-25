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

        $companyId = $request->route('company') ?? $request->input('company_id') ?? session('current_company_id');

        if (!$companyId) {
            $companyId = $user->company_id;
            session(['current_company_id' => $companyId]);
        }

        if (!$user->canAccessCompany($companyId)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Unauthorized access to this company.'], 403);
            }

            abort(403, 'You do not have access to this company.');
        }

        session(['current_company_id' => $companyId]);

        return $next($request);
    }
}
