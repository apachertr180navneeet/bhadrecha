<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TyreManagement extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tyre_management';

    protected $fillable = [
        'company_id',
        'branch_id',
        'vehicle_id',
        'tyre_position',
        'tyre_brand',
        'tyre_size',
        'tyre_model',
        'serial_number',
        'purchase_date',
        'purchase_cost',
        'installation_date',
        'installation_km',
        'removal_date',
        'removal_km',
        'removal_reason',
        'tread_depth_new',
        'tread_depth_current',
        'pressure_psi',
        'status',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'installation_date' => 'date',
        'removal_date' => 'date',
        'purchase_cost' => 'decimal:2',
        'installation_km' => 'decimal:2',
        'removal_km' => 'decimal:2',
        'tread_depth_new' => 'decimal:2',
        'tread_depth_current' => 'decimal:2',
        'pressure_psi' => 'decimal:1',
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
