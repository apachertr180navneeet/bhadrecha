<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'store_name', 'phone', 'email', 'address', 'status'
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function staff()
    {
        return $this->hasMany(Staff::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function getNameAttribute()
    {
        return $this->attributes['store_name'] ?? ($this->attributes['name'] ?? '');
    }
}
