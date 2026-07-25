<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\ActivityLog;
use App\Exports\VendorTemplateExport;
use App\Imports\VendorImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $query = Vendor::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('vendor_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('gstin', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $vendors = $query->latest()->paginate(15);
        return view('admin.masters.vendors.index', compact('vendors'));
    }

    public function create()
    {
        return view('admin.masters.vendors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendor_code' => ['nullable', 'string', 'max:50', Rule::unique('vendors', 'vendor_code')],
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => ['nullable', 'email', 'max:255'],
            'gstin' => 'nullable|string|max:20',
            'contact_person' => 'nullable|string|max:255',
            'contact_person_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'payment_terms' => 'nullable|string|max:255',
        ]);

        $user = auth()->user();
        $validated['company_id'] = $user->company_id;
        $validated['branch_id'] = $user->branch_id;
        $validated['status'] = 'active';

        Vendor::create($validated);

        ActivityLog::log('vendor_created', "Created vendor: {$validated['name']}");

        return redirect()->route('admin.masters.vendors.index')
            ->with('success', 'Vendor created successfully.');
    }

    public function edit(Vendor $vendor)
    {
        return view('admin.masters.vendors.edit', compact('vendor'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'vendor_code' => ['nullable', 'string', 'max:50', Rule::unique('vendors', 'vendor_code')->ignore($vendor->id)],
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => ['nullable', 'email', 'max:255'],
            'gstin' => 'nullable|string|max:20',
            'contact_person' => 'nullable|string|max:255',
            'contact_person_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'payment_terms' => 'nullable|string|max:255',
        ]);

        $vendor->update($validated);

        ActivityLog::log('vendor_updated', "Updated vendor: {$vendor->name}");

        return redirect()->route('admin.masters.vendors.index')
            ->with('success', 'Vendor updated successfully.');
    }

    public function destroy(Vendor $vendor)
    {
        $vendor->delete();

        ActivityLog::log('vendor_deleted', "Deleted vendor: {$vendor->name}");

        return redirect()->route('admin.masters.vendors.index')
            ->with('success', 'Vendor deleted successfully.');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);

        $user = auth()->user();
        $companyId = $user->company_id;
        $branchId = $user->branch_id;

        $import = new VendorImport($companyId, $branchId);
        try {
            Excel::import($import, $request->file('file'));

            $imported = $import->getImportedCount();
            $skipped = $import->getSkippedCount();
            $failures = $import->getFailures();
            $headings = $import->getHeadings();

            $message = "{$imported} vendor(s) imported successfully.";
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

            ActivityLog::log('vendors_imported', "Imported {$imported} vendors from Excel, {$skipped} skipped");

            return redirect()->route('admin.masters.vendors.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('admin.masters.vendors.index')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        ActivityLog::log('vendor_template_downloaded', 'Downloaded vendor import template');
        return Excel::download(new VendorTemplateExport, 'vendor_import_template.xlsx');
    }

    public function toggleStatus(Vendor $vendor)
    {
        $vendor->status = $vendor->status === 'active' ? 'inactive' : 'active';
        $vendor->save();

        ActivityLog::log('vendor_status_changed', "Changed status of vendor: {$vendor->name}");

        return back()->with('success', 'Vendor status updated.');
    }

    public function trashed()
    {
        $vendors = Vendor::onlyTrashed()->latest()->paginate(15);
        return view('admin.masters.vendors.trashed', compact('vendors'));
    }

    public function restore($id)
    {
        $vendor = Vendor::withTrashed()->findOrFail($id);
        $vendor->restore();

        ActivityLog::log('vendor_restored', "Restored vendor: {$vendor->name}");

        return redirect()->route('admin.masters.vendors.trashed')
            ->with('success', 'Vendor restored successfully.');
    }

    public function forceDelete($id)
    {
        $vendor = Vendor::withTrashed()->findOrFail($id);
        $name = $vendor->name;
        $vendor->forceDelete();

        ActivityLog::log('vendor_force_deleted', "Force deleted vendor: {$name}");

        return redirect()->route('admin.masters.vendors.trashed')
            ->with('success', 'Vendor permanently deleted.');
    }
}
