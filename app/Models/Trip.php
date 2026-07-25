<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'builty_id',
        'fasttag_total_amount',
        'fuel_amount',
        'other_amount',
        'adblue_total_amount',
        'advance_total_amount',
        'status',
        'trip_no',
        'vehicle_id',
    ];

    protected $casts = [
        'fasttag_total_amount' => 'decimal:2',
        'fuel_amount' => 'decimal:2',
        'other_amount' => 'decimal:2',
        'adblue_total_amount' => 'decimal:2',
        'advance_total_amount' => 'decimal:2',
    ];

    public function builty()
    {
        return $this->belongsTo(Bulty::class, 'builty_id');
    }

    public function fastTagDetails()
    {
        return $this->hasMany(TripFastTagDetail::class);
    }

    public function fuelDetails()
    {
        return $this->hasMany(TripFuelDetail::class);
    }

    public function otherAmountDetails()
    {
        return $this->hasMany(TripOtherAmountDetail::class);
    }

    public function adblueDetails()
    {
        return $this->hasMany(TripAdBlueDetail::class);
    }

    public function advanceDetails()
    {
        return $this->hasMany(TripAdvanceDetail::class);
    }

    public static function generateTripNumber()
    {
        return 'TRIP-' . date('Y') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }
}
