<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BranchAccessMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('admin.login');
        }

        $user = auth()->user();

        if ($user->isSuperAdmin() || $user->isCompanyAdmin()) {
            return $next($request);
        }

        $branchId = $request->route('branch') ?? $request->input('branch_id') ?? session('current_branch_id');

        if (!$branchId) {
            $branchId = $user->branch_id;
            session(['current_branch_id' => $branchId]);
        }

        if (!$user->canAccessBranch($branchId)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Unauthorized access to this branch.'], 403);
            }

            abort(403, 'You do not have access to this branch.');
        }

        session(['current_branch_id' => $branchId]);

        return $next($request);
    }
}
