<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payroll extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'staff_id', 'month', 'year', 'basic_salary',
        'deductions', 'advance_amount', 'net_amount', 'payment_date', 'status'
    ];

    protected $casts = [
        'payment_date' => 'date',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Auto create expense when payroll/salary is generated or paid
     */
    public function createExpenseRecord()
    {
        $staff = $this->staff ?? Staff::find($this->staff_id);
        $staffName = $staff ? $staff->full_name : 'Employee';

        $monthName = date('F', mktime(0, 0, 0, (int)$this->month, 1));
        $remarkText = "{$monthName} salary - {$staffName}";

        // Ensure category 7 exists or create
        $category = ExpenseCategory::find(7);
        if (!$category) {
            $category = ExpenseCategory::create([
                'id' => 7,
                'name' => 'Salary',
                'status' => 1,
            ]);
        }

        // Prevent duplicate expense creation
        $existing = Expense::where('category_id', 7)
            ->where('remarks', $remarkText)
            ->where('amount', $this->net_amount)
            ->first();

        if ($existing) {
            return $existing;
        }

        return Expense::create([
            'store_id' => $staff ? $staff->store_id : null,
            'branch_id' => $staff ? $staff->branch_id : null,
            'category_id' => 7,
            'amount' => $this->net_amount,
            'date' => $this->payment_date ? $this->payment_date->format('Y-m-d') : now()->toDateString(),
            'remarks' => $remarkText,
        ]);
    }
}
