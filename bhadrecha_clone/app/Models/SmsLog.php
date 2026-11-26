<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    protected $fillable = [
        'customer_id', 'mobile', 'message', 'type', 'status', 'response'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
