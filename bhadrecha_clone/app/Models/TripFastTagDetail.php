<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripFastTagDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'builty_id',
        'transaction_time',
        'amount',
        'description',
        'transaction_id',
        'location',
        'one_way',
        'return',
    ];

    protected $casts = [
        'transaction_time' => 'datetime',
        'amount' => 'decimal:2',
        'one_way' => 'decimal:2',
        'return' => 'decimal:2',
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function builty()
    {
        return $this->belongsTo(Bulty::class, 'builty_id');
    }
}
