<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BultyDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'bulty_id',
        'supplier_id',
        'posting_date',
        'po_no',
        'po_item',
        'mat_doc',
        'gate_entry_no',
        'challan_no',
        'challan_date',
        'transporter_code',
        'transporter_name',
        'gate_out_date',
        'invoice_doc',
        'invoice_date',
        'invoice_time',
        'grn_no',
        'grn_date',
        'grn_time',
        'recd_qty',
        'arrival_time',
        'shortage_grn_no',
        'shortage_grn_date',
        'short_qty',
        'damage_qty',
        'ul_date',
        'ul_rate',
        'bag_ld',
        'bag_ul',
        'bag_short',
        'rate_mt',
        'qty_mt',
        'challan_qty',
        'final_wgt',
        'description_services',
        'mn_no',
        'bill_no',
        'supplier_no',
        'material_name',
        'material_no',
        'depot_name',
        'billed_qty',
    ];

    protected $casts = [
        'posting_date' => 'date',
        'gate_out_date' => 'date',
        'invoice_date' => 'date',
        'invoice_time' => 'datetime:H:i',
        'grn_date' => 'date',
        'grn_time' => 'datetime:H:i',
        'shortage_grn_date' => 'date',
        'recd_qty' => 'decimal:2',
        'short_qty' => 'decimal:2',
        'ul_date' => 'date',
        'bag_ld' => 'integer',
        'bag_ul' => 'integer',
        'bag_short' => 'integer',
        'rate_mt' => 'decimal:2',
        'qty_mt' => 'decimal:2',
        'challan_qty' => 'decimal:3',
        'final_wgt' => 'decimal:3',
        'challan_date' => 'date',
        'billed_qty' => 'decimal:2',
    ];

    public function bulty()
    {
        return $this->belongsTo(Bulty::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
