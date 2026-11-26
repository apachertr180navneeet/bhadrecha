<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankMaster extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bank_masters';

    protected $fillable = [
        'name',
        'code',
        'status',
    ];

    public function branches()
    {
        return $this->hasMany(BankBranchMaster::class, 'bank_id');
    }
}
