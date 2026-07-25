<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Controller;
use App\Models\Packaging;
use App\Models\ActivityLog;
use App\Imports\PackagingImport;
use App\Exports\PackagingTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class PackagingController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user() || !auth()->user()->isSuperAdmin()) {
                abort(403);
            }
            return $next($request);
        })->except(['quickStore']);
    }

    public function index(Request $request)
    {
        $query = Packaging::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $packagings = $query->orderBy('name')->paginate(15);
        return view('admin.masters.packagings.index', compact('packagings'));
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);

        $import = new PackagingImport;
        try {
            Excel::import($import, $request->file('file'));
            $imported = $import->getImportedCount();
            $skipped = $import->getSkippedCount();
            $failures = $import->getFailures();
            $headings = $import->getHeadings();

            $message = "{$imported} packaging(s) imported successfully.";
            if ($skipped > 0) $message .= " {$skipped} row(s) skipped (duplicate name).";
            if (!empty($failures)) {
                $errs = [];
                foreach ($failures as $f) $errs[] = "Row {$f->row()}: " . implode(', ', $f->errors());
                $message .= ' Errors: ' . implode(' | ', array_slice($errs, 0, 5));
                if (count($errs) > 5) $message .= ' ... and ' . (count($errs) - 5) . ' more.';
            }
            if ($imported === 0 && $skipped === 0 && empty($failures))
                $message .= ' No data found. Detected headers: ' . (!empty($headings) ? implode(', ', $headings) : 'none');

            ActivityLog::log('packagings_imported', "Imported {$imported} packagings from Excel");
            return redirect()->route('admin.masters.packagings.index')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('admin.masters.packagings.index')->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        ActivityLog::log('packaging_template_downloaded', 'Downloaded packaging import template');
        return Excel::download(new PackagingTemplateExport, 'packaging_import_template.xlsx');
    }

    public function create()
    {
        return view('admin.masters.packagings.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:packagings,name',
            'description' => 'nullable|string|max:500',
        ]);

        Packaging::create($validated);

        return redirect()->route('admin.masters.packagings.index')
                       ->with('success', 'Packaging created successfully.');
    }

    public function edit(Packaging $packaging)
    {
        return view('admin.masters.packagings.edit', compact('packaging'));
    }

    public function update(Request $request, Packaging $packaging)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:packagings,name,' . $packaging->id,
            'description' => 'nullable|string|max:500',
        ]);

        $packaging->update($validated);

        return redirect()->route('admin.masters.packagings.index')
                       ->with('success', 'Packaging updated successfully.');
    }

    public function destroy(Packaging $packaging)
    {
        $packaging->delete();

        return redirect()->route('admin.masters.packagings.index')
                       ->with('success', 'Packaging deleted successfully.');
    }

    public function trashed()
    {
        $packagings = Packaging::onlyTrashed()->orderBy('deleted_at', 'desc')->paginate(15);
        return view('admin.masters.packagings.trashed', compact('packagings'));
    }

    public function restore($id)
    {
        $packaging = Packaging::withTrashed()->findOrFail($id);
        $packaging->restore();
        ActivityLog::log('packaging_restored', "Restored packaging: {$packaging->name}");
        return redirect()->route('admin.masters.packagings.trashed')->with('success', 'Packaging restored successfully.');
    }

    public function forceDelete($id)
    {
        $packaging = Packaging::withTrashed()->findOrFail($id);
        ActivityLog::log('packaging_force_deleted', "Force deleted packaging: {$packaging->name}");
        $packaging->forceDelete();
        return redirect()->route('admin.masters.packagings.trashed')->with('success', 'Packaging permanently deleted.');
    }

    public function toggleStatus(Packaging $packaging)
    {
        $packaging->status = $packaging->status === 'active' ? 'inactive' : 'active';
        $packaging->save();
        ActivityLog::log('packaging_status_changed', "Changed status of packaging: {$packaging->name}", $packaging);
        return back()->with('success', 'Packaging status updated.');
    }

    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:packagings,name',
            'description' => 'nullable|string|max:500',
        ]);

        $packaging = Packaging::create($validated);

        return response()->json(['id' => $packaging->id, 'name' => $packaging->name]);
    }
}
