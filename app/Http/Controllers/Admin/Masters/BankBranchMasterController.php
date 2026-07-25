<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Controller;
use App\Models\BankBranchMaster;
use App\Models\BankMaster;
use App\Models\ActivityLog;
use App\Imports\BankBranchMasterImport;
use App\Exports\BankBranchMasterTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class BankBranchMasterController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user() || !auth()->user()->isSuperAdmin()) {
                abort(403);
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $query = BankBranchMaster::with('bank');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('branch_name', 'like', "%{$search}%")
                  ->orWhere('ifsc', 'like', "%{$search}%");
            });
        }

        if ($request->filled('bank_id')) {
            $query->where('bank_id', $request->bank_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $branches = $query->orderBy('branch_name')->paginate(15);
        $banks = BankMaster::where('status', 'active')->orderBy('name')->get();

        return view('admin.masters.bank-branches.index', compact('branches', 'banks'));
    }

    public function create()
    {
        $banks = BankMaster::where('status', 'active')->orderBy('name')->get();
        return view('admin.masters.bank-branches.create', compact('banks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bank_id' => 'required|exists:bank_masters,id',
            'branch_name' => 'required|string|max:255',
            'ifsc' => 'required|string|max:20|unique:bank_branch_masters,ifsc',
            'address' => 'nullable|string|max:500',
        ]);

        BankBranchMaster::create($validated + ['status' => 'active']);

        return redirect()->route('admin.masters.bank-branches.index')
            ->with('success', 'Bank branch created successfully.');
    }

    public function edit(BankBranchMaster $bankBranch)
    {
        $banks = BankMaster::where('status', 'active')->orderBy('name')->get();
        return view('admin.masters.bank-branches.edit', compact('bankBranch', 'banks'));
    }

    public function update(Request $request, BankBranchMaster $bankBranch)
    {
        $validated = $request->validate([
            'bank_id' => 'required|exists:bank_masters,id',
            'branch_name' => 'required|string|max:255',
            'ifsc' => 'required|string|max:20|unique:bank_branch_masters,ifsc,' . $bankBranch->id,
            'address' => 'nullable|string|max:500',
        ]);

        $bankBranch->update($validated);

        return redirect()->route('admin.masters.bank-branches.index')
            ->with('success', 'Bank branch updated successfully.');
    }

    public function destroy(BankBranchMaster $bankBranch)
    {
        $bankBranch->delete();
        ActivityLog::log('bank_branch_deleted', "Deleted bank branch: {$bankBranch->branch_name}");
        return redirect()->route('admin.masters.bank-branches.index')
            ->with('success', 'Bank branch deleted successfully.');
    }

    public function trashed()
    {
        $branches = BankBranchMaster::onlyTrashed()->with('bank')->orderBy('deleted_at', 'desc')->paginate(15);
        return view('admin.masters.bank-branches.trashed', compact('branches'));
    }

    public function restore($id)
    {
        $branch = BankBranchMaster::withTrashed()->findOrFail($id);
        $branch->restore();
        ActivityLog::log('bank_branch_restored', "Restored bank branch: {$branch->branch_name}");
        return redirect()->route('admin.masters.bank-branches.trashed')->with('success', 'Bank branch restored successfully.');
    }

    public function forceDelete($id)
    {
        $branch = BankBranchMaster::withTrashed()->findOrFail($id);
        ActivityLog::log('bank_branch_force_deleted', "Force deleted bank branch: {$branch->branch_name}");
        $branch->forceDelete();
        return redirect()->route('admin.masters.bank-branches.trashed')->with('success', 'Bank branch permanently deleted.');
    }

    public function toggleStatus(BankBranchMaster $bankBranch)
    {
        $bankBranch->status = $bankBranch->status === 'active' ? 'inactive' : 'active';
        $bankBranch->save();
        ActivityLog::log('bank_branch_status_changed', "Changed status of branch: {$bankBranch->branch_name}");
        return back()->with('success', 'Bank branch status updated.');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);

        $import = new BankBranchMasterImport;
        try {
            Excel::import($import, $request->file('file'));
            $imported = $import->getImportedCount();
            $skipped = $import->getSkippedCount();
            $failures = $import->getFailures();
            $headings = $import->getHeadings();

            $message = "{$imported} branch(es) imported successfully.";
            if ($skipped > 0) $message .= " {$skipped} row(s) skipped (duplicate IFSC or invalid bank code).";
            if (!empty($failures)) {
                $errs = [];
                foreach ($failures as $f) $errs[] = "Row {$f->row()}: " . implode(', ', $f->errors());
                $message .= ' Errors: ' . implode(' | ', array_slice($errs, 0, 5));
                if (count($errs) > 5) $message .= ' ... and ' . (count($errs) - 5) . ' more.';
            }
            if ($imported === 0 && $skipped === 0 && empty($failures))
                $message .= ' No data found. Detected headers: ' . (!empty($headings) ? implode(', ', $headings) : 'none');

            ActivityLog::log('bank_branch_imported', "Imported {$imported} bank branches from Excel");
            return redirect()->route('admin.masters.bank-branches.index')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('admin.masters.bank-branches.index')->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        ActivityLog::log('bank_branch_template_downloaded', 'Downloaded bank branch import template');
        return Excel::download(new BankBranchMasterTemplateExport, 'bank_branch_import_template.xlsx');
    }
}
