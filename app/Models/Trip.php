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

    public function getTotalFuelQuantityAttribute()
    {
        if ($this->relationLoaded('fuelDetails')) {
            return (float)$this->fuelDetails->sum('quantity');
        }
        if ($this->exists && $this->fuelDetails()->exists()) {
            return (float)$this->fuelDetails()->sum('quantity');
        }
        return 0.0;
    }

    public function getTotalFuelAmountAttribute()
    {
        if ($this->relationLoaded('fuelDetails')) {
            $sum = (float)$this->fuelDetails->sum('amount');
            if ($sum > 0) return $sum;
        } elseif ($this->exists && $this->fuelDetails()->exists()) {
            $sum = (float)$this->fuelDetails()->sum('amount');
            if ($sum > 0) return $sum;
        }
        return (float)($this->fuel_amount ?? 0);
    }

    public function getTotalFasttagAmountAttribute()
    {
        if ($this->relationLoaded('fastTagDetails')) {
            $sum = (float)$this->fastTagDetails->sum('amount');
            if ($sum > 0) return $sum;
        } elseif ($this->exists && $this->fastTagDetails()->exists()) {
            $sum = (float)$this->fastTagDetails()->sum('amount');
            if ($sum > 0) return $sum;
        }
        return (float)($this->fasttag_total_amount ?? 0);
    }

    public function getTotalAdblueAmountAttribute()
    {
        if ($this->relationLoaded('adblueDetails')) {
            $sum = (float)$this->adblueDetails->sum('amount');
            if ($sum > 0) return $sum;
        } elseif ($this->exists && $this->adblueDetails()->exists()) {
            $sum = (float)$this->adblueDetails()->sum('amount');
            if ($sum > 0) return $sum;
        }
        return (float)($this->adblue_total_amount ?? 0);
    }

    public function getTotalOtherAmountAttribute()
    {
        if ($this->relationLoaded('otherAmountDetails')) {
            $sum = (float)$this->otherAmountDetails->sum('amount');
            if ($sum > 0) return $sum;
        } elseif ($this->exists && $this->otherAmountDetails()->exists()) {
            $sum = (float)$this->otherAmountDetails()->sum('amount');
            if ($sum > 0) return $sum;
        }
        return (float)($this->other_amount ?? 0);
    }

    public function getTotalAdvanceAmountAttribute()
    {
        if ($this->relationLoaded('advanceDetails')) {
            $sum = (float)$this->advanceDetails->sum('advance_amount');
            if ($sum > 0) return $sum;
        } elseif ($this->exists && $this->advanceDetails()->exists()) {
            $sum = (float)$this->advanceDetails()->sum('advance_amount');
            if ($sum > 0) return $sum;
        }
        return (float)($this->advance_total_amount ?? 0);
    }

    public function getTotalExpensesAttribute()
    {
        return $this->total_fuel_amount + $this->total_fasttag_amount + $this->total_adblue_amount + $this->total_other_amount + $this->total_advance_amount;
    }

    public function getNetProfitAttribute()
    {
        $bultyAmount = $this->builty ? (float)$this->builty->total_amount : 0;
        return $bultyAmount - $this->total_expenses;
    }
}
