<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashLedger;
use App\Models\Company;
use App\Models\Branch;
use App\Models\ActivityLog;
use App\Exports\CashLedgerExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashLedgerController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user || (!$user->can('view cash ledger') && !$user->can('view expenses') && !$user->isSuperAdmin())) {
            abort(403, 'Unauthorized action. You do not have permission to view the Cash Ledger.');
        }

        $companyId = $user->isSuperAdmin()
            ? ($request->filled('company_id') ? $request->company_id : session('current_company_id'))
            : $user->company_id;

        $sessionBranch = session('current_branch_id');
        $branchId = $request->filled('branch_id') 
            ? $request->branch_id 
            : (($sessionBranch && $sessionBranch !== 'all') ? $sessionBranch : null);

        $query = CashLedger::with(['company', 'branch', 'creator']);

        if ($companyId && $companyId !== 'all') {
            $query->where('company_id', $companyId);
        }

        if ($branchId && $branchId !== 'all') {
            $query->where('branch_id', $branchId);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        if ($request->filled('type') && in_array(strtolower($request->type), ['credit', 'debit'])) {
            $query->where('transaction_type', strtolower($request->type));
        }

        if ($request->filled('account_name') && $request->account_name !== 'all') {
            $query->where('account_name', $request->account_name);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function($q) use ($s) {
                $q->where('voucher_no', 'LIKE', "%{$s}%")
                  ->orWhere('description', 'LIKE', "%{$s}%")
                  ->orWhere('category', 'LIKE', "%{$s}%")
                  ->orWhere('account_name', 'LIKE', "%{$s}%")
                  ->orWhere('account_number', 'LIKE', "%{$s}%");
            });
        }

        // Summary calculations across matching records
        $statsQuery = clone $query;
        $allStats = $statsQuery->select([
            DB::raw("COALESCE(SUM(CASE WHEN LOWER(transaction_type) = 'credit' THEN amount ELSE 0 END), 0) as total_credit"),
            DB::raw("COALESCE(SUM(CASE WHEN LOWER(transaction_type) = 'debit' THEN amount ELSE 0 END), 0) as total_debit"),
            DB::raw("COUNT(*) as total_count")
        ])->first();

        $totalCredit = (float)($allStats->total_credit ?? 0);
        $totalDebit = (float)($allStats->total_debit ?? 0);
        $currentBalance = $totalCredit - $totalDebit;
        $totalCount = (int)($allStats->total_count ?? 0);

        // Account-wise breakdown
        $accountStatsQuery = clone $query;
        $accountSummaries = $accountStatsQuery->select([
            'account_name',
            DB::raw("COALESCE(SUM(CASE WHEN LOWER(transaction_type) = 'credit' THEN amount ELSE 0 END), 0) as total_credit"),
            DB::raw("COALESCE(SUM(CASE WHEN LOWER(transaction_type) = 'debit' THEN amount ELSE 0 END), 0) as total_debit"),
            DB::raw("COUNT(*) as entry_count")
        ])->groupBy('account_name')->get()->map(function($acc) {
            $acc->balance = (float)$acc->total_credit - (float)$acc->total_debit;
            return $acc;
        });

        // Unique account names for filter dropdown
        $existingAccounts = CashLedger::when($companyId && $companyId !== 'all', fn($q) => $q->where('company_id', $companyId))
            ->whereNotNull('account_name')
            ->distinct()
            ->pluck('account_name');

        if (!$existingAccounts->contains('Cash in Hand')) {
            $existingAccounts->prepend('Cash in Hand');
        }

        $sortOrder = $request->get('sort', 'asc'); // Chronological ascending by default for ledgers
        $entries = $query->orderBy('date', $sortOrder)
            ->orderBy('id', $sortOrder)
            ->paginate(25)
            ->withQueryString();

        // Calculate running balance for each entry
        // To show running balance accurately, compute opening balance prior to current page
        $runningBalance = 0;
        if ($entries->currentPage() > 1 && $sortOrder === 'asc') {
            $priorQuery = clone $query;
            $firstItem = $entries->first();
            if ($firstItem) {
                $openingStats = $priorQuery->where(function($q) use ($firstItem) {
                    $q->where('date', '<', $firstItem->date)
                      ->orWhere(function($sub) use ($firstItem) {
                          $sub->where('date', '=', $firstItem->date)
                              ->where('id', '<', $firstItem->id);
                      });
                })->select([
                    DB::raw("COALESCE(SUM(CASE WHEN LOWER(transaction_type) = 'credit' THEN amount ELSE 0 END), 0) as credit"),
                    DB::raw("COALESCE(SUM(CASE WHEN LOWER(transaction_type) = 'debit' THEN amount ELSE 0 END), 0) as debit")
                ])->first();

                $runningBalance = (float)($openingStats->credit ?? 0) - (float)($openingStats->debit ?? 0);
            }
        }

        foreach ($entries as $entry) {
            $amt = (float)$entry->amount;
            if (strtolower($entry->transaction_type) === 'credit') {
                $runningBalance += $amt;
            } else {
                $runningBalance -= $amt;
            }
            $entry->running_balance = $runningBalance;
        }

        $companies = $user->isSuperAdmin() ? Company::where('status', 'active')->get() : collect();
        $branches = Branch::where('status', 'active')
            ->when($companyId && $companyId !== 'all', fn($q) => $q->where('company_id', $companyId))
            ->when(!$user->isSuperAdmin() && !$user->isCompanyAdmin(), fn($q) => $q->whereIn('id', $user->assigned_branch_ids ?: []))
            ->orderBy('name')
            ->get();

        return view('admin.cash_ledger.index', compact(
            'entries',
            'totalCredit',
            'totalDebit',
            'currentBalance',
            'totalCount',
            'accountSummaries',
            'existingAccounts',
            'companies',
            'branches',
            'branchId',
            'companyId'
        ));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user || (!$user->can('add cash entry') && !$user->can('create cash entry') && !$user->isSuperAdmin())) {
            abort(403, 'Unauthorized action. You do not have permission to add cash entries.');
        }

        $validated = $request->validate([
            'account_name' => 'required|string|max:255',
            'account_number' => 'nullable|string|max:100',
            'transaction_type' => 'required|in:credit,debit',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'voucher_no' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:255',
            'description' => 'required|string|max:2000',
            'payment_mode' => 'nullable|string|max:50',
            'branch_id' => 'nullable|exists:branches,id',
            'company_id' => 'nullable|exists:companies,id',
        ]);

        $companyId = $validated['company_id'] ?? ($user->isSuperAdmin() ? (session('current_company_id') ?: null) : $user->company_id);
        $sessionBranch = session('current_branch_id');
        $branchId = $validated['branch_id'] ?? (($sessionBranch && $sessionBranch !== 'all') ? $sessionBranch : $user->branch_id);

        $entry = CashLedger::create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'account_name' => trim($validated['account_name']),
            'account_number' => $validated['account_number'] ?? null,
            'transaction_type' => strtolower($validated['transaction_type']),
            'amount' => $validated['amount'],
            'date' => $validated['date'],
            'voucher_no' => $validated['voucher_no'] ?? null,
            'category' => $validated['category'] ?? null,
            'description' => $validated['description'],
            'payment_mode' => $validated['payment_mode'] ?: 'Cash',
            'created_by' => $user->id,
        ]);

        ActivityLog::log('cash_ledger_entry_created', "Created {$entry->transaction_type} entry of ₹{$entry->amount} for {$entry->account_name}", $entry);

        return redirect()->route('admin.cash-ledger.index')
            ->with('success', ucfirst($entry->transaction_type) . ' entry of ₹' . number_format($entry->amount, 2) . ' recorded successfully.');
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user || (!$user->can('edit cash entry') && !$user->isSuperAdmin())) {
            abort(403, 'Unauthorized action. You do not have permission to edit cash entries.');
        }

        $entry = CashLedger::findOrFail($id);

        $validated = $request->validate([
            'account_name' => 'required|string|max:255',
            'account_number' => 'nullable|string|max:100',
            'transaction_type' => 'required|in:credit,debit',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'voucher_no' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:255',
            'description' => 'required|string|max:2000',
            'payment_mode' => 'nullable|string|max:50',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $entry->update([
            'account_name' => trim($validated['account_name']),
            'account_number' => $validated['account_number'] ?? null,
            'transaction_type' => strtolower($validated['transaction_type']),
            'amount' => $validated['amount'],
            'date' => $validated['date'],
            'voucher_no' => $validated['voucher_no'] ?? null,
            'category' => $validated['category'] ?? null,
            'description' => $validated['description'],
            'payment_mode' => $validated['payment_mode'] ?: 'Cash',
            'branch_id' => $validated['branch_id'] ?? $entry->branch_id,
        ]);

        ActivityLog::log('cash_ledger_entry_updated', "Updated cash entry #{$entry->id}", $entry);

        return redirect()->back()->with('success', 'Cash ledger entry updated successfully.');
    }

    public function destroy($id)
    {
        $user = auth()->user();
        if (!$user || (!$user->can('delete cash entry') && !$user->isSuperAdmin())) {
            abort(403, 'Unauthorized action. You do not have permission to delete cash entries.');
        }

        $entry = CashLedger::findOrFail($id);
        $desc = "Deleted {$entry->transaction_type} entry of ₹{$entry->amount} (#{$entry->id})";
        $entry->delete();

        ActivityLog::log('cash_ledger_entry_deleted', $desc);

        return redirect()->back()->with('success', 'Cash ledger entry deleted successfully.');
    }

    public function exportExcel(Request $request)
    {
        $user = auth()->user();
        if (!$user || (!$user->can('view cash ledger') && !$user->can('view expenses') && !$user->isSuperAdmin())) {
            abort(403, 'Unauthorized action.');
        }

        $companyId = $user->isSuperAdmin()
            ? ($request->filled('company_id') ? $request->company_id : session('current_company_id'))
            : $user->company_id;

        $sessionBranch = session('current_branch_id');
        $branchId = $request->filled('branch_id') 
            ? $request->branch_id 
            : (($sessionBranch && $sessionBranch !== 'all') ? $sessionBranch : null);

        $query = CashLedger::with(['company', 'branch', 'creator']);

        if ($companyId && $companyId !== 'all') {
            $query->where('company_id', $companyId);
        }

        if ($branchId && $branchId !== 'all') {
            $query->where('branch_id', $branchId);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        if ($request->filled('type') && in_array(strtolower($request->type), ['credit', 'debit'])) {
            $query->where('transaction_type', strtolower($request->type));
        }

        if ($request->filled('account_name') && $request->account_name !== 'all') {
            $query->where('account_name', $request->account_name);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function($q) use ($s) {
                $q->where('voucher_no', 'LIKE', "%{$s}%")
                  ->orWhere('description', 'LIKE', "%{$s}%")
                  ->orWhere('category', 'LIKE', "%{$s}%")
                  ->orWhere('account_name', 'LIKE', "%{$s}%");
            });
        }

        $entries = $query->orderBy('date', 'asc')->orderBy('id', 'asc')->get();

        $fileName = 'cash_ledger_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new CashLedgerExport($entries), $fileName);
    }
}
