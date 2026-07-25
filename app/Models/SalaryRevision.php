<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryRevision extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'previous_base_salary',
        'new_base_salary',
        'change_amount',
        'change_type',
        'effective_date',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'previous_base_salary' => 'decimal:2',
        'new_base_salary' => 'decimal:2',
        'change_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
