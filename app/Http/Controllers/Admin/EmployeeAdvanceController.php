<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmployeeAdvance;
use App\Models\Staff;
use App\Models\Store;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Validator;

class EmployeeAdvanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view advances')->only(['index', 'show', 'ajaxSearchStaffs']);
        $this->middleware('permission:create advances')->only(['create', 'store']);
        $this->middleware('permission:edit advances')->only(['edit', 'update']);
        $this->middleware('permission:delete advances')->only('destroy');
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $userStoreId = $user->store_id;
        $canViewAllStores = $user->hasRole('Admin')
            || $user->hasRole('Super Admin')
            || in_array(strtolower($user->role ?? ''), ['admin', 'super_admin'])
            || $user->can('view stores');

        $selectedStoreId = ($userStoreId && !$canViewAllStores) ? $userStoreId : $request->get('store_id');

        $query = EmployeeAdvance::with(['staff', 'store'])
            ->when($selectedStoreId, function ($q) use ($selectedStoreId) {
                return $q->where('store_id', $selectedStoreId);
            });

        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        if ($request->filled('deduction_month')) {
            $query->where('deduction_month', $request->deduction_month);
        }

        if ($request->filled('deduction_year')) {
            $query->where('deduction_year', $request->deduction_year);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $advances = (clone $query)->orderBy('id', 'desc')->paginate(15)->withQueryString();

        // Stats summary
        $totalAdvance = (clone $query)->sum('amount');
        $totalPending = (clone $query)->where('status', 'pending')->sum('amount');
        $totalDeducted = (clone $query)->where('status', 'deducted')->sum('amount');

        $staffs = Staff::where('status', true)
            ->when($selectedStoreId, fn($q) => $q->where('store_id', $selectedStoreId))
            ->get();

        $stores = Store::all();

        return view('admin.advances.index', compact(
            'advances', 'staffs', 'stores', 'totalAdvance', 'totalPending', 'totalDeducted', 'selectedStoreId', 'canViewAllStores'
        ));
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'staff_id' => 'required|exists:staffs,id',
                'amount' => 'required|numeric|min:1',
                'advance_date' => 'required|date',
                'deduction_month' => 'required|integer|min:1|max:12',
                'deduction_year' => 'required|integer|min:2020|max:2099',
                'reason' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $staff = Staff::findOrFail($request->staff_id);
            $storeId = $request->store_id ?? $staff->store_id ?? auth()->user()->store_id;

            $advance = EmployeeAdvance::create([
                'store_id' => $storeId,
                'staff_id' => $request->staff_id,
                'amount' => $request->amount,
                'advance_date' => $request->advance_date,
                'deduction_month' => $request->deduction_month,
                'deduction_year' => $request->deduction_year,
                'reason' => $request->reason,
                'status' => 'pending',
            ]);

            logActivity('Created', 'EmployeeAdvance', "Added advance of ₹{$advance->amount} for {$staff->full_name}");

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Employee advance recorded successfully']);
            }

            return redirect()->route('admin.advances.index')->with('success', 'Employee advance recorded successfully');
        } catch (Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $advance = EmployeeAdvance::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'staff_id' => 'required|exists:staffs,id',
                'amount' => 'required|numeric|min:1',
                'advance_date' => 'required|date',
                'deduction_month' => 'required|integer|min:1|max:12',
                'deduction_year' => 'required|integer|min:2020|max:2099',
                'status' => 'required|in:pending,deducted,cancelled',
                'reason' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $staff = Staff::findOrFail($request->staff_id);
            $oldStatus = $advance->status;

            $advance->update([
                'staff_id' => $request->staff_id,
                'amount' => $request->amount,
                'advance_date' => $request->advance_date,
                'deduction_month' => $request->deduction_month,
                'deduction_year' => $request->deduction_year,
                'status' => $request->status,
                'reason' => $request->reason,
            ]);

            if ($oldStatus !== 'deducted' && $request->status === 'deducted') {
                $advance->createExpenseRecord();
            }

            logActivity('Updated', 'EmployeeAdvance', "Updated advance #{$advance->id} for {$staff->full_name}");

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Employee advance updated successfully']);
            }

            return redirect()->route('admin.advances.index')->with('success', 'Employee advance updated successfully');
        } catch (Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $advance = EmployeeAdvance::findOrFail($id);
            $advance->delete();

            logActivity('Deleted', 'EmployeeAdvance', "Deleted advance #{$id}");

            return response()->json(['success' => true, 'message' => 'Employee advance deleted successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $advance = EmployeeAdvance::findOrFail($id);
            $request->validate(['status' => 'required|in:pending,deducted,cancelled']);
            
            $oldStatus = $advance->status;
            $advance->update(['status' => $request->status]);

            if ($oldStatus !== 'deducted' && $request->status === 'deducted') {
                $advance->createExpenseRecord();
            }

            return response()->json(['success' => true, 'message' => 'Status updated successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function ajaxSearchStaffs(Request $request)
    {
        $q = trim($request->get('q', ''));
        $storeId = $request->get('store_id');

        $staffs = Staff::where(function ($s) {
                $s->where('status', true)->orWhere('status', 1);
            })
            ->when($storeId, function ($query) use ($storeId) {
                $query->where('store_id', $storeId);
            })
            ->when($q, function ($query) use ($q) {
                $query->where(function($sub) use ($q) {
                    $sub->where('first_name', 'LIKE', "%{$q}%")
                        ->orWhere('last_name', 'LIKE', "%{$q}%")
                        ->orWhere('phone', 'LIKE', "%{$q}%")
                        ->orWhere('position', 'LIKE', "%{$q}%")
                        ->orWhere(\Illuminate\Support\Facades\DB::raw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))"), 'LIKE', "%{$q}%");
                });
            })
            ->limit(50)
            ->get();

        $results = $staffs->map(function ($st) {
            $fullName = trim(($st->first_name ?? '') . ' ' . ($st->last_name ?? ''));
            $name = !empty($fullName) ? $fullName : ($st->name ?? 'Staff #' . $st->id);
            return [
                'id' => $st->id,
                'name' => $name,
                'position' => $st->position ?? '',
                'phone' => $st->phone ?? '',
                'salary' => (float)($st->salary ?? 0),
                'store_id' => $st->store_id,
                'text' => $name . ($st->position ? ' (' . $st->position . ')' : ''),
            ];
        });

        return response()->json($results);
    }
}
