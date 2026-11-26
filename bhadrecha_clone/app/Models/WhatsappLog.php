<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappLog extends Model
{
    protected $fillable = [
        'customer_id', 'mobile', 'message', 'type', 'status', 'response'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function getWhatsappUrlAttribute()
    {
        $mobile = $this->mobile ?? ($this->customer ? $this->customer->mobile : null);
        if (!$mobile) return null;

        $cleanMobile = preg_replace('/[^0-9]/', '', $mobile);
        if (strlen($cleanMobile) == 10) {
            $cleanMobile = '91' . $cleanMobile;
        }
        return "https://wa.me/{$cleanMobile}?text=" . urlencode($this->message);
    }
}
