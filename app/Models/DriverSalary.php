<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverSalary extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'salary_amount',
        'effective_from',
        'effective_to',
    ];

    protected $casts = [
        'salary_amount' => 'decimal:2',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
