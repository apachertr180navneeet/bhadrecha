<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Exports\AdBlueCompanyTemplateExport;
use App\Imports\AdBlueCompanyImport;
use App\Http\Controllers\Controller;
use App\Models\AdBlueCompany;
use Exception;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AdBlueCompanyController extends Controller
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
        $query = AdBlueCompany::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $adblueCompanies = $query->orderBy('name')->paginate(15);
        return view('admin.masters.adblue-companies.index', compact('adblueCompanies'));
    }

    public function create()
    {
        return view('admin.masters.adblue-companies.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:adblue_companies,name',
        ]);

        AdBlueCompany::create($validated + ['status' => 'active']);

        return redirect()->route('admin.masters.adblue-companies.index')
            ->with('success', 'AdBlue company created successfully.');
    }

    public function edit(AdBlueCompany $adblueCompany)
    {
        return view('admin.masters.adblue-companies.edit', compact('adblueCompany'));
    }

    public function update(Request $request, AdBlueCompany $adblueCompany)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:adblue_companies,name,' . $adblueCompany->id,
        ]);

        $adblueCompany->update($validated);

        return redirect()->route('admin.masters.adblue-companies.index')
            ->with('success', 'AdBlue company updated successfully.');
    }

    public function destroy(AdBlueCompany $adblueCompany)
    {
        $adblueCompany->delete();

        return redirect()->route('admin.masters.adblue-companies.index')
            ->with('success', 'AdBlue company deleted successfully.');
    }

    public function trashed()
    {
        $adblueCompanies = AdBlueCompany::onlyTrashed()->orderBy('deleted_at', 'desc')->paginate(15);
        return view('admin.masters.adblue-companies.trashed', compact('adblueCompanies'));
    }

    public function restore($id)
    {
        $adblueCompany = AdBlueCompany::withTrashed()->findOrFail($id);
        $adblueCompany->restore();

        return redirect()->route('admin.masters.adblue-companies.trashed')
            ->with('success', 'AdBlue company restored successfully.');
    }

    public function forceDelete($id)
    {
        $adblueCompany = AdBlueCompany::withTrashed()->findOrFail($id);
        $adblueCompany->forceDelete();

        return redirect()->route('admin.masters.adblue-companies.trashed')
            ->with('success', 'AdBlue company permanently deleted.');
    }

    public function downloadTemplate()
    {
        return Excel::download(new AdBlueCompanyTemplateExport, 'adblue_company_import_template.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);

        $import = new AdBlueCompanyImport;
        try {
            Excel::import($import, $request->file('file'));
            $imported = $import->getImportedCount();
            $skipped = $import->getSkippedCount();
            $failures = $import->getFailures();
            $headings = $import->getHeadings();

            $message = "{$imported} AdBlue company(s) imported successfully.";
            if ($skipped > 0) $message .= " {$skipped} row(s) skipped (duplicate name).";
            if (!empty($failures)) {
                $errs = [];
                foreach ($failures as $f) $errs[] = "Row {$f->row()}: " . implode(', ', $f->errors());
                $message .= ' Errors: ' . implode(' | ', array_slice($errs, 0, 5));
                if (count($errs) > 5) $message .= ' ... and ' . (count($errs) - 5) . ' more.';
            }
            if ($imported === 0 && $skipped === 0 && empty($failures))
                $message .= ' No data found. Detected headers: ' . (!empty($headings) ? implode(', ', $headings) : 'none');

            return redirect()->route('admin.masters.adblue-companies.index')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('admin.masters.adblue-companies.index')->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function toggleStatus(AdBlueCompany $adblueCompany)
    {
        $adblueCompany->status = $adblueCompany->status === 'active' ? 'inactive' : 'active';
        $adblueCompany->save();

        return back()->with('success', 'AdBlue company status updated.');
    }
}
