<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Controller;
use App\Models\GstMaster;
use App\Models\ActivityLog;
use App\Imports\GstMasterImport;
use App\Exports\GstMasterTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class GstMasterController extends Controller
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

    /**
     * Display a listing of GST Masters
     */
    public function index(Request $request)
    {
        $query = GstMaster::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('gst_rate', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $gstMasters = $query->orderBy('percentage')->paginate(15);

        return view('admin.masters.gst.index', compact('gstMasters'));
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);

        $import = new GstMasterImport;
        try {
            Excel::import($import, $request->file('file'));
            $imported = $import->getImportedCount();
            $skipped = $import->getSkippedCount();
            $failures = $import->getFailures();
            $headings = $import->getHeadings();

            $message = "{$imported} GST record(s) imported successfully.";
            if ($skipped > 0) $message .= " {$skipped} row(s) skipped (duplicate GST rate).";
            if (!empty($failures)) {
                $errs = [];
                foreach ($failures as $f) $errs[] = "Row {$f->row()}: " . implode(', ', $f->errors());
                $message .= ' Errors: ' . implode(' | ', array_slice($errs, 0, 5));
                if (count($errs) > 5) $message .= ' ... and ' . (count($errs) - 5) . ' more.';
            }
            if ($imported === 0 && $skipped === 0 && empty($failures))
                $message .= ' No data found. Detected headers: ' . (!empty($headings) ? implode(', ', $headings) : 'none');

            ActivityLog::log('gst_imported', "Imported {$imported} GST records from Excel");
            return redirect()->route('admin.masters.gst.index')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('admin.masters.gst.index')->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        ActivityLog::log('gst_template_downloaded', 'Downloaded GST import template');
        return Excel::download(new GstMasterTemplateExport, 'gst_import_template.xlsx');
    }

    /**
     * Show the form for creating a new GST Master
     */
    public function create()
    {
        return view('admin.masters.gst.create');
    }

    /**
     * Store a newly created GST Master in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'gst_rate' => 'required|string|max:50|unique:gst_masters,gst_rate',
            'percentage' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string|max:500',
        ]);

        GstMaster::create($validated + ['status' => 'active']);

        return redirect()->route('admin.masters.gst.index')
                       ->with('success', 'GST Master created successfully.');
    }

    /**
     * Show the form for editing the specified GST Master
     */
    public function edit(GstMaster $gst)
    {
        return view('admin.masters.gst.edit', compact('gst'));
    }

    /**
     * Update the specified GST Master in storage
     */
    public function update(Request $request, GstMaster $gst)
    {
        $validated = $request->validate([
            'gst_rate' => 'required|string|max:50|unique:gst_masters,gst_rate,' . $gst->id,
            'percentage' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string|max:500',
        ]);

        $gst->update($validated);

        return redirect()->route('admin.masters.gst.index')
                       ->with('success', 'GST Master updated successfully.');
    }

    /**
     * Remove the specified GST Master from storage
     */
    public function destroy(GstMaster $gst)
    {
        $gst->delete();

        return redirect()->route('admin.masters.gst.index')
                       ->with('success', 'GST Master deleted successfully.');
    }

    public function trashed()
    {
        $gsts = GstMaster::onlyTrashed()->orderBy('deleted_at', 'desc')->paginate(15);
        return view('admin.masters.gst.trashed', compact('gsts'));
    }

    public function restore($id)
    {
        $gst = GstMaster::withTrashed()->findOrFail($id);
        $gst->restore();
        ActivityLog::log('gst_restored', "Restored GST: {$gst->gst_rate}");
        return redirect()->route('admin.masters.gst.trashed')->with('success', 'GST restored successfully.');
    }

    public function forceDelete($id)
    {
        $gst = GstMaster::withTrashed()->findOrFail($id);
        ActivityLog::log('gst_force_deleted', "Force deleted GST: {$gst->gst_rate}");
        $gst->forceDelete();
        return redirect()->route('admin.masters.gst.trashed')->with('success', 'GST permanently deleted.');
    }

    public function toggleStatus(GstMaster $gst)
    {
        $gst->status = $gst->status === 'active' ? 'inactive' : 'active';
        $gst->save();
        ActivityLog::log('gst_status_changed', "Changed status of GST: {$gst->gst_rate}", $gst);
        return back()->with('success', 'GST status updated.');
    }

    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'gst_rate' => 'required|string|max:50|unique:gst_masters,gst_rate',
            'percentage' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string|max:500',
        ]);

        $gst = GstMaster::create($validated + ['status' => 'active']);

        return response()->json(['id' => $gst->id, 'gst_rate' => $gst->gst_rate, 'percentage' => $gst->percentage]);
    }
}
