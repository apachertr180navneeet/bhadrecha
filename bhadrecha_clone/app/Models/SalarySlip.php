<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalarySlip extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'month',
        'year',
        'salary_amount',
        'total_deductions',
        'net_payable',
        'advances_data',
        'generated_at',
    ];

    protected $casts = [
        'salary_amount' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_payable' => 'decimal:2',
        'advances_data' => 'array',
        'generated_at' => 'datetime',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
