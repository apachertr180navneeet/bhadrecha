<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Controller;
use App\Models\FuelCompany;
use App\Models\ActivityLog;
use App\Imports\FuelCompanyImport;
use App\Exports\FuelCompanyTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class FuelCompanyController extends Controller
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
        $query = FuelCompany::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $fuelCompanies = $query->orderBy('name')->paginate(15);
        return view('admin.masters.fuel-companies.index', compact('fuelCompanies'));
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);

        $import = new FuelCompanyImport;
        try {
            Excel::import($import, $request->file('file'));
            $imported = $import->getImportedCount();
            $skipped = $import->getSkippedCount();
            $failures = $import->getFailures();
            $headings = $import->getHeadings();

            $message = "{$imported} fuel company(s) imported successfully.";
            if ($skipped > 0) $message .= " {$skipped} row(s) skipped (duplicate name).";
            if (!empty($failures)) {
                $errs = [];
                foreach ($failures as $f) $errs[] = "Row {$f->row()}: " . implode(', ', $f->errors());
                $message .= ' Errors: ' . implode(' | ', array_slice($errs, 0, 5));
                if (count($errs) > 5) $message .= ' ... and ' . (count($errs) - 5) . ' more.';
            }
            if ($imported === 0 && $skipped === 0 && empty($failures))
                $message .= ' No data found. Detected headers: ' . (!empty($headings) ? implode(', ', $headings) : 'none');

            ActivityLog::log('fuel_companies_imported', "Imported {$imported} fuel companies from Excel");
            return redirect()->route('admin.masters.fuel-companies.index')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('admin.masters.fuel-companies.index')->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        ActivityLog::log('fuel_company_template_downloaded', 'Downloaded fuel company import template');
        return Excel::download(new FuelCompanyTemplateExport, 'fuel_company_import_template.xlsx');
    }

    public function create()
    {
        return view('admin.masters.fuel-companies.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:fuel_companies,name',
        ]);

        FuelCompany::create($validated + ['status' => 'active']);

        return redirect()->route('admin.masters.fuel-companies.index')
                       ->with('success', 'Fuel company created successfully.');
    }

    public function edit(FuelCompany $fuelCompany)
    {
        return view('admin.masters.fuel-companies.edit', compact('fuelCompany'));
    }

    public function update(Request $request, FuelCompany $fuelCompany)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:fuel_companies,name,' . $fuelCompany->id,
        ]);

        $fuelCompany->update($validated);

        return redirect()->route('admin.masters.fuel-companies.index')
                       ->with('success', 'Fuel company updated successfully.');
    }

    public function destroy(FuelCompany $fuelCompany)
    {
        if ($fuelCompany->fuelPumps()->exists()) {
            return back()->with('error', 'Cannot delete fuel company assigned to fuel pumps.');
        }
        $fuelCompany->delete();

        return redirect()->route('admin.masters.fuel-companies.index')
                       ->with('success', 'Fuel company deleted successfully.');
    }

    public function trashed()
    {
        $fuelCompanies = FuelCompany::onlyTrashed()->orderBy('deleted_at', 'desc')->paginate(15);
        return view('admin.masters.fuel-companies.trashed', compact('fuelCompanies'));
    }

    public function restore($id)
    {
        $fuelCompany = FuelCompany::withTrashed()->findOrFail($id);
        $fuelCompany->restore();
        ActivityLog::log('fuel_company_restored', "Restored fuel company: {$fuelCompany->name}");
        return redirect()->route('admin.masters.fuel-companies.trashed')->with('success', 'Fuel company restored successfully.');
    }

    public function forceDelete($id)
    {
        $fuelCompany = FuelCompany::withTrashed()->findOrFail($id);
        ActivityLog::log('fuel_company_force_deleted', "Force deleted fuel company: {$fuelCompany->name}");
        $fuelCompany->forceDelete();
        return redirect()->route('admin.masters.fuel-companies.trashed')->with('success', 'Fuel company permanently deleted.');
    }

    public function toggleStatus(FuelCompany $fuelCompany)
    {
        $fuelCompany->status = $fuelCompany->status === 'active' ? 'inactive' : 'active';
        $fuelCompany->save();
        ActivityLog::log('fuel_company_status_changed', "Changed status of fuel company: {$fuelCompany->name}", $fuelCompany);
        return back()->with('success', 'Fuel company status updated.');
    }
}
