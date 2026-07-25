<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\ActivityLog;
use App\Imports\UnitImport;
use App\Exports\UnitTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class UnitController extends Controller
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
        $query = Unit::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $units = $query->orderBy('name')->paginate(15);
        return view('admin.masters.units.index', compact('units'));
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);

        $import = new UnitImport;
        try {
            Excel::import($import, $request->file('file'));
            $imported = $import->getImportedCount();
            $skipped = $import->getSkippedCount();
            $failures = $import->getFailures();
            $headings = $import->getHeadings();

            $message = "{$imported} unit(s) imported successfully.";
            if ($skipped > 0) $message .= " {$skipped} row(s) skipped (duplicate name).";
            if (!empty($failures)) {
                $errs = [];
                foreach ($failures as $f) $errs[] = "Row {$f->row()}: " . implode(', ', $f->errors());
                $message .= ' Errors: ' . implode(' | ', array_slice($errs, 0, 5));
                if (count($errs) > 5) $message .= ' ... and ' . (count($errs) - 5) . ' more.';
            }
            if ($imported === 0 && $skipped === 0 && empty($failures))
                $message .= ' No data found. Detected headers: ' . (!empty($headings) ? implode(', ', $headings) : 'none');

            ActivityLog::log('units_imported', "Imported {$imported} units from Excel");
            return redirect()->route('admin.masters.units.index')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('admin.masters.units.index')->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        ActivityLog::log('unit_template_downloaded', 'Downloaded unit import template');
        return Excel::download(new UnitTemplateExport, 'unit_import_template.xlsx');
    }

    public function create()
    {
        return view('admin.masters.units.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:units,name',
            'description' => 'nullable|string|max:500',
        ]);

        Unit::create($validated);

        return redirect()->route('admin.masters.units.index')
                       ->with('success', 'Unit created successfully.');
    }

    public function edit(Unit $unit)
    {
        return view('admin.masters.units.edit', compact('unit'));
    }

    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:units,name,' . $unit->id,
            'description' => 'nullable|string|max:500',
        ]);

        $unit->update($validated);

        return redirect()->route('admin.masters.units.index')
                       ->with('success', 'Unit updated successfully.');
    }

    public function destroy(Unit $unit)
    {
        $unit->delete();

        return redirect()->route('admin.masters.units.index')
                       ->with('success', 'Unit deleted successfully.');
    }

    public function trashed()
    {
        $units = Unit::onlyTrashed()->orderBy('deleted_at', 'desc')->paginate(15);
        return view('admin.masters.units.trashed', compact('units'));
    }

    public function restore($id)
    {
        $unit = Unit::withTrashed()->findOrFail($id);
        $unit->restore();
        ActivityLog::log('unit_restored', "Restored unit: {$unit->name}");
        return redirect()->route('admin.masters.units.trashed')->with('success', 'Unit restored successfully.');
    }

    public function forceDelete($id)
    {
        $unit = Unit::withTrashed()->findOrFail($id);
        ActivityLog::log('unit_force_deleted', "Force deleted unit: {$unit->name}");
        $unit->forceDelete();
        return redirect()->route('admin.masters.units.trashed')->with('success', 'Unit permanently deleted.');
    }

    public function toggleStatus(Unit $unit)
    {
        $unit->status = $unit->status === 'active' ? 'inactive' : 'active';
        $unit->save();
        ActivityLog::log('unit_status_changed', "Changed status of unit: {$unit->name}", $unit);
        return back()->with('success', 'Unit status updated.');
    }

    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:units,name',
            'description' => 'nullable|string|max:500',
        ]);

        $unit = Unit::create($validated);

        return response()->json(['id' => $unit->id, 'name' => $unit->name]);
    }
}
