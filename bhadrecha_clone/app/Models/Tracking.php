<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tracking extends Model
{
    use HasFactory;

    protected $fillable = ['lr_number', 'status', 'current_location', 'date', 'remarks'];

    protected $casts = [
        'date' => 'date',
    ];
}
