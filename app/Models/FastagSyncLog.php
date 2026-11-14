<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FastagSyncLog extends Model
{
    use HasFactory;

    protected $table = 'fastag_sync_logs';

    protected $fillable = [
        'vehicle_number',
        'sync_from',
        'sync_to',
        'sync_status',
        'total_records_received',
        'total_records_saved',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'sync_from' => 'datetime',
        'sync_to' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'total_records_received' => 'integer',
        'total_records_saved' => 'integer',
    ];

    public function scopeRecent($query)
    {
        return $query->orderBy('id', 'desc');
    }

    public function scopeSuccessful($query)
    {
        return $query->where('sync_status', 'SUCCESS');
    }

    public function scopeFailed($query)
    {
        return $query->where('sync_status', 'FAILED');
    }
}
