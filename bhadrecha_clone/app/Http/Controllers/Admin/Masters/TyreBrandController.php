<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Controller;
use App\Models\TyreBrand;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class TyreBrandController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('view tyre brands') && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $query = TyreBrand::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $brands = $query->orderBy('name')->paginate(15);
        return view('admin.masters.tyre-brands.index', compact('brands'));
    }

    public function create()
    {
        return view('admin.masters.tyre-brands.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tyre_brands,name',
            'code' => 'nullable|string|max:100|unique:tyre_brands,code',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
        ]);

        $brand = TyreBrand::create($validated);

        ActivityLog::log('tyre_brand_created', "Created Tyre Brand: {$brand->name}", $brand);

        return redirect()->route('admin.masters.tyre-brands.index')
                       ->with('success', 'Tyre brand created successfully.');
    }

    public function edit(TyreBrand $tyreBrand)
    {
        return view('admin.masters.tyre-brands.edit', compact('tyreBrand'));
    }

    public function update(Request $request, TyreBrand $tyreBrand)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tyre_brands,name,' . $tyreBrand->id,
            'code' => 'nullable|string|max:100|unique:tyre_brands,code,' . $tyreBrand->id,
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
        ]);

        $tyreBrand->update($validated);

        ActivityLog::log('tyre_brand_updated', "Updated Tyre Brand: {$tyreBrand->name}", $tyreBrand);

        return redirect()->route('admin.masters.tyre-brands.index')
                       ->with('success', 'Tyre brand updated successfully.');
    }

    public function destroy(TyreBrand $tyreBrand)
    {
        $tyreBrand->delete();

        ActivityLog::log('tyre_brand_deleted', "Deleted Tyre Brand: {$tyreBrand->name}", $tyreBrand);

        return redirect()->route('admin.masters.tyre-brands.index')
                       ->with('success', 'Tyre brand moved to recycle bin successfully.');
    }

    public function trashed()
    {
        $brands = TyreBrand::onlyTrashed()->orderBy('deleted_at', 'desc')->paginate(15);
        return view('admin.masters.tyre-brands.trashed', compact('brands'));
    }

    public function restore($id)
    {
        $brand = TyreBrand::withTrashed()->findOrFail($id);
        $brand->restore();
        ActivityLog::log('tyre_brand_restored', "Restored Tyre Brand: {$brand->name}", $brand);
        return redirect()->route('admin.masters.tyre-brands.trashed')->with('success', 'Tyre brand restored successfully.');
    }

    public function forceDelete($id)
    {
        $brand = TyreBrand::withTrashed()->findOrFail($id);
        ActivityLog::log('tyre_brand_force_deleted', "Permanently deleted Tyre Brand: {$brand->name}", $brand);
        $brand->forceDelete();
        return redirect()->route('admin.masters.tyre-brands.trashed')->with('success', 'Tyre brand permanently deleted.');
    }

    public function toggleStatus(TyreBrand $tyreBrand)
    {
        $tyreBrand->status = $tyreBrand->status === 'active' ? 'inactive' : 'active';
        $tyreBrand->save();
        ActivityLog::log('tyre_brand_status_changed', "Changed status of Tyre Brand: {$tyreBrand->name}", $tyreBrand);
        return back()->with('success', 'Tyre brand status updated successfully.');
    }

    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tyre_brands,name',
            'code' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
        ]);

        $validated['status'] = 'active';
        $brand = TyreBrand::create($validated);

        ActivityLog::log('tyre_brand_quick_created', "Quick created Tyre Brand: {$brand->name}", $brand);

        return response()->json(['id' => $brand->id, 'name' => $brand->name]);
    }
}
