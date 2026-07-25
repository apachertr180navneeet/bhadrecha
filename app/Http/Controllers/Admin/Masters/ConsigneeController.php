<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Controller;
use App\Models\Consignee;
use App\Models\Branch;
use App\Models\Company;
use App\Models\ActivityLog;
use App\Imports\ConsigneeImport;
use App\Exports\ConsigneeTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ConsigneeController extends Controller
{
    public function index(Request $request)
    {
        $query = Consignee::with(['branch', 'company']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('gstin', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $consignees = $query->orderBy('created_at', 'desc')->paginate(15);
        return view('admin.masters.consignees.index', compact('consignees'));
    }

    public function create()
    {
        $branches = Branch::where('status', 'active')->get();
        return view('admin.masters.consignees.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'name' => 'required|string|max:255',
            'phone' => ['nullable', 'string', 'max:10', Rule::unique('consignees', 'phone')],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('consignees', 'email')],
            'gstin' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
        ]);

        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            $validated['company_id'] = $request->validate(['company_id' => 'required|exists:companies,id'])['company_id'];
        } else {
            abort_if(!$user->company_id, 403, 'Your account is not associated with any company.');
            $validated['company_id'] = $user->company_id;
        }

        if (!$user->isSuperAdmin()) {
            $validated['branch_id'] = $validated['branch_id'] ?? $user->branch_id;
        }

        $validated['status'] = 'active';

        $consignee = Consignee::create($validated);
        ActivityLog::log('consignee_created', "Created consignee: {$consignee->name}", $consignee);

        return redirect()->route('admin.masters.consignees.index')->with('success', 'Consignee created successfully.');
    }

    public function edit(Consignee $consignee)
    {
        $branches = Branch::where('status', 'active')->get();
        return view('admin.masters.consignees.edit', compact('consignee', 'branches'));
    }

    public function update(Request $request, Consignee $consignee)
    {
        $validated = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'name' => 'required|string|max:255',
            'phone' => ['nullable', 'string', 'max:10', Rule::unique('consignees', 'phone')->ignore($consignee->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('consignees', 'email')->ignore($consignee->id)],
            'gstin' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
        ]);

        if (auth()->user()->isSuperAdmin()) {
            $validated['company_id'] = $request->validate(['company_id' => 'required|exists:companies,id'])['company_id'];
        }

        $consignee->update($validated);
        ActivityLog::log('consignee_updated', "Updated consignee: {$consignee->name}", $consignee);

        return redirect()->route('admin.masters.consignees.index')->with('success', 'Consignee updated successfully.');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);

        $user = auth()->user();
        $companyId = $user->isSuperAdmin()
            ? $request->validate(['company_id' => 'required|exists:companies,id'])['company_id']
            : $user->company_id;
        $branchId = $request->branch_id ?? ($user->isSuperAdmin() ? null : $user->branch_id);

        $import = new ConsigneeImport($companyId, $branchId);
        try {
            Excel::import($import, $request->file('file'));
            $imported = $import->getImportedCount();
            $skipped = $import->getSkippedCount();
            $failures = $import->getFailures();
            $headings = $import->getHeadings();

            $message = "{$imported} consignee(s) imported successfully.";
            if ($skipped > 0) $message .= " {$skipped} row(s) skipped (duplicate phone).";
            if (!empty($failures)) {
                $errs = [];
                foreach ($failures as $f) $errs[] = "Row {$f->row()}: " . implode(', ', $f->errors());
                $message .= ' Errors: ' . implode(' | ', array_slice($errs, 0, 5));
                if (count($errs) > 5) $message .= ' ... and ' . (count($errs) - 5) . ' more.';
            }
            if ($imported === 0 && $skipped === 0 && empty($failures))
                $message .= ' No data found. Detected headers: ' . (!empty($headings) ? implode(', ', $headings) : 'none');

            ActivityLog::log('consignees_imported', "Imported {$imported} consignees from Excel, {$skipped} skipped");
            return redirect()->route('admin.masters.consignees.index')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('admin.masters.consignees.index')->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        ActivityLog::log('consignee_template_downloaded', 'Downloaded consignee import template');
        return Excel::download(new ConsigneeTemplateExport, 'consignee_import_template.xlsx');
    }

    public function transferForm(Consignee $consignee)
    {
        $companies = Company::where('status', 'active')->get();
        $branches = Branch::where('status', 'active')->get();
        return view('admin.masters.consignees.transfer', compact('consignee', 'companies', 'branches'));
    }

    public function transfer(Request $request, Consignee $consignee)
    {
        $validated = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        if (auth()->user()->isSuperAdmin()) {
            $validated['company_id'] = $request->validate(['company_id' => 'required|exists:companies,id'])['company_id'];
        }

        $consignee->update($validated);
        ActivityLog::log('consignee_transferred', "Transferred consignee: {$consignee->name}", $consignee);

        return redirect()->route('admin.masters.consignees.index')->with('success', 'Consignee transferred successfully.');
    }

    public function trashed()
    {
        $consignees = Consignee::onlyTrashed()->with(['branch', 'company'])->paginate(15);
        return view('admin.masters.consignees.trashed', compact('consignees'));
    }

    public function restore($id)
    {
        $consignee = Consignee::withTrashed()->findOrFail($id);
        $consignee->restore();
        ActivityLog::log('consignee_restored', "Restored consignee: {$consignee->name}");
        return redirect()->route('admin.masters.consignees.trashed')->with('success', 'Consignee restored successfully.');
    }

    public function forceDelete($id)
    {
        $consignee = Consignee::withTrashed()->findOrFail($id);
        ActivityLog::log('consignee_force_deleted', "Force deleted consignee: {$consignee->name}");
        $consignee->forceDelete();
        return redirect()->route('admin.masters.consignees.trashed')->with('success', 'Consignee permanently deleted.');
    }

    public function destroy(Consignee $consignee)
    {
        $consignee->delete();
        ActivityLog::log('consignee_deleted', "Deleted consignee: {$consignee->name}");
        return redirect()->route('admin.masters.consignees.index')->with('success', 'Consignee deleted successfully.');
    }

    public function toggleStatus(Consignee $consignee)
    {
        $consignee->status = $consignee->status === 'active' ? 'inactive' : 'active';
        $consignee->save();
        ActivityLog::log('consignee_status_changed', "Changed status of consignee: {$consignee->name}", $consignee);
        return back()->with('success', 'Consignee status updated.');
    }

    public function search(Request $request)
    {
        $term = $request->term;
        $consignees = Consignee::where('name', 'like', "%{$term}%")
            ->orWhere('phone', 'like', "%{$term}%")
            ->orWhere('gstin', 'like', "%{$term}%")
            ->limit(10)
            ->get();

        return response()->json($consignees);
    }

    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'gstin' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

    $validated['company_id'] = $request->input('company_id', auth()->user()->company_id);
    $validated['branch_id'] = $request->input('branch_id', auth()->user()->branch_id);
    $validated['status'] = 'active';

        $consignee = Consignee::create($validated);

        ActivityLog::log('consignee_quick_created', "Quick created consignee: {$consignee->name}", $consignee);

        return response()->json([
            'id' => $consignee->id,
            'name' => $consignee->name,
            'phone' => $consignee->phone,
            'gstin' => $consignee->gstin,
            'address' => $consignee->address,
        ]);
    }
}
