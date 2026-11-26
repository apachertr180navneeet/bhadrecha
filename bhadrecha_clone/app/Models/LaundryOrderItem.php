<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaundryOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'laundry_order_id',
        'item_id',
        'qty',
        'received_qty',
        'price',
        'total',
    ];

    protected $casts = [
        'qty' => 'integer',
        'received_qty' => 'integer',
        'price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function laundryOrder()
    {
        return $this->belongsTo(LaundryOrder::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
