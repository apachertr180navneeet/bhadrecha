<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelPump extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'fuel_company_id',
        'number',
        'address',
        'owner_name',
        'owner_mobile',
        'status',
    ];

    public function fuelCompany()
    {
        return $this->belongsTo(FuelCompany::class);
    }
}
