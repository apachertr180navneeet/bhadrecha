<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeIncentive extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'amount',
        'reason',
        'is_processed',
        'salary_payment_id',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_processed' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function salaryPayment()
    {
        return $this->belongsTo(SalaryPayment::class);
    }
}
