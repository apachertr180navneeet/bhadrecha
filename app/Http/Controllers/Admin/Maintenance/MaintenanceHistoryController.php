<?php

namespace App\Http\Controllers\Admin\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceHistory;
use App\Models\Vehicle;
use App\Models\ServiceSchedule;
use App\Models\SparePart;
use App\Models\Vendor;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class MaintenanceHistoryController extends Controller
{
    public function index()
    {
        $histories = MaintenanceHistory::with('vehicle', 'branch', 'vendor')
            ->latest()
            ->paginate(15);

        $vehicles = Vehicle::orderBy('vehicle_number')->get();

        return view('admin.maintenance.maintenance-history.index', compact('histories', 'vehicles'));
    }

    public function create()
    {
        $vehicles = Vehicle::orderBy('vehicle_number')->get();
        $serviceSchedules = ServiceSchedule::where('status', 'completed')->latest()->get();
        $spareParts = SparePart::latest()->get();
        $vendors = Vendor::where('status', 'active')->orderBy('name')->get();

        return view('admin.maintenance.maintenance-history.create', compact('vehicles', 'serviceSchedules', 'spareParts', 'vendors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'service_schedule_id' => 'nullable|exists:service_schedules,id',
            'spare_part_id' => 'nullable|exists:spare_parts,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'service_type' => 'required|string|max:255',
            'service_date' => "required|date|before_or_equal:9999-12-31",
            'current_km' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:2000',
            'vendor_name' => 'nullable|string|max:255',
            'cost' => 'nullable|numeric|min:0',
            'next_service_date' => "nullable|date|before_or_equal:9999-12-31",
            'next_service_km' => 'nullable|numeric|min:0',
            'status' => 'required|in:completed,pending,cancelled',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();
        $validated['company_id'] = $user->company_id;
        $validated['branch_id'] = $user->branch_id;

        $history = MaintenanceHistory::create($validated);

        ActivityLog::log('maintenance_history_created', "Maintenance history record created for vehicle", $history);

        return redirect()->route('admin.maintenance.maintenance-history.index')
            ->with('success', 'Maintenance history record created successfully');
    }

    public function show(MaintenanceHistory $maintenanceHistory)
    {
        $maintenanceHistory->load('vehicle', 'serviceSchedule', 'sparePart', 'vendor', 'branch', 'company');

        return view('admin.maintenance.maintenance-history.show', compact('maintenanceHistory'));
    }

    public function edit(MaintenanceHistory $maintenanceHistory)
    {
        $vehicles = Vehicle::orderBy('vehicle_number')->get();
        $serviceSchedules = ServiceSchedule::where('status', 'completed')->latest()->get();
        $spareParts = SparePart::latest()->get();
        $vendors = Vendor::where('status', 'active')->orderBy('name')->get();

        return view('admin.maintenance.maintenance-history.edit', compact('maintenanceHistory', 'vehicles', 'serviceSchedules', 'spareParts', 'vendors'));
    }

    public function update(Request $request, MaintenanceHistory $maintenanceHistory)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'service_schedule_id' => 'nullable|exists:service_schedules,id',
            'spare_part_id' => 'nullable|exists:spare_parts,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'service_type' => 'required|string|max:255',
            'service_date' => "required|date|before_or_equal:9999-12-31",
            'current_km' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:2000',
            'vendor_name' => 'nullable|string|max:255',
            'cost' => 'nullable|numeric|min:0',
            'next_service_date' => "nullable|date|before_or_equal:9999-12-31",
            'next_service_km' => 'nullable|numeric|min:0',
            'status' => 'required|in:completed,pending,cancelled',
            'notes' => 'nullable|string|max:1000',
        ]);

        $maintenanceHistory->update($validated);

        ActivityLog::log('maintenance_history_updated', "Maintenance history record updated", $maintenanceHistory);

        return redirect()->route('admin.maintenance.maintenance-history.index')
            ->with('success', 'Maintenance history record updated successfully');
    }

    public function destroy(MaintenanceHistory $maintenanceHistory)
    {
        $maintenanceHistory->delete();

        ActivityLog::log('maintenance_history_deleted', "Maintenance history record deleted", $maintenanceHistory);

        return redirect()->route('admin.maintenance.maintenance-history.index')
            ->with('success', 'Maintenance history record deleted successfully');
    }

    public function trashed()
    {
        $histories = MaintenanceHistory::onlyTrashed()->with('vehicle')->latest()->paginate(15);

        return view('admin.maintenance.maintenance-history.trashed', compact('histories'));
    }

    public function restore($id)
    {
        $history = MaintenanceHistory::onlyTrashed()->findOrFail($id);
        $history->restore();

        ActivityLog::log('maintenance_history_restored', "Maintenance history record restored", $history);

        return redirect()->route('admin.maintenance.maintenance-history.trashed')
            ->with('success', 'Maintenance history record restored successfully');
    }

    public function forceDelete($id)
    {
        $history = MaintenanceHistory::onlyTrashed()->findOrFail($id);
        $history->forceDelete();

        ActivityLog::log('maintenance_history_force_deleted', "Maintenance history record permanently deleted", $history);

        return redirect()->route('admin.maintenance.maintenance-history.trashed')
            ->with('success', 'Maintenance history record permanently deleted');
    }
}
