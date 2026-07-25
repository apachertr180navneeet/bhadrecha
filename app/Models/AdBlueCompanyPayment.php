<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CompanyScope;

class AdBlueCompanyPayment extends Model
{
    use HasFactory, CompanyScope;

    protected $table = 'adblue_company_payments';

    protected $fillable = [
        'company_id',
        'date',
        'adblue_company_id',
        'amount',
        'payment_method',
        'remark',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'company_id' => 'integer',

        'adblue_company_id' => 'integer',
    ];

    public function adblueCompany()
    {
        return $this->belongsTo(AdBlueCompany::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
