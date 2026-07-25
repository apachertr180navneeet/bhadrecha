<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyLoan;
use App\Models\BankMaster;
use App\Models\LoanPayment;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class CompanyLoanController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = CompanyLoan::with(['company', 'bank', 'branch', 'payments']);

        if (!$user->isSuperAdmin()) {
            $query->where('company_id', $user->company_id);
        } elseif ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('loan_id', 'like', "%{$search}%")
                  ->orWhereHas('bank', function($qq) use ($search) {
                      $qq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $loans = $query->orderBy('created_at', 'desc')->paginate(15);
        $companies = $user->isSuperAdmin()
            ? Company::where('status', 'active')->orderBy('name')->get()
            : collect([]);

        return view('admin.loan.company-loan.index', compact('loans', 'companies'));
    }

    public function create()
    {
        $user = auth()->user();
        $banks = BankMaster::where('status', 'active')->orderBy('name')->get();
        $companies = $user->isSuperAdmin()
            ? Company::where('status', 'active')->orderBy('name')->get()
            : collect([]);

        return view('admin.loan.company-loan.create', compact('banks', 'companies'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $validated = $request->validate([
            'company_id' => $user->isSuperAdmin() ? 'required|exists:companies,id' : 'nullable',
            'bank_id' => 'required|exists:bank_masters,id',
            'branch_id' => 'required|exists:bank_branch_masters,id',
            'loan_id' => 'required|string|max:255|unique:company_loans,loan_id',
            'tenure_months' => 'required|integer|min:1',
            'given_emi_count' => 'required|integer|min:0',
            'loan_amount' => 'required|numeric|min:0',
            'tenure_calculation' => 'nullable|string',
            'interest_rate' => 'required|numeric|min:0',
            'given_amount' => 'required|numeric|min:0',
            'emi_amount' => 'required|numeric|min:0',
            'pending_emi_date' => "nullable|date|before_or_equal:9999-12-31",
        ]);

        $validated['company_id'] = $validated['company_id'] ?? $user->company_id;
        $validated['total_interest'] = $request->total_interest ?? 0;
        $validated['remaining_amount'] = ($validated['loan_amount'] + $validated['total_interest']) - ($validated['given_emi_count'] * $validated['emi_amount']);

        CompanyLoan::create($validated);

        return redirect()->route('admin.loan.company-loan.index')
            ->with('success', 'Company loan created successfully.');
    }

    public function edit(CompanyLoan $companyLoan)
    {
        $user = auth()->user();
        $banks = BankMaster::where('status', 'active')->orderBy('name')->get();
        $companies = $user->isSuperAdmin()
            ? Company::where('status', 'active')->orderBy('name')->get()
            : collect([]);

        return view('admin.loan.company-loan.edit', compact('companyLoan', 'banks', 'companies'));
    }

    public function update(Request $request, CompanyLoan $companyLoan)
    {
        $user = auth()->user();
        $validated = $request->validate([
            'company_id' => $user->isSuperAdmin() ? 'required|exists:companies,id' : 'nullable',
            'bank_id' => 'required|exists:bank_masters,id',
            'branch_id' => 'required|exists:bank_branch_masters,id',
            'loan_id' => 'required|string|max:255|unique:company_loans,loan_id,' . $companyLoan->id,
            'tenure_months' => 'required|integer|min:1',
            'given_emi_count' => 'required|integer|min:0',
            'loan_amount' => 'required|numeric|min:0',
            'tenure_calculation' => 'nullable|string',
            'interest_rate' => 'required|numeric|min:0',
            'given_amount' => 'required|numeric|min:0',
            'emi_amount' => 'required|numeric|min:0',
            'pending_emi_date' => "nullable|date|before_or_equal:9999-12-31",
        ]);

        $validated['company_id'] = $validated['company_id'] ?? $user->company_id;
        $validated['total_interest'] = $request->total_interest ?? 0;
        $validated['remaining_amount'] = ($validated['loan_amount'] + $validated['total_interest'])
            - max($validated['given_emi_count'] * $validated['emi_amount'], $companyLoan->payments()->sum('amount'));

        $companyLoan->update($validated);

        return redirect()->route('admin.loan.company-loan.index')
            ->with('success', 'Company loan updated successfully.');
    }

    public function destroy(CompanyLoan $companyLoan)
    {
        $companyLoan->delete();

        return redirect()->route('admin.loan.company-loan.index')
            ->with('success', 'Company loan deleted successfully.');
    }

    public function toggleStatus(CompanyLoan $companyLoan)
    {
        $statuses = ['active', 'inactive', 'closed'];
        $currentIndex = array_search($companyLoan->status, $statuses);
        $companyLoan->status = $statuses[($currentIndex + 1) % count($statuses)];
        $companyLoan->save();

        ActivityLog::log('company_loan_status_changed', "Changed status of loan: {$companyLoan->loan_id}", $companyLoan);
        return back()->with('success', 'Company loan status updated.');
    }

    public function getBranches($bankId)
    {
        $branches = \App\Models\BankBranchMaster::where('bank_id', $bankId)
            ->where('status', 'active')
            ->orderBy('branch_name')
            ->get(['id', 'branch_name', 'ifsc']);

        return response()->json($branches);
    }

    public function recordPayment(Request $request, CompanyLoan $companyLoan)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => "required|date|before_or_equal:9999-12-31",
            'notes' => 'nullable|string|max:500',
        ]);

        $validated['company_loan_id'] = $companyLoan->id;

        LoanPayment::create($validated);

        $companyLoan->increment('given_emi_count');
        $companyLoan->decrement('remaining_amount', $validated['amount']);

        if ($companyLoan->remaining_amount <= 0) {
            $companyLoan->update(['status' => 'closed', 'remaining_amount' => 0]);
        }

        ActivityLog::log('loan_payment_recorded', "Payment of {$validated['amount']} recorded for loan: {$companyLoan->loan_id}", $companyLoan);
        return back()->with('success', 'Payment recorded successfully.');
    }

    public function payments(CompanyLoan $companyLoan)
    {
        $payments = $companyLoan->payments()->orderBy('payment_date', 'desc')->get();
        return response()->json($payments);
    }
}
