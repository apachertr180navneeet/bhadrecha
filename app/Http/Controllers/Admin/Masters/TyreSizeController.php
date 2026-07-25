<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Controller;
use App\Models\TyreBrand;
use App\Models\TyreModel;
use App\Models\TyreSize;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class TyreSizeController extends Controller
{
    public function index(Request $request)
    {
        $query = TyreSize::with('brand', 'model');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('tyre_brand_id')) {
            $query->where('tyre_brand_id', $request->tyre_brand_id);
        }

        if ($request->filled('tyre_model_id')) {
            $query->where('tyre_model_id', $request->tyre_model_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sizes = $query->orderBy('name')->paginate(15);
        $brands = TyreBrand::where('status', 'active')->orderBy('name')->get();
        $models = TyreModel::where('status', 'active')->orderBy('name')->get();

        return view('admin.masters.tyre-sizes.index', compact('sizes', 'brands', 'models'));
    }

    public function create()
    {
        $brands = TyreBrand::where('status', 'active')->orderBy('name')->get();
        $models = TyreModel::where('status', 'active')->orderBy('name')->get();

        return view('admin.masters.tyre-sizes.create', compact('brands', 'models'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tyre_brand_id' => 'nullable|exists:tyre_brands,id',
            'tyre_model_id' => 'nullable|exists:tyre_models,id',
            'code' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
        ]);

        $size = TyreSize::create($validated);

        ActivityLog::log('tyre_size_created', "Created Tyre Size: {$size->name}", $size);

        return redirect()->route('admin.masters.tyre-sizes.index')
                       ->with('success', 'Tyre size created successfully.');
    }

    public function edit(TyreSize $tyreSize)
    {
        $brands = TyreBrand::where('status', 'active')->orderBy('name')->get();
        $models = TyreModel::where('status', 'active')->orderBy('name')->get();

        return view('admin.masters.tyre-sizes.edit', compact('tyreSize', 'brands', 'models'));
    }

    public function update(Request $request, TyreSize $tyreSize)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tyre_brand_id' => 'nullable|exists:tyre_brands,id',
            'tyre_model_id' => 'nullable|exists:tyre_models,id',
            'code' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
        ]);

        $tyreSize->update($validated);

        ActivityLog::log('tyre_size_updated', "Updated Tyre Size: {$tyreSize->name}", $tyreSize);

        return redirect()->route('admin.masters.tyre-sizes.index')
                       ->with('success', 'Tyre size updated successfully.');
    }

    public function destroy(TyreSize $tyreSize)
    {
        $tyreSize->delete();

        ActivityLog::log('tyre_size_deleted', "Deleted Tyre Size: {$tyreSize->name}", $tyreSize);

        return redirect()->route('admin.masters.tyre-sizes.index')
                       ->with('success', 'Tyre size moved to recycle bin successfully.');
    }

    public function trashed()
    {
        $sizes = TyreSize::onlyTrashed()->with('brand', 'model')->orderBy('deleted_at', 'desc')->paginate(15);
        return view('admin.masters.tyre-sizes.trashed', compact('sizes'));
    }

    public function restore($id)
    {
        $size = TyreSize::withTrashed()->findOrFail($id);
        $size->restore();
        ActivityLog::log('tyre_size_restored', "Restored Tyre Size: {$size->name}", $size);
        return redirect()->route('admin.masters.tyre-sizes.trashed')->with('success', 'Tyre size restored successfully.');
    }

    public function forceDelete($id)
    {
        $size = TyreSize::withTrashed()->findOrFail($id);
        ActivityLog::log('tyre_size_force_deleted', "Permanently deleted Tyre Size: {$size->name}", $size);
        $size->forceDelete();
        return redirect()->route('admin.masters.tyre-sizes.trashed')->with('success', 'Tyre size permanently deleted.');
    }

    public function toggleStatus(TyreSize $tyreSize)
    {
        $tyreSize->status = $tyreSize->status === 'active' ? 'inactive' : 'active';
        $tyreSize->save();
        ActivityLog::log('tyre_size_status_changed', "Changed status of Tyre Size: {$tyreSize->name}", $tyreSize);
        return back()->with('success', 'Tyre size status updated successfully.');
    }

    public function getByModel($modelId)
    {
        $sizes = TyreSize::where('tyre_model_id', $modelId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json($sizes);
    }

    public function getByBrand($brandId)
    {
        $sizes = TyreSize::where(function($q) use ($brandId) {
                $q->where('tyre_brand_id', $brandId)
                  ->orWhereNull('tyre_brand_id');
            })
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json($sizes);
    }
}
