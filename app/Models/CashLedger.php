<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\CompanyScope;

class CashLedger extends Model
{
    use HasFactory, SoftDeletes, CompanyScope;

    protected $table = 'cash_ledgers';

    protected $fillable = [
        'company_id',
        'branch_id',
        'account_name',
        'account_number',
        'transaction_type', // 'debit' or 'credit'
        'amount',
        'date',
        'voucher_no',
        'category',
        'description',
        'payment_mode',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isCredit(): bool
    {
        return strtolower($this->transaction_type) === 'credit';
    }

    public function isDebit(): bool
    {
        return strtolower($this->transaction_type) === 'debit';
    }
}
