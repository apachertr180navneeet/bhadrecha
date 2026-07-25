<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripOtherAmountDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'builty_id',
        'title',
        'amount',
        'date',
        'remark',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
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
