<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\CompanyScope;

class Bulty extends Model
{
    use HasFactory, SoftDeletes, CompanyScope;

    protected $table = 'bulties';

    protected $fillable = [
        'company_id', 'branch_id', 'lr_no', 'e_lr_no', 'lr_date',
        'from_city', 'to_city',
        'consignor_id', 'consignee_id',
        'declared_value', 'freight_charges', 'gst_type', 'gst_amount', 'gst_master_id', 'other_charges',
        'damage_amount', 'shortage_amount',
        'total_amount', 'payment_type', 'mode', 'status', 'share_token',
        'material_document', 'material_document_status',
        'consignor_pod', 'consignee_pod',
        'pod_document', 'pod_document_status',
        'vehicle_id', 'driver_id',
        'remark', 'bilty_commission',
        'order_number', 'delivery_number', 'from_no',
        'invoice_number', 'invoice_date',
        'eway_bill_no', 'generation_date', 'expiry_date',
        'advance_amount',
        'remaining_amount',
        'bill_status',
        'invoice_id',
        'toll_invoice_id',
    ];

    protected $casts = [
        'declared_value' => 'decimal:2',
        'freight_charges' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'other_charges' => 'decimal:2',
        'damage_amount' => 'decimal:2',
        'shortage_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'bilty_commission' => 'decimal:2',
        'advance_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'material_document_status' => 'boolean',
        'pod_document_status' => 'boolean',
        'lr_date' => 'date',
        'invoice_date' => 'date',
        'generation_date' => 'date',
        'expiry_date' => 'date',
        'bill_status' => 'string',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($bulty) {
            if (!$bulty->lr_no) {
                $bulty->lr_no = static::generateLRNumber($bulty->branch_id);
            }
            if (!$bulty->share_token) {
                $bulty->share_token = (string) \Illuminate\Support\Str::uuid();
            }
        });

        static::deleting(function ($bulty) {
            if ($bulty->isForceDeleting()) {
                $bulty->bultyItems()->forceDelete();
                $bulty->bultyDetail()->forceDelete();
                if ($bulty->trip) {
                    $bulty->trip->fastTagDetails()->delete();
                    $bulty->trip->fuelDetails()->delete();
                    $bulty->trip->otherAmountDetails()->delete();
                    $bulty->trip->adblueDetails()->delete();
                    $bulty->trip->delete();
                }
            }
        });
    }

    public static function generateLRNumber($branchId = null)
    {
        $year = date('Y');
        $prefix = 'LR';

        if ($branchId) {
            $branch = \App\Models\Branch::find($branchId);
            if ($branch && $branch->name) {
                $prefix = strtoupper(substr($branch->name, 0, 1));
            }
        }

        $latest = static::withTrashed()->where('lr_no', 'like', "{$prefix}-{$year}-%")->orderBy('id', 'desc')->first();

        if ($latest) {
            $lastNumber = intval(substr($latest->lr_no, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . '-' . $year . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function consignor()
    {
        return $this->belongsTo(Consignor::class);
    }

    public function consignee()
    {
        return $this->belongsTo(Consignee::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function originCity()
    {
        return $this->belongsTo(City::class, 'from_city');
    }

    public function destinationCity()
    {
        return $this->belongsTo(City::class, 'to_city');
    }

    public function bultyItems()
    {
        return $this->hasMany(BultyItem::class);
    }

    public function bultyDetail()
    {
        return $this->hasOne(BultyDetail::class);
    }

    public function biltyDetail()
    {
        return $this->bultyDetail();
    }

    public function biltyAdvanceDetails()
    {
        return $this->hasMany(BiltyAdvanceDetail::class, 'bulty_id');
    }

    public function trip()
    {
        return $this->hasOne(Trip::class, 'builty_id');
    }

    public function gstMaster()
    {
        return $this->belongsTo(GstMaster::class, 'gst_master_id');
    }

    public function getMaterialDocumentsListAttribute()
    {
        if (!$this->material_document) {
            return [];
        }
        $decoded = json_decode($this->material_document, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }
        return [$this->material_document];
    }

    public function getPodDocumentsListAttribute()
    {
        if (!$this->pod_document) {
            return [];
        }
        $decoded = json_decode($this->pod_document, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }
        return [$this->pod_document];
    }

    public static function deleteStoredDocumentFiles($fieldValue)
    {
        if (!$fieldValue) {
            return;
        }
        $urls = is_array($fieldValue) ? $fieldValue : (json_decode($fieldValue, true) ?: [$fieldValue]);
        foreach ($urls as $url) {
            if (!is_string($url) || empty($url)) {
                continue;
            }
            $relativePath = str_replace(asset('uploads/'), '', $url);
            $relativePath = ltrim(str_replace('/uploads/', '', parse_url($relativePath, PHP_URL_PATH) ?? $relativePath), '/');
            \Illuminate\Support\Facades\Storage::disk('uploads')->delete($relativePath);
            $fullPath = public_path('uploads/' . $relativePath);
            if (file_exists($fullPath) && is_file($fullPath)) {
                @unlink($fullPath);
            }
        }
    }

    public function getBultyCommissionAttribute()
    {
        return $this->bilty_commission;
    }

    public function setBultyCommissionAttribute($value)
    {
        $this->bilty_commission = $value;
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function tollInvoice()
    {
        return $this->belongsTo(Invoice::class, 'toll_invoice_id');
    }
}
