<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeSalary extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'base_salary',
        'hra',
        'da',
        'special_allowance',
        'pf',
        'esi',
        'professional_tax',
        'tds',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'hra' => 'decimal:2',
        'da' => 'decimal:2',
        'special_allowance' => 'decimal:2',
        'pf' => 'decimal:2',
        'esi' => 'decimal:2',
        'professional_tax' => 'decimal:2',
        'tds' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
