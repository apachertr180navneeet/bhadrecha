<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Controller;
use App\Models\TyreBrand;
use App\Models\TyreModel;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class TyreModelController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('view tyre models') && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $query = TyreModel::with('brand');

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

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $models = $query->orderBy('name')->paginate(15);
        $brands = TyreBrand::where('status', 'active')->orderBy('name')->get();

        return view('admin.masters.tyre-models.index', compact('models', 'brands'));
    }

    public function create()
    {
        $brands = TyreBrand::where('status', 'active')->orderBy('name')->get();
        return view('admin.masters.tyre-models.create', compact('brands'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tyre_brand_id' => 'required|exists:tyre_brands,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
        ]);

        $model = TyreModel::create($validated);

        ActivityLog::log('tyre_model_created', "Created Tyre Model: {$model->name}", $model);

        return redirect()->route('admin.masters.tyre-models.index')
                       ->with('success', 'Tyre model created successfully.');
    }

    public function edit(TyreModel $tyreModel)
    {
        $brands = TyreBrand::where('status', 'active')->orderBy('name')->get();
        return view('admin.masters.tyre-models.edit', compact('tyreModel', 'brands'));
    }

    public function update(Request $request, TyreModel $tyreModel)
    {
        $validated = $request->validate([
            'tyre_brand_id' => 'required|exists:tyre_brands,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
        ]);

        $tyreModel->update($validated);

        ActivityLog::log('tyre_model_updated', "Updated Tyre Model: {$tyreModel->name}", $tyreModel);

        return redirect()->route('admin.masters.tyre-models.index')
                       ->with('success', 'Tyre model updated successfully.');
    }

    public function destroy(TyreModel $tyreModel)
    {
        $tyreModel->delete();

        ActivityLog::log('tyre_model_deleted', "Deleted Tyre Model: {$tyreModel->name}", $tyreModel);

        return redirect()->route('admin.masters.tyre-models.index')
                       ->with('success', 'Tyre model moved to recycle bin successfully.');
    }

    public function trashed()
    {
        $models = TyreModel::onlyTrashed()->with('brand')->orderBy('deleted_at', 'desc')->paginate(15);
        return view('admin.masters.tyre-models.trashed', compact('models'));
    }

    public function restore($id)
    {
        $model = TyreModel::withTrashed()->findOrFail($id);
        $model->restore();
        ActivityLog::log('tyre_model_restored', "Restored Tyre Model: {$model->name}", $model);
        return redirect()->route('admin.masters.tyre-models.trashed')->with('success', 'Tyre model restored successfully.');
    }

    public function forceDelete($id)
    {
        $model = TyreModel::withTrashed()->findOrFail($id);
        ActivityLog::log('tyre_model_force_deleted', "Permanently deleted Tyre Model: {$model->name}", $model);
        $model->forceDelete();
        return redirect()->route('admin.masters.tyre-models.trashed')->with('success', 'Tyre model permanently deleted.');
    }

    public function toggleStatus(TyreModel $tyreModel)
    {
        $tyreModel->status = $tyreModel->status === 'active' ? 'inactive' : 'active';
        $tyreModel->save();
        ActivityLog::log('tyre_model_status_changed', "Changed status of Tyre Model: {$tyreModel->name}", $tyreModel);
        return back()->with('success', 'Tyre model status updated successfully.');
    }

    public function getByBrand($brandId)
    {
        $models = TyreModel::where('tyre_brand_id', $brandId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json($models);
    }
}
