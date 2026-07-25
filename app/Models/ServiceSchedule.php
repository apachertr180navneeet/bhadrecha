<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceSchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'vehicle_id',
        'service_type',
        'scheduled_date',
        'scheduled_km',
        'last_service_date',
        'last_service_km',
        'interval_days',
        'interval_km',
        'status',
        'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'scheduled_km' => 'decimal:2',
        'last_service_date' => 'date',
        'last_service_km' => 'decimal:2',
        'interval_km' => 'decimal:2',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
