<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripAdBlueDetail extends Model
{
    use HasFactory;

    protected $table = 'trip_adblue_details';

    protected $fillable = [
        'trip_id',
        'builty_id',
        'date',
        'adblue_company_id',
        'quantity',
        'rate',
        'amount',
        'km',
        'payment_type',
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

    public function adblueCompany()
    {
        return $this->belongsTo(AdBlueCompany::class);
    }
}
