<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentService extends Model
{
    protected $table = 'appointment_services';

    protected $fillable = [
        'appointment_id',
        'service_id',
        'staff_id',
        'staff_ids',
        'price',
        'duration',
    ];

    protected $casts = [
        'staff_ids' => 'array',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function staffMembers()
    {
        $ids = $this->staff_ids;
        if (!is_array($ids) || empty($ids)) {
            if ($this->staff_id) {
                $ids = [$this->staff_id];
            } else {
                return collect([]);
            }
        }
        return Staff::whereIn('id', $ids)->get();
    }
}
