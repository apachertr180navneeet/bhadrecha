<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillFormat extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'format_name', 'template_type', 'depot_id', 'party_id',
        'visible_fields', 'field_order', 'grn_fields', 'grn_field_order', 'grn_new_page', 'gst_master_id', 'user_id',
    ];

    protected $casts = [
        'visible_fields' => 'array',
        'field_order' => 'array',
        'grn_fields' => 'array',
        'grn_field_order' => 'array',
        'grn_new_page' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function depot()
    {
        return $this->belongsTo(Branch::class, 'depot_id');
    }

    public function party()
    {
        return $this->belongsTo(Consignor::class, 'party_id');
    }

    public function gstMaster()
    {
        return $this->belongsTo(GstMaster::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
