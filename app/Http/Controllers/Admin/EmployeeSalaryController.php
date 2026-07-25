<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use App\Models\EmployeeSalary;
use App\Models\SalaryRevision;
use App\Models\EmployeeIncentive;
use App\Models\EmployeeAdvance;
use App\Models\Attendance;
use App\Models\SalaryPayment;
use Illuminate\Http\Request;

class EmployeeSalaryController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isCompanyAdmin()) {
            return redirect()->route('admin.employee-salary.details', auth()->id());
        }

        $query = User::with(['company:id,name', 'branch:id,name', 'roles', 'salary', 'incentives' => function ($q) {
            $q->where('is_processed', false);
        }, 'advances' => function ($q) {
            $q->where('status', 'approved');
        }])
            ->whereDoesntHave('roles', function ($q) {
                $q->whereIn('name', ['Super Admin', 'Company Admin']);
            });

        if (auth()->user()->isSuperAdmin()) {
            if ($request->filled('company_id')) {
                $query->where('company_id', $request->company_id);
            }
        } else {
            $query->where('company_id', auth()->user()->company_id);
        }

        $search = $request->input('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->get();

        $payments = SalaryPayment::whereNotNull('advances_data')->get(['advances_data']);
        $deductedSoFar = [];
        foreach ($payments as $payment) {
            foreach (collect($payment->advances_data ?? []) as $ad) {
                $aid = $ad['id'] ?? null;
                if ($aid) {
                    $deductedSoFar[$aid] = ($deductedSoFar[$aid] ?? 0) + ($ad['deduction_amount'] ?? 0);
                }
            }
        }

        $employees = $users->map(function ($user) use ($deductedSoFar) {
            $role = $user->roles->first();
            $salary = $user->salary;
            $baseSalary = floatval($salary?->base_salary ?? 0);
            $allowances = floatval($salary?->hra ?? 0)
                        + floatval($salary?->da ?? 0)
                        + floatval($salary?->special_allowance ?? 0);
            $deductions = floatval($salary?->pf ?? 0)
                        + floatval($salary?->esi ?? 0)
                        + floatval($salary?->professional_tax ?? 0)
                        + floatval($salary?->tds ?? 0);
            $pendingIncentives = $user->incentives->sum('amount');
            
            $pendingAdvances = 0;
            foreach ($user->advances as $adv) {
                $deducted = $deductedSoFar[$adv->id] ?? 0;
                $remaining = $adv->amount - $deducted;
                if ($remaining <= 0) continue;

                if ($adv->deduction_type === 'monthly') {
                    $pendingAdvances += min($adv->monthly_deduction ?? 0, $remaining);
                } else {
                    $pendingAdvances += $remaining;
                }
            }

            $now = now();
            $month = intval($now->format('m'));
            $year = intval($now->format('Y'));
            $totalDaysInMonth = $now->daysInMonth;
            $workingDays = $totalDaysInMonth; // In transportation, usually all days in month are considered working days for salary calculation

            $attendances = Attendance::where('user_id', $user->id)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->get();
                
            $attendedDays = 0;
            $absentDaysCount = 0;
            $halfDaysCount = 0;
            foreach ($attendances as $a) {
                if ($a->status === 'present') {
                    $attendedDays++;
                } elseif ($a->status === 'half-day') {
                    $attendedDays += 0.5;
                    $absentDaysCount += 0.5;
                    $halfDaysCount++;
                } elseif ($a->status === 'absent') {
                    $absentDaysCount++;
                }
            }

            // Unmarked days are also considered absent
            $unmarkedDays = $workingDays - $attendances->count();
            $totalAbsent = $absentDaysCount + $unmarkedDays;

            $netPayable = $baseSalary + $allowances + floatval($pendingIncentives) - $deductions - floatval($pendingAdvances);

            return [
                'id' => $user->id,
                'employee_id' => 'EMP-' . str_pad($user->id, 4, '0', STR_PAD_LEFT),
                'name' => $user->full_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'designation' => $role ? $role->name : 'N/A',
                'company_name' => $user->company?->name ?? '-',
                'branch_name' => $user->branch?->name ?? '-',
                'joining_date' => $user->created_at->format('Y-m-d'),
                'base_salary' => $baseSalary,
                'allowances' => $allowances,
                'deductions' => $deductions,
                'status' => 'Pending',
                'avatar_color' => 'bg-label-primary',
                'net_payable' => $netPayable,
                'pending_incentives' => floatval($pendingIncentives),
                'pending_advances' => floatval($pendingAdvances),
                'working_days' => $workingDays,
                'attended_days' => $attendedDays,
                'absent_days' => $totalAbsent,
            ];
        })->toArray();

        $totalEmployeesCount = count($employees);

        return view('admin.employee-salary.employees-list', [
            'employees' => $employees,
            'stats' => [
                'total_count' => $totalEmployeesCount,
                'total_budget' => array_sum(array_column($employees, 'net_payable')),
                'paid_amount' => 0,
                'pending_amount' => array_sum(array_column($employees, 'net_payable')),
            ],
            'selected_company_id' => $request->input('company_id'),
            'search' => $search,
            'companies' => Company::where('status', 'active')->get(),
        ]);
    }

    public function details($id)
    {
        $employee = User::with(['company', 'branch', 'roles', 'salary'])->findOrFail($id);
        $role = $employee->roles->first();
        $salaryPayments = SalaryPayment::where('user_id', $id)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();
        return view('admin.employee-salary.details', compact('employee', 'role', 'salaryPayments'));
    }

    public function editStructure($id)
    {
        $employee = User::with(['company', 'branch', 'roles', 'salary'])->findOrFail($id);
        $role = $employee->roles->first();
        return view('admin.employee-salary.edit-structure', compact('employee', 'role'));
    }

    public function updateStructure(Request $request, $id)
    {
        $employee = User::findOrFail($id);

        $request->validate([
            'base_salary' => 'required|numeric|min:0',
            'hra' => 'nullable|numeric|min:0',
            'da' => 'nullable|numeric|min:0',
            'special_allowance' => 'nullable|numeric|min:0',
            'pf' => 'nullable|numeric|min:0',
            'esi' => 'nullable|numeric|min:0',
            'professional_tax' => 'nullable|numeric|min:0',
            'tds' => 'nullable|numeric|min:0',
        ]);

        $salary = EmployeeSalary::updateOrCreate(
            ['user_id' => $employee->id],
            [
                'base_salary' => floatval($request->base_salary),
                'hra' => floatval($request->hra ?? 0),
                'da' => floatval($request->da ?? 0),
                'special_allowance' => floatval($request->special_allowance ?? 0),
                'pf' => floatval($request->pf ?? 0),
                'esi' => floatval($request->esi ?? 0),
                'professional_tax' => floatval($request->professional_tax ?? 0),
                'tds' => floatval($request->tds ?? 0),
            ]
        );

        $previousBase = floatval($salary->getOriginal('base_salary') ?? 0);
        $newBase = floatval($request->base_salary);

        if (abs($newBase - $previousBase) > 0.01) {
            SalaryRevision::create([
                'user_id' => $employee->id,
                'previous_base_salary' => $previousBase,
                'new_base_salary' => $newBase,
                'change_amount' => $newBase - $previousBase,
                'change_type' => $newBase > $previousBase ? 'increment' : 'decrement',
                'effective_date' => now()->toDateString(),
                'reason' => 'Salary structure updated',
                'created_by' => auth()->id(),
            ]);
        }

        return redirect()->route('admin.employee-salary.employees-list')
            ->with('success', "Salary structure updated successfully for {$employee->full_name}!");
    }

    public function revisions($id)
    {
        $employee = User::with(['company', 'branch', 'roles', 'salary'])->findOrFail($id);
        $role = $employee->roles->first();
        $revisions = SalaryRevision::where('user_id', $id)
            ->with('creator')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.employee-salary.revisions', compact('employee', 'role', 'revisions'));
    }

    public function applyRevision(Request $request, $id)
    {
        $employee = User::findOrFail($id);

        $request->validate([
            'new_base_salary' => 'required|numeric|min:0',
            'effective_date' => "required|date|before_or_equal:9999-12-31",
            'reason' => 'required|string|max:500',
        ]);

        $salary = EmployeeSalary::firstOrCreate(['user_id' => $employee->id]);
        $previousBase = floatval($salary->base_salary);
        $newBase = floatval($request->new_base_salary);

        if (abs($newBase - $previousBase) < 0.01) {
            return back()->with('error', 'New salary is the same as the current salary.');
        }

        $salary->update(['base_salary' => $newBase]);

        SalaryRevision::create([
            'user_id' => $employee->id,
            'previous_base_salary' => $previousBase,
            'new_base_salary' => $newBase,
            'change_amount' => $newBase - $previousBase,
            'change_type' => $newBase > $previousBase ? 'increment' : 'decrement',
            'effective_date' => $request->effective_date,
            'reason' => $request->reason,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.employee-salary.revisions', $employee->id)
            ->with('success', "Salary revision applied for {$employee->full_name}!");
    }

    public function storeIncentive(Request $request, $id)
    {
        $employee = User::findOrFail($id);

        $request->validate([
            'amount' => 'required|numeric|min:0',
            'reason' => 'nullable|string|max:500',
        ]);

        EmployeeIncentive::create([
            'user_id' => $employee->id,
            'amount' => floatval($request->amount),
            'reason' => $request->reason,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.employee-salary.details', $employee->id)
            ->with('success', "Incentive of ₹{$request->amount} added for {$employee->full_name}!");
    }

    public function processSalary(Request $request, $id)
    {
        $employee = User::findOrFail($id);

        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020',
            'base_salary' => 'required|numeric|min:0',
            'allowances' => 'required|numeric',
            'deductions' => 'required|numeric',
            'incentives_total' => 'required|numeric|min:0',
            'advance_deduction' => 'nullable|numeric|min:0',
            'working_days' => 'nullable|integer|min:0',
            'attended_days' => 'nullable|numeric|min:0',
        ]);

        $existing = SalaryPayment::where('user_id', $id)
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->exists();

        if ($existing) {
            return response()->json(['error' => 'Salary already processed for this month.'], 422);
        }

        $advanceDeduction = floatval($request->advance_deduction ?? 0);
        $workingDays = intval($request->working_days ?? 1);
        $attendedDays = floatval($request->attended_days ?? 0);
        $baseSalary = floatval($request->base_salary);
        $perDayRate = $baseSalary / max($workingDays, 1);
        $attendanceSalary = round($perDayRate * $attendedDays);

        $netPayable = $attendanceSalary
                    + floatval($request->allowances)
                    + floatval($request->incentives_total)
                    - floatval($request->deductions)
                    - $advanceDeduction;

        $payments = SalaryPayment::whereNotNull('advances_data')->get(['advances_data']);
        $deductedSoFar = [];
        foreach ($payments as $paymentRecord) {
            foreach (collect($paymentRecord->advances_data ?? []) as $ad) {
                $aid = $ad['id'] ?? null;
                if ($aid) {
                    $deductedSoFar[$aid] = ($deductedSoFar[$aid] ?? 0) + ($ad['deduction_amount'] ?? 0);
                }
            }
        }

        $pendingAdvancesList = EmployeeAdvance::where('user_id', $id)
            ->where('status', 'approved')
            ->orderBy('created_at', 'asc')
            ->get();

        $advancesDataToSave = [];
        $remainingDeductionToDistribute = $advanceDeduction;

        foreach ($pendingAdvancesList as $adv) {
            if ($remainingDeductionToDistribute <= 0) break;

            $deducted = $deductedSoFar[$adv->id] ?? 0;
            $remaining = $adv->amount - $deducted;
            
            if ($remaining <= 0) continue;

            $deductThisAdvance = min($remaining, $remainingDeductionToDistribute);

            if ($deductThisAdvance > 0) {
                $advancesDataToSave[] = [
                    'id' => $adv->id,
                    'deduction_amount' => $deductThisAdvance
                ];
                $remainingDeductionToDistribute -= $deductThisAdvance;
            }
        }

        $payment = SalaryPayment::create([
            'user_id' => $employee->id,
            'month' => intval($request->month),
            'year' => intval($request->year),
            'base_salary' => floatval($request->base_salary),
            'allowances' => floatval($request->allowances),
            'deductions' => floatval($request->deductions),
            'incentives_total' => floatval($request->incentives_total),
            'advance_deduction' => $advanceDeduction,
            'working_days' => $workingDays,
            'attended_days' => $attendedDays,
            'per_day_rate' => $perDayRate,
            'attendance_salary' => $attendanceSalary,
            'net_payable' => $netPayable,
            'status' => 'paid',
            'processed_at' => now(),
            'advances_data' => count($advancesDataToSave) > 0 ? $advancesDataToSave : null,
            'created_by' => auth()->id(),
        ]);

        EmployeeIncentive::where('user_id', $id)
            ->where('is_processed', false)
            ->update(['is_processed' => true, 'salary_payment_id' => $payment->id]);

        foreach ($advancesDataToSave as $advData) {
            $adv = EmployeeAdvance::find($advData['id']);
            if ($adv) {
                $deducted = ($deductedSoFar[$adv->id] ?? 0) + $advData['deduction_amount'];
                if ($deducted >= $adv->amount) {
                    $adv->update(['status' => 'paid', 'salary_payment_id' => $payment->id]);
                } else {
                    $adv->update(['salary_payment_id' => $payment->id]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Salary processed for {$employee->full_name} for month {$request->month}/{$request->year}.",
            'net_payable' => $netPayable,
        ]);
    }
}
