<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'store_id', 'branch_id', 'category_id', 'amount', 'date', 'remarks', 'attachment'
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }
}
