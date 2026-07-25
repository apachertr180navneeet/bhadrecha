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
}
