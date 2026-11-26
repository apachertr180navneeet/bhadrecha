<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripFuelDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'builty_id',
        'date',
        'fuel_company_id',
        'fuel_pump_id',
        'quantity',
        'rate',
        'amount',
        'km',
        'payment_type',
        'remark',
    ];

    protected $casts = [
        'date' => 'date',
        'quantity' => 'decimal:2',
        'rate' => 'decimal:2',
        'amount' => 'decimal:2',
        'km' => 'decimal:2',
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function builty()
    {
        return $this->belongsTo(Bulty::class, 'builty_id');
    }

    public function fuelCompany()
    {
        return $this->belongsTo(FuelCompany::class);
    }

    public function fuelPump()
    {
        return $this->belongsTo(FuelPump::class);
    }
}
