<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployeeLeave;
use App\Models\User;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = EmployeeLeave::with(['user.company', 'user.branch', 'user.roles', 'approver']);

        if ($user->isSuperAdmin()) {
            // Super Admin sees all
            if ($request->filled('company_id')) {
                $query->whereHas('user', fn($q) => $q->where('company_id', $request->company_id));
            }
        } elseif ($user->isCompanyAdmin()) {
            // Company Admin sees only their company's employees
            $query->whereHas('user', fn($q) => $q->where('company_id', $user->company_id));
        } else {
            // Regular employee sees only their own leaves
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('leave_type')) {
            $query->where('leave_type', $request->leave_type);
        }

        $leaves = $query->orderBy('created_at', 'desc')->paginate(20);
        $companies = $user->isSuperAdmin() ? \App\Models\Company::where('status', 'active')->get() : collect();

        return view('admin.leaves.index', compact('leaves', 'companies'));
    }

    public function create()
    {
        return view('admin.leaves.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'leave_type' => 'required|in:sick,casual,annual,other',
            'start_date' => 'required|date|before_or_equal:9999-12-31|after_or_equal:today',
            'end_date' => 'required|date|before_or_equal:9999-12-31|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
        ]);

        EmployeeLeave::create([
            'user_id' => auth()->id(),
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
        ]);

        return redirect()->route('admin.leaves.index')->with('success', 'Leave application submitted successfully.');
    }

    public function approve($id)
    {
        $leave = EmployeeLeave::with('user')->findOrFail($id);
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            // can approve any
        } elseif ($user->isCompanyAdmin()) {
            if ($leave->user->company_id !== $user->company_id) {
                return back()->with('error', 'You can only approve leaves for your own company.');
            }
        } else {
            return back()->with('error', 'Unauthorized.');
        }

        if ($leave->status !== 'pending') {
            return back()->with('error', 'Leave is already ' . $leave->status . '.');
        }

        $leave->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        $startDate = \Carbon\Carbon::parse($leave->start_date);
        $endDate = \Carbon\Carbon::parse($leave->end_date);
        
        while ($startDate->lte($endDate)) {
            if (!$startDate->isSunday()) {
                \App\Models\Attendance::updateOrCreate(
                    [
                        'user_id' => $leave->user_id,
                        'date' => $startDate->format('Y-m-d')
                    ],
                    [
                        'status' => 'leave',
                        'remarks' => 'Leave Approved'
                    ]
                );
            }
            $startDate->addDay();
        }

        return back()->with('success', 'Leave approved successfully and attendance marked.');
    }

    public function reject(Request $request, $id)
    {
        $leave = EmployeeLeave::with('user')->findOrFail($id);
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            // can reject any
        } elseif ($user->isCompanyAdmin()) {
            if ($leave->user->company_id !== $user->company_id) {
                return back()->with('error', 'You can only reject leaves for your own company.');
            }
        } else {
            return back()->with('error', 'Unauthorized.');
        }

        if ($leave->status !== 'pending') {
            return back()->with('error', 'Leave is already ' . $leave->status . '.');
        }

        $leave->update([
            'status' => 'rejected',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Leave rejected.');
    }
}
