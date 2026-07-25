<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vehicle_number', 'vehicle_type', 'make_model',
        'capacity_tons', 'owner_name', 'owner_phone', 'insurance_expiry',
        'fitness_expiry', 'permit_expiry', 'pollution_expiry', 'status',
        'registration_cert', 'insurance_doc', 'fitness_doc', 'permit_doc', 'pollution_cert',
    ];

    protected $casts = [
        'capacity_tons' => 'decimal:2',
        'insurance_expiry' => 'date',
        'fitness_expiry' => 'date',
        'permit_expiry' => 'date',
        'pollution_expiry' => 'date',
    ];
}
