<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceCategory extends Model
{
    use SoftDeletes;
    protected $fillable = ['name', 'description', 'status'];

    public function services()
    {
        return $this->hasMany(Service::class, 'category_id');
    }
}
