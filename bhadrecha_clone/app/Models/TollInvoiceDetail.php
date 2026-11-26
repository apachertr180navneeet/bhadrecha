<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TollInvoiceDetail extends Model
{
    use HasFactory;

    protected $table = 'toll_invoice_details';

    protected $fillable = [
        'toll_invoice_id',
        'builty_id',
        'location',
        'one_way',
        'return_amount',
    ];

    protected $casts = [
        'one_way' => 'decimal:2',
        'return_amount' => 'decimal:2',
    ];

    public function tollInvoice()
    {
        return $this->belongsTo(Invoice::class, 'toll_invoice_id');
    }

    public function builty()
    {
        return $this->belongsTo(Bulty::class, 'builty_id');
    }
}
