<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TyreSize extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tyre_sizes';

    protected $fillable = [
        'tyre_brand_id',
        'tyre_model_id',
        'name',
        'code',
        'description',
        'status',
    ];

    public function brand()
    {
        return $this->belongsTo(TyreBrand::class, 'tyre_brand_id');
    }

    public function model()
    {
        return $this->belongsTo(TyreModel::class, 'tyre_model_id');
    }
}
