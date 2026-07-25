<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TyreBrand extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tyre_brands';

    protected $fillable = [
        'name',
        'code',
        'description',
        'status',
    ];

    public function models()
    {
        return $this->hasMany(TyreModel::class, 'tyre_brand_id');
    }

    public function sizes()
    {
        return $this->hasMany(TyreSize::class, 'tyre_brand_id');
    }
}
