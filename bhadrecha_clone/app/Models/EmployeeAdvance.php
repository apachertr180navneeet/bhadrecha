<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeAdvance extends Model
{
    use HasFactory;
    protected $table = 'employee_advances';

    protected $fillable = [
        'user_id',
        'amount',
        'deduction_type',
        'monthly_deduction',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'salary_payment_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'monthly_deduction' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
