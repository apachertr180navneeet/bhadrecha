<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $fillable = [
        'customer_id', 'email', 'subject', 'message', 'type', 'status', 'response'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
