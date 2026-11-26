<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Controller;
use App\Models\BankMaster;
use App\Models\ActivityLog;
use App\Imports\BankMasterImport;
use App\Exports\BankMasterTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class BankMasterController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user() || (!auth()->user()->can('view banks') && !auth()->user()->isSuperAdmin())) {
                abort(403);
            }
            return $next($request);
        })->except(['quickStore']);
    }

    public function index(Request $request)
    {
        $query = BankMaster::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $banks = $query->orderBy('name')->paginate(15);

        return view('admin.masters.banks.index', compact('banks'));
    }

    public function create()
    {
        return view('admin.masters.banks.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:bank_masters,code',
        ]);

        BankMaster::create($validated + ['status' => 'active']);

        return redirect()->route('admin.masters.banks.index')
            ->with('success', 'Bank created successfully.');
    }

    public function edit(BankMaster $bank)
    {
        return view('admin.masters.banks.edit', compact('bank'));
    }

    public function update(Request $request, BankMaster $bank)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:bank_masters,code,' . $bank->id,
        ]);

        $bank->update($validated);

        return redirect()->route('admin.masters.banks.index')
            ->with('success', 'Bank updated successfully.');
    }

    public function destroy(BankMaster $bank)
    {
        $bank->delete();
        ActivityLog::log('bank_deleted', "Deleted bank: {$bank->name}");
        return redirect()->route('admin.masters.banks.index')
            ->with('success', 'Bank deleted successfully.');
    }

    public function trashed()
    {
        $banks = BankMaster::onlyTrashed()->orderBy('deleted_at', 'desc')->paginate(15);
        return view('admin.masters.banks.trashed', compact('banks'));
    }

    public function restore($id)
    {
        $bank = BankMaster::withTrashed()->findOrFail($id);
        $bank->restore();
        ActivityLog::log('bank_restored', "Restored bank: {$bank->name}");
        return redirect()->route('admin.masters.banks.trashed')->with('success', 'Bank restored successfully.');
    }

    public function forceDelete($id)
    {
        $bank = BankMaster::withTrashed()->findOrFail($id);
        ActivityLog::log('bank_force_deleted', "Force deleted bank: {$bank->name}");
        $bank->forceDelete();
        return redirect()->route('admin.masters.banks.trashed')->with('success', 'Bank permanently deleted.');
    }

    public function toggleStatus(BankMaster $bank)
    {
        $bank->status = $bank->status === 'active' ? 'inactive' : 'active';
        $bank->save();
        ActivityLog::log('bank_status_changed', "Changed status of bank: {$bank->name}");
        return back()->with('success', 'Bank status updated.');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);

        $import = new BankMasterImport;
        try {
            Excel::import($import, $request->file('file'));
            $imported = $import->getImportedCount();
            $skipped = $import->getSkippedCount();
            $failures = $import->getFailures();
            $headings = $import->getHeadings();

            $message = "{$imported} bank(s) imported successfully.";
            if ($skipped > 0) $message .= " {$skipped} row(s) skipped (duplicate or invalid).";
            if (!empty($failures)) {
                $errs = [];
                foreach ($failures as $f) $errs[] = "Row {$f->row()}: " . implode(', ', $f->errors());
                $message .= ' Errors: ' . implode(' | ', array_slice($errs, 0, 5));
                if (count($errs) > 5) $message .= ' ... and ' . (count($errs) - 5) . ' more.';
            }
            if ($imported === 0 && $skipped === 0 && empty($failures))
                $message .= ' No data found. Detected headers: ' . (!empty($headings) ? implode(', ', $headings) : 'none');

            ActivityLog::log('bank_imported', "Imported {$imported} banks from Excel");
            return redirect()->route('admin.masters.banks.index')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('admin.masters.banks.index')->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        ActivityLog::log('bank_template_downloaded', 'Downloaded bank import template');
        return Excel::download(new BankMasterTemplateExport, 'bank_import_template.xlsx');
    }
}
