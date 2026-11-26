<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CompanyScope;

class FuelPumpPayment extends Model
{
    use HasFactory, CompanyScope;

    protected $table = 'fuel_pump_payments';

    protected $fillable = [
        'company_id',
        'date',
        'fuel_company_id',
        'fuel_pump_id',
        'amount',
        'payment_method',
        'remark',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'company_id' => 'integer',

        'fuel_company_id' => 'integer',
        'fuel_pump_id' => 'integer',
    ];

    public function fuelCompany()
    {
        return $this->belongsTo(FuelCompany::class);
    }

    public function fuelPump()
    {
        return $this->belongsTo(FuelPump::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
