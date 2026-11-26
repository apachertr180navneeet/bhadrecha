<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'maintenance_history';

    protected $fillable = [
        'company_id',
        'branch_id',
        'vehicle_id',
        'service_schedule_id',
        'spare_part_id',
        'vendor_id',
        'service_type',
        'service_date',
        'current_km',
        'description',
        'vendor_name',
        'cost',
        'next_service_date',
        'next_service_km',
        'status',
        'notes',
    ];

    protected $casts = [
        'service_date' => 'date',
        'current_km' => 'decimal:2',
        'cost' => 'decimal:2',
        'next_service_date' => 'date',
        'next_service_km' => 'decimal:2',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function serviceSchedule()
    {
        return $this->belongsTo(ServiceSchedule::class);
    }

    public function sparePart()
    {
        return $this->belongsTo(SparePart::class);
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
