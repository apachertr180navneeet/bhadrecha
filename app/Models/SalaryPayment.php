<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryPayment extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'month',
        'year',
        'base_salary',
        'allowances',
        'deductions',
        'incentives_total',
        'advance_deduction',
        'advances_data',
        'working_days',
        'attended_days',
        'per_day_rate',
        'attendance_salary',
        'net_payable',
        'status',
        'processed_at',
        'created_by',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'deductions' => 'decimal:2',
        'incentives_total' => 'decimal:2',
        'advance_deduction' => 'decimal:2',
        'per_day_rate' => 'decimal:2',
        'attendance_salary' => 'decimal:2',
        'net_payable' => 'decimal:2',
        'working_days' => 'integer',
        'attended_days' => 'integer',
        'processed_at' => 'datetime',
        'advances_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function incentives()
    {
        return $this->hasMany(EmployeeIncentive::class, 'salary_payment_id');
    }
}
