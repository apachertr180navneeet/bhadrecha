<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use App\Models\Staff;
use App\Models\Attendance;
use Carbon\Carbon;
use Validator, Exception;

class LeaveRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view leave requests')->only(['index', 'show']);
        $this->middleware('permission:create leave requests')->only(['create', 'store']);
        $this->middleware('permission:approve leave requests')->only(['updateStatus', 'edit', 'update']);
        $this->middleware('permission:delete leave requests')->only('destroy');
    }

    public function index()
    {
        $storeId = auth()->user()->store_id;
        $isAdmin = auth()->user()->hasRole('Admin');

        $leaves = LeaveRequest::with('staff')
            ->whereHas('staff', function ($q) use ($storeId, $isAdmin) {
                if ($storeId && !$isAdmin) {
                    $q->where('store_id', $storeId);
                }
            })
            ->latest()->paginate(10);

        $staffs = Staff::where('status', true)
            ->when($storeId && !$isAdmin, fn($q) => $q->where('store_id', $storeId))
            ->get();

        return view('admin.leave-requests.index', compact('leaves', 'staffs'));
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'staff_id' => 'required|exists:staffs,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'reason' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            LeaveRequest::create($request->all());
            return response()->json(['success' => true, 'message' => 'Leave request submitted successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $leave = LeaveRequest::findOrFail($id);
            $leave->update([
                'status' => $request->status,
                'admin_remarks' => $request->admin_remarks,
            ]);

            if ($request->status === 'approved') {
                $startDate = Carbon::parse($leave->start_date);
                $endDate = Carbon::parse($leave->end_date);
                for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
                    Attendance::updateOrCreate(
                        ['staff_id' => $leave->staff_id, 'date' => $date->format('Y-m-d')],
                        ['status' => 'leave', 'remarks' => 'Leave Approved']
                    );
                }
            }

            logActivity('Updated Status', 'Leave Request', "Leave request #{$id} {$request->status}");
            return response()->json(['success' => true, 'message' => 'Leave request updated successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function destroy($id)
    {
        try {
            $leave = LeaveRequest::findOrFail($id);
            $leave->delete();
            return response()->json(['success' => true, 'message' => 'Leave request deleted successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
