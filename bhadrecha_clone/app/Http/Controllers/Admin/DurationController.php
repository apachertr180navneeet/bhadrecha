<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Duration;
use Validator, Exception;

class DurationController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view service durations')->only(['index', 'show']);
        $this->middleware('permission:create service durations')->only(['create', 'store']);
        $this->middleware('permission:edit service durations')->only(['edit', 'update', 'toggleStatus']);
        $this->middleware('permission:delete service durations')->only('destroy');
    }

    public function index()
    {
        $durations = Duration::latest()->paginate(10);
        return view('admin.durations.index', compact('durations'));
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|unique:durations,name',
                'days' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $request->all();
            $data['status'] = $request->has('status') ? ($request->status === 'active' ? 1 : 0) : 1;
            $duration = Duration::create($data);
            logActivity('Created', 'Duration', "Created duration {$duration->name}");
            return response()->json(['success' => true, 'message' => 'Duration created successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $duration = Duration::findOrFail($id);
        return response()->json($duration);
    }

    public function update(Request $request, $id)
    {
        try {
            $duration = Duration::findOrFail($id);
            $validator = Validator::make($request->all(), [
                'name' => 'required|unique:durations,name,' . $id,
                'days' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $request->all();
            $data['status'] = $request->has('status') ? ($request->status === 'active' ? 1 : 0) : $duration->status;
            $duration->update($data);
            logActivity('Updated', 'Duration', "Updated duration {$duration->name}");
            return response()->json(['success' => true, 'message' => 'Duration updated successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function toggleStatus($id)
    {
        try {
            $duration = Duration::findOrFail($id);
            $duration->status = !$duration->status;
            $duration->save();
            logActivity('Updated', 'Duration', "Toggled status of duration {$duration->name}");
            return response()->json(['success' => true, 'status' => $duration->status, 'message' => 'Status updated successfully']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $duration = Duration::findOrFail($id);
            $duration->delete();
            logActivity('Deleted', 'Duration', "Deleted duration {$duration->name}");
            return response()->json(['success' => true, 'message' => 'Duration deleted successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
