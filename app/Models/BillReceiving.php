<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillReceiving extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'company_id',
        'branch_id',
        'date',
        'receiving_amount',
        'receiving_gst',
        'tds',
        'deduction',
        'deduction_reason',
    ];

    protected $casts = [
        'date' => 'date',
        'receiving_amount' => 'decimal:2',
        'receiving_gst' => 'decimal:2',
        'tds' => 'decimal:2',
        'deduction' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
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
