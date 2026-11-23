<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FastagTransaction extends Model
{
    use HasFactory;

    protected $table = 'fastag_transactions';

    protected $fillable = [
        'vehicle_id',
        'vehicle_number',
        'transaction_id',
        'tag_id',
        'toll_plaza_name',
        'transaction_type',
        'amount',
        'transaction_date',
        'transaction_status',
        'lane_code',
        'plaza_code',
        'raw_response',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'datetime',
        'raw_response' => 'array',
    ];

    /**
     * Associated vehicle in the system.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    /**
     * Scope for filtering by vehicle number.
     */
    public function scopeForVehicle($query, $vehicleNumber)
    {
        if (!empty($vehicleNumber)) {
            $normalized = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $vehicleNumber));
            $query->where(function ($q) use ($vehicleNumber, $normalized) {
                $q->where('vehicle_number', $vehicleNumber)
                  ->orWhereRaw("UPPER(REPLACE(REPLACE(vehicle_number, ' ', ''), '-', '')) = ?", [$normalized]);
            });
        }
        return $query;
    }

    /**
     * Scope for filtering by transaction date range.
     */
    public function scopeDateBetween($query, $from = null, $to = null)
    {
        if ($from) {
            $query->where('transaction_date', '>=', $from . ' 00:00:00');
        }
        if ($to) {
            $query->where('transaction_date', '<=', $to . ' 23:59:59');
        }
        return $query;
    }

    /**
     * Scope for status.
     */
    public function scopeStatus($query, $status)
    {
        if (!empty($status)) {
            $query->where('transaction_status', strtoupper($status));
        }
        return $query;
    }

    /**
     * Scope for toll plaza.
     */
    public function scopePlaza($query, $plaza)
    {
        if (!empty($plaza)) {
            $query->where('toll_plaza_name', 'like', '%' . $plaza . '%');
        }
        return $query;
    }

    /**
     * Scope for transaction type (DEBIT, CREDIT, REFUND).
     */
    public function scopeType($query, $type)
    {
        if (!empty($type)) {
            $query->where('transaction_type', strtoupper($type));
        }
        return $query;
    }
}
