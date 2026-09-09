<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payroll;
use App\Models\Staff;
use App\Models\EmployeeAdvance;
use Barryvdh\DomPDF\Facade\Pdf;
use Validator, Exception, DB;
use Carbon\Carbon;

class PayrollController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view payrolls')->only(['index', 'show', 'downloadPayslip']);
        $this->middleware('permission:create payrolls')->only(['create', 'store', 'generate', 'processSinglePayroll', 'bulkGenerate']);
        $this->middleware('permission:edit payrolls')->only(['edit', 'update', 'updateStatus', 'markAsPaid']);
        $this->middleware('permission:delete payrolls')->only('destroy');
    }

    public function index()
    {
        $storeId = auth()->user()->store_id;
        $isAdmin = auth()->user()->hasRole('Admin');

        $payrolls = Payroll::with('staff')
            ->whereHas('staff', function ($q) use ($storeId, $isAdmin) {
                if ($storeId && !$isAdmin) {
                    $q->where('store_id', $storeId);
                }
            })
            ->latest()->paginate(10);

        return view('admin.payrolls.index', compact('payrolls'));
    }

    public function create()
    {
        $storeId = auth()->user()->store_id;
        $isAdmin = auth()->user()->hasRole('Admin');

        $staffs = Staff::where('status', true)
            ->when($storeId && !$isAdmin, fn($q) => $q->where('store_id', $storeId))
            ->get();

        return view('admin.payrolls.create', compact('staffs'));
    }

    public function calculate(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'staff_id' => 'required|exists:staffs,id',
                'month' => 'required|numeric|min:1|max:12',
                'year' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => 'Invalid data'], 422);
            }

            $staff = Staff::findOrFail($request->staff_id);
            $basicSalary = $request->filled('basic_salary') && $request->basic_salary !== '' 
                ? (float)$request->basic_salary 
                : (float)($staff->salary ?? 0);
            
            $daysInMonth = Carbon::createFromDate($request->year, $request->month, 1)->daysInMonth;
            $perDaySalary = $basicSalary > 0 ? ($basicSalary / $daysInMonth) : 0;
            
            $attendances = $staff->attendances()
                ->whereMonth('date', $request->month)
                ->whereYear('date', $request->year)
                ->get();
                
            $presentCount = $attendances->where('status', 'present')->count();
            $lateCount = $attendances->where('status', 'late')->count();
            $absentCount = $attendances->where('status', 'absent')->count();
            $leaveCount = $attendances->where('status', 'leave')->count();
            $halfDayCount = $attendances->where('status', 'half_day')->count();
            $holidayCount = $attendances->where('status', 'holiday')->count();
            
            $paidDays = $attendances->whereIn('status', ['present', 'late', 'holiday', 'leave'])->count();
            $totalPayableDays = $paidDays + ($halfDayCount * 0.5);
            
            if ($basicSalary > 0) {
                $deductions = $basicSalary - ($totalPayableDays * $perDaySalary);
                if ($deductions < 0) $deductions = 0;
            } else {
                $deductions = 0;
            }

            $advancesQuery = EmployeeAdvance::where('staff_id', $request->staff_id)
                ->where('deduction_month', $request->month)
                ->where('deduction_year', $request->year)
                ->where('status', '!=', 'cancelled');

            $advances = $advancesQuery->get();
            $advanceAmount = (float)$advances->sum('amount');

            $advanceList = $advances->map(function ($adv) {
                return [
                    'id' => $adv->id,
                    'amount' => (float)$adv->amount,
                    'date' => Carbon::parse($adv->advance_date)->format('d M, Y'),
                    'reason' => $adv->reason ?: 'Salary Advance',
                    'status' => $adv->status,
                ];
            });

            return response()->json([
                'basic_salary' => round($basicSalary, 2),
                'per_day_salary' => round($perDaySalary, 2),
                'deductions' => round($deductions, 2),
                'advance_amount' => round($advanceAmount, 2),
                'total_deductions' => round($deductions + $advanceAmount, 2),
                'net_amount' => round(max(0, $basicSalary - $deductions - $advanceAmount), 2),
                'attendance_summary' => [
                    'present' => $presentCount,
                    'absent' => $absentCount,
                    'leave' => $leaveCount,
                    'late' => $lateCount,
                    'half_day' => $halfDayCount,
                    'holiday' => $holidayCount,
                    'days_in_month' => $daysInMonth,
                    'total_payable_days' => $totalPayableDays,
                    'recorded_days' => $attendances->count(),
                ],
                'advances' => $advanceList,
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function generateAll(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'month' => 'required|numeric|min:1|max:12',
                'year' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator->errors());
            }

            $storeId = auth()->user()->store_id;
            $isAdmin = auth()->user()->hasRole('Admin');

            $staffs = Staff::where('status', true)
                ->when($storeId && !$isAdmin, fn($q) => $q->where('store_id', $storeId))
                ->get();

            $generatedCount = 0;
            $daysInMonth = Carbon::createFromDate($request->year, $request->month, 1)->daysInMonth;

            foreach ($staffs as $staff) {
                // Check if payroll already exists
                $exists = Payroll::where('staff_id', $staff->id)
                    ->where('month', $request->month)
                    ->where('year', $request->year)
                    ->exists();

                if ($exists) continue;

                $basicSalary = $staff->salary ?? 0;
                $deductions = 0;

                if ($basicSalary > 0) {
                    $perDaySalary = $basicSalary / $daysInMonth;
                    
                    $attendances = $staff->attendances()
                        ->whereMonth('date', $request->month)
                        ->whereYear('date', $request->year)
                        ->get();
                        
                    $paidDays = $attendances->whereIn('status', ['present', 'late', 'holiday', 'leave'])->count();
                    $halfDays = $attendances->where('status', 'half_day')->count();
                    
                    $totalPayableDays = $paidDays + ($halfDays * 0.5);
                    
                    $deductions = $basicSalary - ($totalPayableDays * $perDaySalary);
                    if ($deductions < 0) $deductions = 0;
                }

                $advanceAmount = EmployeeAdvance::where('staff_id', $staff->id)
                    ->where('deduction_month', $request->month)
                    ->where('deduction_year', $request->year)
                    ->where('status', '!=', 'cancelled')
                    ->sum('amount');

                $netAmount = max(0, $basicSalary - $deductions - $advanceAmount);

                $payroll = Payroll::create([
                    'staff_id' => $staff->id,
                    'month' => $request->month,
                    'year' => $request->year,
                    'basic_salary' => $basicSalary,
                    'deductions' => $deductions,
                    'advance_amount' => $advanceAmount,
                    'net_amount' => $netAmount,
                    'status' => 'pending',
                ]);
                $payroll->createExpenseRecord();

                if ($advanceAmount > 0) {
                    $pendingAdvances = EmployeeAdvance::where('staff_id', $staff->id)
                        ->where('deduction_month', $request->month)
                        ->where('deduction_year', $request->year)
                        ->where('status', '!=', 'cancelled')
                        ->get();

                    foreach ($pendingAdvances as $adv) {
                        $adv->update(['status' => 'deducted']);
                        $adv->createExpenseRecord();
                    }
                }

                $generatedCount++;
            }

            if ($generatedCount > 0) {
                logActivity('Created', 'Payroll', "Generated bulk payrolls for {$generatedCount} staff members.");
                return redirect()->back()->with('success', "Successfully generated {$generatedCount} payrolls.");
            } else {
                return redirect()->back()->with('warning', 'No new payrolls were generated. Staff may already have payrolls for this month.');
            }

        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'staff_id' => 'required|exists:staffs,id',
                'month' => 'required',
                'year' => 'required',
                'basic_salary' => 'required|numeric',
                'deductions' => 'nullable|numeric',
                'advance_amount' => 'nullable|numeric',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors($validator->errors());
            }

            $staff = Staff::find($request->staff_id);
            $advanceAmount = $request->filled('advance_amount') ? (float)$request->advance_amount : EmployeeAdvance::where('staff_id', $request->staff_id)
                ->where('deduction_month', $request->month)
                ->where('deduction_year', $request->year)
                ->where('status', '!=', 'cancelled')
                ->sum('amount');

            $deductions = (float)($request->deductions ?? 0);
            $netAmount = max(0, (float)$request->basic_salary - $deductions - $advanceAmount);

            $payroll = Payroll::create([
                'staff_id' => $request->staff_id,
                'month' => $request->month,
                'year' => $request->year,
                'basic_salary' => $request->basic_salary,
                'deductions' => $deductions,
                'advance_amount' => $advanceAmount,
                'net_amount' => $netAmount,
                'status' => 'pending',
            ]);
            $payroll->createExpenseRecord();

            if ($advanceAmount > 0) {
                $pendingAdvances = EmployeeAdvance::where('staff_id', $request->staff_id)
                    ->where('deduction_month', $request->month)
                    ->where('deduction_year', $request->year)
                    ->where('status', '!=', 'cancelled')
                    ->get();

                foreach ($pendingAdvances as $adv) {
                    $adv->update(['status' => 'deducted']);
                    $adv->createExpenseRecord();
                }
            }

            logActivity('Created', 'Payroll', "Created payroll for {$staff->full_name}");
            return redirect()->route('admin.payrolls.index')->with('success', 'Payroll created successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function markPaid($id)
    {
        try {
            $payroll = Payroll::findOrFail($id);
            $payroll->update([
                'status' => 'paid',
                'payment_date' => now(),
            ]);
            $payroll->createExpenseRecord();

            return response()->json(['success' => true, 'message' => 'Payroll marked as paid']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function bulkMarkPaid(Request $request)
    {
        try {
            $ids = $request->ids;
            if (!$ids || !is_array($ids)) {
                return response()->json(['success' => false, 'message' => 'No payrolls selected']);
            }

            $payrolls = Payroll::whereIn('id', $ids)->get();
            foreach ($payrolls as $payroll) {
                $payroll->update([
                    'status' => 'paid',
                    'payment_date' => now(),
                ]);
                $payroll->createExpenseRecord();
            }

            logActivity('Updated', 'Payroll', 'Bulk marked payrolls as paid');
            return response()->json(['success' => true, 'message' => 'Selected payrolls marked as paid']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function downloadPayslip($id)
    {
        $payroll = Payroll::with('staff')->findOrFail($id);
        $pdf = Pdf::loadView('admin.payrolls.payslip', compact('payroll'));
        return $pdf->download("payslip-{$payroll->staff->full_name}-{$payroll->month}-{$payroll->year}.pdf");
    }

    public function edit($id)
    {
        $payroll = Payroll::with('staff')->findOrFail($id);
        $storeId = auth()->user()->store_id;
        $isAdmin = auth()->user()->hasRole('Admin');

        $staffs = Staff::where('status', true)
            ->when($storeId && !$isAdmin, fn($q) => $q->where('store_id', $storeId))
            ->get();

        return view('admin.payrolls.edit', compact('payroll', 'staffs'));
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'staff_id' => 'required|exists:staffs,id',
                'month' => 'required|numeric|min:1|max:12',
                'year' => 'required|numeric',
                'basic_salary' => 'required|numeric|min:0',
                'deductions' => 'nullable|numeric|min:0',
                'advance_amount' => 'nullable|numeric|min:0',
                'status' => 'required|in:pending,paid',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors($validator->errors());
            }

            $payroll = Payroll::findOrFail($id);
            $basicSalary = (float)$request->basic_salary;
            $deductions = (float)($request->deductions ?? 0);
            $advanceAmount = (float)($request->advance_amount ?? 0);
            $netAmount = max(0, $basicSalary - $deductions - $advanceAmount);

            $payroll->update([
                'staff_id' => $request->staff_id,
                'month' => $request->month,
                'year' => $request->year,
                'basic_salary' => $basicSalary,
                'deductions' => $deductions,
                'advance_amount' => $advanceAmount,
                'net_amount' => $netAmount,
                'status' => $request->status,
                'payment_date' => $request->status === 'paid' ? ($payroll->payment_date ?? now()) : null,
            ]);
            $payroll->createExpenseRecord();

            logActivity('Updated', 'Payroll', "Updated payroll for staff #{$payroll->staff_id}");
            return redirect()->route('admin.payrolls.index')->with('success', 'Payroll updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            Payroll::findOrFail($id)->delete();
            return response()->json(['success' => true, 'message' => 'Payroll deleted successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
