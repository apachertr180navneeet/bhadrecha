<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DriverAdvance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'driver_id',
        'amount',
        'deduction_type',
        'monthly_deduction',
        'date',
        'remark',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'monthly_deduction' => 'decimal:2',
        'date' => 'date',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
