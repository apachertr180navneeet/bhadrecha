<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployeeAdvance;
use App\Models\User;
use App\Models\SalaryPayment;
use Illuminate\Http\Request;

class AdvanceController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = EmployeeAdvance::with(['user.company', 'user.branch', 'approver']);

        if ($user->isSuperAdmin()) {
            if ($request->filled('company_id')) {
                $query->whereHas('user', fn($q) => $q->where('company_id', $request->company_id));
            }
        } elseif ($user->isCompanyAdmin()) {
            $query->whereHas('user', fn($q) => $q->where('company_id', $user->company_id));
        } else {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $advances = $query->orderBy('created_at', 'desc')->paginate(20);
        $companies = $user->isSuperAdmin() ? \App\Models\Company::where('status', 'active')->get() : collect();

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

        foreach ($advances as $advance) {
            $deducted = $deductedSoFar[$advance->id] ?? 0;
            $advance->is_cleared = $deducted >= $advance->amount;
            $advance->balance = max(0, $advance->amount - $deducted);
        }

        return view('admin.advances.index', compact('advances', 'companies'));
    }

    public function create()
    {
        $employees = collect();
        if (auth()->user()->isSuperAdmin() || auth()->user()->isCompanyAdmin()) {
            $query = User::whereDoesntHave('roles', fn($q) => $q->whereIn('name', ['Super Admin', 'Company Admin']));
            if (auth()->user()->isCompanyAdmin()) {
                $query->where('company_id', auth()->user()->company_id);
            }
            $employees = $query->orderBy('first_name')->get(['id', 'first_name', 'last_name']);
        }

        return view('admin.advances.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'deduction_type' => 'required|in:full,monthly',
            'monthly_deduction' => 'nullable|required_if:deduction_type,monthly|numeric|min:0',
            'reason' => 'required|string|max:1000',
        ]);

        $userId = auth()->id();
        if (auth()->user()->isSuperAdmin() || auth()->user()->isCompanyAdmin()) {
            $request->validate(['user_id' => 'required|exists:users,id']);
            $userId = $request->user_id;
        }

        EmployeeAdvance::create([
            'user_id' => $userId,
            'amount' => floatval($request->amount),
            'deduction_type' => $request->deduction_type,
            'monthly_deduction' => $request->deduction_type === 'monthly' ? floatval($request->monthly_deduction) : null,
            'reason' => $request->reason,
        ]);

        return redirect()->route('admin.advances.index')->with('success', 'Advance request submitted successfully.');
    }

    public function approve($id)
    {
        $advance = EmployeeAdvance::with('user')->findOrFail($id);
        $current = auth()->user();

        if ($current->isSuperAdmin()) {
            // ok
        } elseif ($current->isCompanyAdmin()) {
            if ($advance->user->company_id !== $current->company_id) {
                return back()->with('error', 'You can only approve advances for your own company.');
            }
        } else {
            return back()->with('error', 'Unauthorized.');
        }

        if ($advance->status !== 'pending') {
            return back()->with('error', 'Advance is already ' . $advance->status . '.');
        }

        $advance->update([
            'status' => 'approved',
            'approved_by' => $current->id,
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Advance approved.');
    }

    public function reject($id)
    {
        $advance = EmployeeAdvance::with('user')->findOrFail($id);
        $current = auth()->user();

        if ($current->isSuperAdmin()) {
            // ok
        } elseif ($current->isCompanyAdmin()) {
            if ($advance->user->company_id !== $current->company_id) {
                return back()->with('error', 'You can only reject advances for your own company.');
            }
        } else {
            return back()->with('error', 'Unauthorized.');
        }

        if ($advance->status !== 'pending') {
            return back()->with('error', 'Advance is already ' . $advance->status . '.');
        }

        $advance->update([
            'status' => 'rejected',
            'approved_by' => $current->id,
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Advance rejected.');
    }

    public function markPaid($id)
    {
        $advance = EmployeeAdvance::findOrFail($id);
        $current = auth()->user();

        if (!$current->isSuperAdmin() && !$current->isCompanyAdmin()) {
            return back()->with('error', 'Unauthorized.');
        }

        if ($advance->status !== 'approved') {
            return back()->with('error', 'Only approved advances can be marked as paid.');
        }

        $advance->update([
            'status' => 'paid',
            'approved_by' => $current->id,
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Advance marked as paid.');
    }
}
