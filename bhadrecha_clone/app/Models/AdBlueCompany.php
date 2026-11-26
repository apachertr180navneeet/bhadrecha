<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdBlueCompany extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'adblue_companies';

    protected $fillable = [
        'name',
        'status',
    ];

    public function tripAdBlueDetails()
    {
        return $this->hasMany(TripAdBlueDetail::class, 'adblue_company_id');
    }

    public function payments()
    {
        return $this->hasMany(AdBlueCompanyPayment::class, 'adblue_company_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
