<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Breakdown extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'vehicle_id',
        'driver_id',
        'breakdown_date',
        'breakdown_time',
        'location',
        'latitude',
        'longitude',
        'description',
        'issue_type',
        'severity',
        'vendor_id',
        'repair_cost',
        'downtime_hours',
        'status',
        'resolution_notes',
        'resolved_at',
    ];

    protected $casts = [
        'breakdown_date' => 'date',
        'breakdown_time' => 'string',
        'repair_cost' => 'decimal:2',
        'downtime_hours' => 'decimal:2',
        'resolved_at' => 'datetime',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
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
