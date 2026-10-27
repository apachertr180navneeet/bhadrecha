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
            $model = $builder->getModel();
            $table = $model->getTable();
            $hasCompany = in_array('company_id', $model->getFillable());
            $hasBranch = in_array('branch_id', $model->getFillable());

            if ($user->isSuperAdmin()) {
                $sessionCompanyId = session('current_company_id');
                $sessionBranchId = session('current_branch_id');

                if ($hasCompany && $sessionCompanyId && $sessionCompanyId !== 'all') {
                    $builder->where($table . '.company_id', $sessionCompanyId);
                }

                if ($hasBranch && $sessionBranchId && $sessionBranchId !== 'all') {
                    $builder->where($table . '.branch_id', $sessionBranchId);
                }
            } else {
                // Non-Super Admin: restrict strictly to auth user's assigned company
                if ($hasCompany && $user->company_id) {
                    $builder->where($table . '.company_id', $user->company_id);
                }

                if ($hasBranch) {
                    $sessionBranchId = session('current_branch_id');
                    $assignedBranchIds = $user->assigned_branch_ids;

                    if ($user->isCompanyAdmin()) {
                        // Company Admins manage all branches across their company by default, unless specific branch is selected
                        if ($sessionBranchId && $sessionBranchId !== 'all') {
                            $builder->where($table . '.branch_id', $sessionBranchId);
                        }
                    } else {
                        // Branch-specific or multi-branch authorized user
                        if ($sessionBranchId && $sessionBranchId !== 'all' && $user->canAccessBranch($sessionBranchId)) {
                            $builder->where($table . '.branch_id', $sessionBranchId);
                        } elseif (!empty($assignedBranchIds)) {
                            if (count($assignedBranchIds) === 1) {
                                $builder->where($table . '.branch_id', $assignedBranchIds[0]);
                            } else {
                                $builder->whereIn($table . '.branch_id', $assignedBranchIds);
                            }
                        } elseif ($user->branch_id) {
                            $builder->where($table . '.branch_id', $user->branch_id);
                        }
                    }
                }
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}

