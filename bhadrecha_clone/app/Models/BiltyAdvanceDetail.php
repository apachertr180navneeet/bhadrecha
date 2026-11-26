<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\CompanyScope;

class BiltyAdvanceDetail extends Model
{
    use HasFactory, SoftDeletes, CompanyScope;

    protected $table = 'bilty_advance_details';

    protected $fillable = [
        'bulty_id',
        'company_id',
        'branch_id',
        'date',
        'advance_amount',
        'remarks',
    ];

    protected $casts = [
        'date' => 'date',
        'advance_amount' => 'decimal:2',
    ];

    public function builty()
    {
        return $this->belongsTo(Bulty::class, 'bulty_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
