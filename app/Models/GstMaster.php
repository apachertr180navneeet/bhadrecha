<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GstMaster extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'gst_masters';

    protected $fillable = [
        'gst_rate',
        'percentage',
        'description',
        'status',
    ];

    protected $casts = [
        'gst_rate' => 'string',
        'percentage' => 'decimal:2',
    ];
}
