<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyLoan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'company_loans';

    protected $fillable = [
        'company_id',
        'bank_id',
        'branch_id',
        'loan_id',
        'tenure_months',
        'given_emi_count',
        'loan_amount',
        'tenure_calculation',
        'interest_rate',
        'total_interest',
        'given_amount',
        'emi_amount',
        'remaining_amount',
        'pending_emi_date',
        'status',
    ];

    protected $casts = [
        'loan_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'total_interest' => 'decimal:2',
        'given_amount' => 'decimal:2',
        'emi_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'pending_emi_date' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function bank()
    {
        return $this->belongsTo(BankMaster::class, 'bank_id');
    }

    public function branch()
    {
        return $this->belongsTo(BankBranchMaster::class, 'branch_id');
    }

    public function payments()
    {
        return $this->hasMany(LoanPayment::class, 'company_loan_id');
    }

    public function getTotalPayableAttribute()
    {
        return $this->loan_amount + $this->total_interest;
    }

    public function getTotalPaidAttribute()
    {
        return $this->payments->sum('amount');
    }
}
