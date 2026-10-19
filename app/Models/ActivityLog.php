<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'description',
        'properties',
        'ip_address',
        'user_agent',
        'company_id',
        'branch_id',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function model()
    {
        return $this->morphTo();
    }

    public static function log($action, $description = null, $model = null, $properties = [])
    {
        $companyId = session('current_company_id');
        if ($companyId === 'all' || !is_numeric($companyId)) {
            $companyId = auth()->user()?->company_id ?? ($model?->company_id ?? null);
        }

        $branchId = session('current_branch_id');
        if ($branchId === 'all' || !is_numeric($branchId)) {
            $branchId = $model?->branch_id ?? null;
        }

        return static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->id : null,
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'company_id' => is_numeric($companyId) ? (int) $companyId : null,
            'branch_id' => is_numeric($branchId) ? (int) $branchId : null,
        ]);
    }
}
