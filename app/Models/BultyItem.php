<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BultyItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'bulty_id',
        'item_id',
        'item_name',
        'packaging_type',
        'articles',
        'weight',
        'unit',
        'freight_per_mt',
        'amount',
    ];

    protected $casts = [
        'articles' => 'integer',
        'weight' => 'decimal:2',
        'freight_per_mt' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function bulty()
    {
        return $this->belongsTo(Bulty::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
