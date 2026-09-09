<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'category_id', 'service_name', 'description', 'duration', 'price', 'status'
    ];

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }


    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }
}
