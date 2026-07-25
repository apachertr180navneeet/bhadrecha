<?php

namespace App\Http\Controllers\Admin\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\Breakdown;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Vendor;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BreakdownController extends Controller
{
    public function index()
    {
        $breakdowns = Breakdown::with('vehicle', 'driver', 'vendor')
            ->latest()
            ->paginate(15);

        $vehicles = Vehicle::orderBy('vehicle_number')->get();

        return view('admin.maintenance.breakdowns.index', compact('breakdowns', 'vehicles'));
    }

    public function create()
    {
        $vehicles = Vehicle::orderBy('vehicle_number')->get();
        $drivers = Driver::where('status', 'active')->orderBy('name')->get();
        $vendors = Vendor::where('status', 'active')->orderBy('name')->get();

        return view('admin.maintenance.breakdowns.create', compact('vehicles', 'drivers', 'vendors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'breakdown_date' => "required|date|before_or_equal:9999-12-31",
            'breakdown_time' => 'nullable|date_format:H:i',
            'location' => 'required|string|max:500',
            'latitude' => 'nullable|string|max:50',
            'longitude' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:2000',
            'issue_type' => 'required|string|max:255',
            'severity' => 'required|in:minor,major,critical',
            'vendor_id' => 'nullable|exists:vendors,id',
            'repair_cost' => 'nullable|numeric|min:0',
            'downtime_hours' => 'nullable|numeric|min:0',
            'status' => 'required|in:reported,in_progress,resolved,towed',
            'resolution_notes' => 'nullable|string|max:2000',
        ]);

        $user = auth()->user();
        $validated['company_id'] = $user->company_id;
        $validated['branch_id'] = $user->branch_id;

        if ($validated['status'] === 'resolved' && empty($validated['resolved_at'])) {
            $validated['resolved_at'] = now();
        }

        $breakdown = Breakdown::create($validated);

        ActivityLog::log('breakdown_created', "Breakdown reported for vehicle", $breakdown);

        return redirect()->route('admin.maintenance.breakdowns.index')
            ->with('success', 'Breakdown reported successfully');
    }

    public function show(Breakdown $breakdown)
    {
        $breakdown->load('vehicle', 'driver', 'vendor', 'branch', 'company');

        return view('admin.maintenance.breakdowns.show', compact('breakdown'));
    }

    public function edit(Breakdown $breakdown)
    {
        $vehicles = Vehicle::orderBy('vehicle_number')->get();
        $drivers = Driver::where('status', 'active')->orderBy('name')->get();
        $vendors = Vendor::where('status', 'active')->orderBy('name')->get();

        return view('admin.maintenance.breakdowns.edit', compact('breakdown', 'vehicles', 'drivers', 'vendors'));
    }

    public function update(Request $request, Breakdown $breakdown)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'breakdown_date' => "required|date|before_or_equal:9999-12-31",
            'breakdown_time' => 'nullable|date_format:H:i',
            'location' => 'required|string|max:500',
            'latitude' => 'nullable|string|max:50',
            'longitude' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:2000',
            'issue_type' => 'required|string|max:255',
            'severity' => 'required|in:minor,major,critical',
            'vendor_id' => 'nullable|exists:vendors,id',
            'repair_cost' => 'nullable|numeric|min:0',
            'downtime_hours' => 'nullable|numeric|min:0',
            'status' => 'required|in:reported,in_progress,resolved,towed',
            'resolution_notes' => 'nullable|string|max:2000',
        ]);

        if ($validated['status'] === 'resolved' && empty($breakdown->resolved_at)) {
            $validated['resolved_at'] = now();
        }

        if ($validated['status'] !== 'resolved') {
            $validated['resolved_at'] = null;
        }

        $breakdown->update($validated);

        ActivityLog::log('breakdown_updated', "Breakdown record updated", $breakdown);

        return redirect()->route('admin.maintenance.breakdowns.index')
            ->with('success', 'Breakdown record updated successfully');
    }

    public function destroy(Breakdown $breakdown)
    {
        $breakdown->delete();

        ActivityLog::log('breakdown_deleted', "Breakdown record deleted", $breakdown);

        return redirect()->route('admin.maintenance.breakdowns.index')
            ->with('success', 'Breakdown record deleted successfully');
    }

    public function trashed()
    {
        $breakdowns = Breakdown::onlyTrashed()->with('vehicle')->latest()->paginate(15);

        return view('admin.maintenance.breakdowns.trashed', compact('breakdowns'));
    }

    public function restore($id)
    {
        $breakdown = Breakdown::onlyTrashed()->findOrFail($id);
        $breakdown->restore();

        ActivityLog::log('breakdown_restored', "Breakdown record restored", $breakdown);

        return redirect()->route('admin.maintenance.breakdowns.trashed')
            ->with('success', 'Breakdown record restored successfully');
    }

    public function forceDelete($id)
    {
        $breakdown = Breakdown::onlyTrashed()->findOrFail($id);
        $breakdown->forceDelete();

        ActivityLog::log('breakdown_force_deleted', "Breakdown record permanently deleted", $breakdown);

        return redirect()->route('admin.maintenance.breakdowns.trashed')
            ->with('success', 'Breakdown record permanently deleted');
    }

    public function markResolved(Breakdown $breakdown)
    {
        $breakdown->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        ActivityLog::log('breakdown_resolved', "Breakdown marked as resolved", $breakdown);

        return redirect()->route('admin.maintenance.breakdowns.index')
            ->with('success', 'Breakdown marked as resolved');
    }
}
