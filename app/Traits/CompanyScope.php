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

            $sessionCompanyId = session('current_company_id');
            $sessionBranchId = session('current_branch_id');

            if ($sessionCompanyId === 'all') return;

            if ($sessionCompanyId) {
                $builder->where('company_id', $sessionCompanyId);
            } elseif (!auth()->user()->isSuperAdmin()) {
                $builder->where('company_id', auth()->user()->company_id);
            }

            if ($sessionBranchId && $sessionBranchId !== 'all') {
                $builder->where('branch_id', $sessionBranchId);
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
