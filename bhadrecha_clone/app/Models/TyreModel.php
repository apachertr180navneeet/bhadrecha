<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TyreModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tyre_models';

    protected $fillable = [
        'tyre_brand_id',
        'name',
        'code',
        'description',
        'status',
    ];

    public function brand()
    {
        return $this->belongsTo(TyreBrand::class, 'tyre_brand_id');
    }

    public function sizes()
    {
        return $this->hasMany(TyreSize::class, 'tyre_model_id');
    }
}
