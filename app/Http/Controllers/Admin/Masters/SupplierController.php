<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\ActivityLog;
use App\Exports\SupplierTemplateExport;
use App\Imports\SupplierImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('view suppliers') && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $query = Supplier::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('gstin', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $suppliers = $query->latest()->paginate(15);
        return view('admin.masters.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('admin.masters.suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => ['nullable', 'email', 'max:255'],
            'gstin' => 'nullable|string|max:20',
            'contact_person' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
        ]);

        $user = auth()->user();
        $validated['company_id'] = $user->company_id;
        $validated['branch_id'] = $user->branch_id;
        $validated['status'] = 'active';

        Supplier::create($validated);

        ActivityLog::log('supplier_created', "Created supplier: {$validated['name']}");

        return redirect()->route('admin.masters.suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    public function edit(Supplier $supplier)
    {
        return view('admin.masters.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => ['nullable', 'email', 'max:255'],
            'gstin' => 'nullable|string|max:20',
            'contact_person' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
        ]);

        $supplier->update($validated);

        ActivityLog::log('supplier_updated', "Updated supplier: {$supplier->name}");

        return redirect()->route('admin.masters.suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        ActivityLog::log('supplier_deleted', "Deleted supplier: {$supplier->name}");

        return redirect()->route('admin.masters.suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);

        $user = auth()->user();
        $companyId = $user->company_id;
        $branchId = $user->branch_id;

        $import = new SupplierImport($companyId, $branchId);
        try {
            Excel::import($import, $request->file('file'));

            $imported = $import->getImportedCount();
            $skipped = $import->getSkippedCount();
            $failures = $import->getFailures();
            $headings = $import->getHeadings();

            $message = "{$imported} supplier(s) imported successfully.";
            if ($skipped > 0) {
                $message .= " {$skipped} row(s) skipped (missing data).";
            }

            if (!empty($failures)) {
                $errorMessages = [];
                foreach ($failures as $failure) {
                    $errorMessages[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
                }
                $message .= ' Validation errors: ' . implode(' | ', array_slice($errorMessages, 0, 5));
                if (count($errorMessages) > 5) {
                    $message .= ' ... and ' . (count($errorMessages) - 5) . ' more.';
                }
            }

            if ($imported === 0 && $skipped === 0 && empty($failures)) {
                $headingsStr = !empty($headings) ? ' Detected headers: ' . implode(', ', $headings) : ' No headers detected.';
                $message .= ' Ensure your Excel file has data rows below the header row.' . $headingsStr;
            }

            ActivityLog::log('suppliers_imported', "Imported {$imported} suppliers from Excel, {$skipped} skipped");

            return redirect()->route('admin.masters.suppliers.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('admin.masters.suppliers.index')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        ActivityLog::log('supplier_template_downloaded', 'Downloaded supplier import template');
        return Excel::download(new SupplierTemplateExport, 'supplier_import_template.xlsx');
    }

    public function toggleStatus(Supplier $supplier)
    {
        $supplier->status = $supplier->status === 'active' ? 'inactive' : 'active';
        $supplier->save();

        ActivityLog::log('supplier_status_changed', "Changed status of supplier: {$supplier->name}");

        return back()->with('success', 'Supplier status updated.');
    }

    public function trashed()
    {
        $suppliers = Supplier::onlyTrashed()->latest()->paginate(15);
        return view('admin.masters.suppliers.trashed', compact('suppliers'));
    }

    public function restore($id)
    {
        $supplier = Supplier::withTrashed()->findOrFail($id);
        $supplier->restore();

        ActivityLog::log('supplier_restored', "Restored supplier: {$supplier->name}");

        return redirect()->route('admin.masters.suppliers.trashed')
            ->with('success', 'Supplier restored successfully.');
    }

    public function forceDelete($id)
    {
        $supplier = Supplier::withTrashed()->findOrFail($id);
        $name = $supplier->name;
        $supplier->forceDelete();

        ActivityLog::log('supplier_force_deleted', "Force deleted supplier: {$name}");

        return redirect()->route('admin.masters.suppliers.trashed')
            ->with('success', 'Supplier permanently deleted.');
    }
}
