<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripAdvanceDetail extends Model
{
    use HasFactory;

    protected $table = 'trip_advance_details';

    protected $fillable = [
        'trip_id',
        'builty_id',
        'date',
        'fuel_company_id',
        'fuel_pump_id',
        'advance_amount',
        'remark',
    ];

    protected $casts = [
        'date' => 'date',
        'advance_amount' => 'decimal:2',
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
