<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\ServiceCategory;
use Validator, Exception;

class ServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view services')->only(['index', 'show']);
        $this->middleware('permission:create services')->only(['create', 'store']);
        $this->middleware('permission:edit services')->only(['edit', 'update', 'toggleStatus']);
        $this->middleware('permission:delete services')->only('destroy');
    }

    public function index()
    {
        $services = Service::with('category')->latest()->paginate(10);
        $categories = ServiceCategory::where('status', true)->get();
        return view('admin.services.index', compact('services', 'categories'));
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'category_id' => 'required|exists:service_categories,id',
                'service_name' => 'required',
                'duration' => 'required|integer|min:5',
                'price' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $request->all();
            $data['status'] = $request->has('status') ? (in_array($request->status, ['active', '1', 1], true) ? 1 : 0) : 1;
            $service = Service::create($data);
            logActivity('Created', 'Service', "Created service {$service->service_name}");
            return response()->json(['success' => true, 'message' => 'Service created successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $service = Service::with('category')->findOrFail($id);
        return response()->json($service);
    }

    public function update(Request $request, $id)
    {
        try {
            $service = Service::findOrFail($id);
            $validator = Validator::make($request->all(), [
                'category_id' => 'required|exists:service_categories,id',
                'service_name' => 'required',
                'duration' => 'required|integer|min:5',
                'price' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $request->all();
            $data['status'] = $request->has('status') ? (in_array($request->status, ['active', '1', 1], true) ? 1 : 0) : $service->status;
            $service->update($data);
            logActivity('Updated', 'Service', "Updated service {$service->service_name}");
            return response()->json(['success' => true, 'message' => 'Service updated successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function toggleStatus($id)
    {
        try {
            $service = Service::findOrFail($id);
            $service->status = !$service->status;
            $service->save();
            logActivity('Updated', 'Service', "Toggled status of service {$service->service_name}");
            return response()->json(['success' => true, 'status' => $service->status, 'message' => 'Status updated successfully']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $service = Service::findOrFail($id);
            $service->delete();
            logActivity('Deleted', 'Service', "Deleted service {$service->service_name}");
            return response()->json(['success' => true, 'message' => 'Service deleted successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getByCategory($categoryId)
    {
        $services = Service::where('category_id', $categoryId)->where('status', true)->get();
        return response()->json($services);
    }
}
