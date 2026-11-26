<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceCategory;
use Validator, Exception;

class ServiceCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view service categories')->only(['index', 'show']);
        $this->middleware('permission:create service categories')->only(['create', 'store']);
        $this->middleware('permission:edit service categories')->only(['edit', 'update', 'toggleStatus']);
        $this->middleware('permission:delete service categories')->only('destroy');
    }

    public function index()
    {
        $categories = ServiceCategory::withCount('services')->latest()->paginate(10);
        return view('admin.service-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|unique:service_categories,name',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $request->all();
            $data['status'] = $request->has('status') ? ($request->status === 'active' ? 1 : 0) : 1;
            $category = ServiceCategory::create($data);
            logActivity('Created', 'Service Category', "Created category {$category->name}");
            return response()->json(['success' => true, 'message' => 'Category created successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $category = ServiceCategory::findOrFail($id);
        return response()->json($category);
    }

    public function update(Request $request, $id)
    {
        try {
            $category = ServiceCategory::findOrFail($id);
            $validator = Validator::make($request->all(), [
                'name' => 'required|unique:service_categories,name,' . $id,
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $request->all();
            $data['status'] = $request->has('status') ? ($request->status === 'active' ? 1 : 0) : $category->status;
            $category->update($data);
            logActivity('Updated', 'Service Category', "Updated category {$category->name}");
            return response()->json(['success' => true, 'message' => 'Category updated successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function toggleStatus($id)
    {
        try {
            $category = ServiceCategory::findOrFail($id);
            $category->status = !$category->status;
            $category->save();
            logActivity('Updated', 'Service Category', "Toggled status of category {$category->name}");
            return response()->json(['success' => true, 'status' => $category->status, 'message' => 'Status updated successfully']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $category = ServiceCategory::findOrFail($id);
            $category->delete();
            logActivity('Deleted', 'Service Category', "Deleted category {$category->name}");
            return response()->json(['success' => true, 'message' => 'Category deleted successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
