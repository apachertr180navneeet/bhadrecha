<?php

namespace App\Traits;

use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;

trait CompanyScope
{
    protected static function bootCompanyScope()
    {
        static::addGlobalScope('company', function (Builder $builder) {
            if (!auth()->check()) return;

            $user = auth()->user();

            if ($user->isSuperAdmin()) {
                $sessionCompanyId = session('current_company_id');
                $sessionBranchId = session('current_branch_id');

                if ($sessionCompanyId && $sessionCompanyId !== 'all') {
                    $builder->where('company_id', $sessionCompanyId);
                }

                if ($sessionBranchId && $sessionBranchId !== 'all') {
                    $builder->where('branch_id', $sessionBranchId);
                }
            } else {
                // Non-Super Admin: ALWAYS restrict strictly to auth user's assigned company and branch
                if ($user->company_id) {
                    $builder->where('company_id', $user->company_id);
                }
                if ($user->branch_id) {
                    $builder->where('branch_id', $user->branch_id);
                }
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}

