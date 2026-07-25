<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driver extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'driver_id', 'name', 'phone', 'license_number', 'license_expiry',
        'address', 'city', 'state', 'emergency_contact', 'status',
        'license_front', 'license_back', 'aadhar_front', 'aadhar_back', 'pan_front', 'pan_back',
    ];

    protected $casts = [
        'license_expiry' => 'date',
    ];
}
