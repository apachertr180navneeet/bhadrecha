<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ActivityLog;
use App\Imports\ItemImport;
use App\Exports\ItemTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user() || (!auth()->user()->can('view items') && !auth()->user()->isSuperAdmin())) {
                abort(403);
            }
            return $next($request);
        })->except(['quickStore']);
    }

    public function index(Request $request)
    {
        $query = Item::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('name')->paginate(15);
        return view('admin.masters.items.index', compact('items'));
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);

        $import = new ItemImport;
        try {
            Excel::import($import, $request->file('file'));
            $imported = $import->getImportedCount();
            $skipped = $import->getSkippedCount();
            $failures = $import->getFailures();
            $headings = $import->getHeadings();

            $message = "{$imported} item(s) imported successfully.";
            if ($skipped > 0) $message .= " {$skipped} row(s) skipped (duplicate name).";
            if (!empty($failures)) {
                $errs = [];
                foreach ($failures as $f) $errs[] = "Row {$f->row()}: " . implode(', ', $f->errors());
                $message .= ' Errors: ' . implode(' | ', array_slice($errs, 0, 5));
                if (count($errs) > 5) $message .= ' ... and ' . (count($errs) - 5) . ' more.';
            }
            if ($imported === 0 && $skipped === 0 && empty($failures))
                $message .= ' No data found. Detected headers: ' . (!empty($headings) ? implode(', ', $headings) : 'none');

            ActivityLog::log('items_imported', "Imported {$imported} items from Excel");
            return redirect()->route('admin.masters.items.index')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('admin.masters.items.index')->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        ActivityLog::log('item_template_downloaded', 'Downloaded item import template');
        return Excel::download(new ItemTemplateExport, 'item_import_template.xlsx');
    }

    public function create()
    {
        return view('admin.masters.items.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:items,name',
            'description' => 'nullable|string|max:500',
        ]);

        Item::create($validated);

        return redirect()->route('admin.masters.items.index')
                       ->with('success', 'Item created successfully.');
    }

    public function edit(Item $item)
    {
        return view('admin.masters.items.edit', compact('item'));
    }

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:items,name,' . $item->id,
            'description' => 'nullable|string|max:500',
        ]);

        $item->update($validated);

        return redirect()->route('admin.masters.items.index')
                       ->with('success', 'Item updated successfully.');
    }

    public function destroy(Item $item)
    {
        $item->delete();

        return redirect()->route('admin.masters.items.index')
                       ->with('success', 'Item deleted successfully.');
    }

    public function trashed()
    {
        $items = Item::onlyTrashed()->orderBy('deleted_at', 'desc')->paginate(15);
        return view('admin.masters.items.trashed', compact('items'));
    }

    public function restore($id)
    {
        $item = Item::withTrashed()->findOrFail($id);
        $item->restore();
        ActivityLog::log('item_restored', "Restored item: {$item->name}");
        return redirect()->route('admin.masters.items.trashed')->with('success', 'Item restored successfully.');
    }

    public function forceDelete($id)
    {
        $item = Item::withTrashed()->findOrFail($id);
        ActivityLog::log('item_force_deleted', "Force deleted item: {$item->name}");
        $item->forceDelete();
        return redirect()->route('admin.masters.items.trashed')->with('success', 'Item permanently deleted.');
    }

    public function toggleStatus(Item $item)
    {
        $item->status = $item->status === 'active' ? 'inactive' : 'active';
        $item->save();
        ActivityLog::log('item_status_changed', "Changed status of item: {$item->name}", $item);
        return back()->with('success', 'Item status updated.');
    }

    public function search(Request $request)
    {
        $term = $request->term;
        $items = Item::where('name', 'like', "%{$term}%")
            ->orWhere('description', 'like', "%{$term}%")
            ->limit(10)
            ->get();

        return response()->json($items);
    }

    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:items,name',
            'description' => 'nullable|string|max:500',
        ]);

        $item = Item::create($validated);

        return response()->json(['id' => $item->id, 'name' => $item->name]);
    }
}
