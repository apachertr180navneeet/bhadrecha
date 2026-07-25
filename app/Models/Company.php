<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'phone', 'address', 'state', 'disclaimer', 'declaration', 'gst_number', 'logo', 'status',
        'bank_holder_name', 'bank_name', 'bank_account_no', 'bank_ifsc', 'bank_branch',
        'digital_signature',
    ];

    protected $appends = ['logo_url', 'digital_signature_url'];

    public function getLogoUrlAttribute()
    {
        if (!$this->logo) {
            return null;
        }
        $clean = ltrim($this->logo, '/');
        if (\Illuminate\Support\Str::startsWith($clean, ['http://', 'https://'])) {
            return $clean;
        }
        return asset(\Illuminate\Support\Str::startsWith($clean, 'uploads/') ? $clean : 'uploads/' . $clean);
    }

    public function getDigitalSignatureUrlAttribute()
    {
        if (!$this->digital_signature) {
            return null;
        }
        $clean = ltrim($this->digital_signature, '/');
        if (\Illuminate\Support\Str::startsWith($clean, ['http://', 'https://'])) {
            return $clean;
        }
        return asset(\Illuminate\Support\Str::startsWith($clean, 'uploads/') ? $clean : 'uploads/' . $clean);
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
