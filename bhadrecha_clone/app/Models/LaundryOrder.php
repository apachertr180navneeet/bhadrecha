<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaundryOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_date',
        'remark',
        'total_amount',
        'status',
        'is_paid',
        'payment_status',
        'paid_at',
        'expense_id',
        'store_id',
        'branch_id',
    ];

    protected $casts = [
        'order_date' => 'date',
        'paid_at'    => 'date',
        'is_paid'    => 'boolean',
        'total_amount' => 'decimal:2',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    public function items()
    {
        return $this->hasMany(LaundryOrderItem::class);
    }
}
