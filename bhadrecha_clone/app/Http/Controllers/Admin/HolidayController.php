<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Holiday;
use App\Models\Staff;
use App\Models\Attendance;
use Validator, Exception;

class HolidayController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view holidays')->only('index');
        $this->middleware('permission:create holidays')->only('store');
        $this->middleware('permission:edit holidays')->only(['edit', 'update']);
        $this->middleware('permission:delete holidays')->only('destroy');
    }

    public function index()
    {
        $storeId = auth()->user()->store_id;
        $isAdmin = auth()->user()->hasRole('Admin');

        $holidays = Holiday::when($storeId && !$isAdmin, fn($q) => $q->where('store_id', $storeId))
            ->orderBy('date', 'desc')
            ->paginate(15);

        return view('admin.holidays.index', compact('holidays'));
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'date' => 'required|date',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $request->all();
            $data['store_id'] = auth()->user()->hasRole('Admin') ? null : auth()->user()->store_id;
            $data['status'] = in_array($request->status, ['active', '1', 1], true) ? 1 : 0;
            
            $holiday = Holiday::create($data);
            
            if ($holiday->status) {
                $staffQuery = Staff::where('status', true);
                if ($holiday->store_id) {
                    $staffQuery->where('store_id', $holiday->store_id);
                }
                $staffs = $staffQuery->get();
                
                foreach ($staffs as $staff) {
                    Attendance::updateOrCreate(
                        ['staff_id' => $staff->id, 'date' => $holiday->date],
                        ['status' => 'holiday', 'remarks' => $holiday->name]
                    );
                }
            }
            
            return response()->json(['success' => true, 'message' => 'Holiday added successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $storeId = auth()->user()->store_id;
        $isAdmin = auth()->user()->hasRole('Admin');
        
        $holiday = Holiday::when($storeId && !$isAdmin, fn($q) => $q->where('store_id', $storeId))
            ->findOrFail($id);
            
        return response()->json($holiday);
    }

    public function update(Request $request, $id)
    {
        try {
            $storeId = auth()->user()->store_id;
            $isAdmin = auth()->user()->hasRole('Admin');
            
            $holiday = Holiday::when($storeId && !$isAdmin, fn($q) => $q->where('store_id', $storeId))
                ->findOrFail($id);
                
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'date' => 'required|date',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $request->all();
            if ($request->has('status')) {
                $data['status'] = in_array($request->status, ['active', '1', 1], true) ? 1 : 0;
            }
            
            $oldDate = $holiday->getOriginal('date');
            
            $holiday->update($data);
            
            $staffIds = Staff::when($holiday->store_id, fn($q) => $q->where('store_id', $holiday->store_id))->pluck('id');

            $oldDateFormatted = \Carbon\Carbon::parse($oldDate)->format('Y-m-d');
            $newDateFormatted = \Carbon\Carbon::parse($holiday->date)->format('Y-m-d');

            // If date changed or status became inactive, remove old attendances
            if (!$holiday->status || $oldDateFormatted !== $newDateFormatted) {
                Attendance::where('date', $oldDateFormatted)
                    ->where('status', 'holiday')
                    ->whereIn('staff_id', $staffIds)
                    ->delete();
            }

            if ($holiday->status) {
                $staffs = Staff::whereIn('id', $staffIds)->get();
                foreach ($staffs as $staff) {
                    Attendance::updateOrCreate(
                        ['staff_id' => $staff->id, 'date' => $holiday->date],
                        ['status' => 'holiday', 'remarks' => $holiday->name]
                    );
                }
            }

            return response()->json(['success' => true, 'message' => 'Holiday updated successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $storeId = auth()->user()->store_id;
            $isAdmin = auth()->user()->hasRole('Admin');
            
            $holiday = Holiday::when($storeId && !$isAdmin, fn($q) => $q->where('store_id', $storeId))
                ->findOrFail($id);
                
            $holidayDate = $holiday->date;
            $holidayStoreId = $holiday->store_id;

            $holiday->delete();
            
            $staffIds = Staff::when($holidayStoreId, fn($q) => $q->where('store_id', $holidayStoreId))->pluck('id');
            Attendance::where('date', $holidayDate)
                ->where('status', 'holiday')
                ->whereIn('staff_id', $staffIds)
                ->delete();

            return response()->json(['success' => true, 'message' => 'Holiday deleted successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
