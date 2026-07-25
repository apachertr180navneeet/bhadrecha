<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\CompanyScope;

class Consignee extends Model
{
    use HasFactory, SoftDeletes, CompanyScope;

    protected $fillable = [
        'company_id', 'branch_id', 'name', 'phone', 'email', 'gstin',
        'address', 'city', 'state', 'pincode', 'status',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
