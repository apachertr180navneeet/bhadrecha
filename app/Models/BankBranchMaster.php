<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankBranchMaster extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bank_branch_masters';

    protected $fillable = [
        'bank_id',
        'branch_name',
        'ifsc',
        'address',
        'status',
    ];

    public function bank()
    {
        return $this->belongsTo(BankMaster::class, 'bank_id');
    }
}
