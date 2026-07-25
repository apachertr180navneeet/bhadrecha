<?php

namespace App\Http\Controllers\Admin\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\ServiceSchedule;
use App\Models\Vehicle;
use App\Models\ActivityLog;
use App\Models\MaintenanceHistory;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ServiceScheduleController extends Controller
{
    public function index()
    {
        $schedules = ServiceSchedule::with('vehicle', 'branch')
            ->latest()
            ->paginate(15);

        $vehicles = Vehicle::orderBy('vehicle_number')->get();

        return view('admin.maintenance.service-schedule.index', compact('schedules', 'vehicles'));
    }

    public function create()
    {
        $vehicles = Vehicle::orderBy('vehicle_number')->get();

        return view('admin.maintenance.service-schedule.create', compact('vehicles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'service_type' => 'required|string|max:255',
            'scheduled_date' => "nullable|date|before_or_equal:9999-12-31",
            'scheduled_km' => 'nullable|numeric|min:0',
            'last_service_date' => "nullable|date|before_or_equal:9999-12-31",
            'last_service_km' => 'nullable|numeric|min:0',
            'interval_days' => 'nullable|integer|min:0',
            'interval_km' => 'nullable|numeric|min:0',
            'status' => 'required|in:upcoming,overdue,completed,cancelled',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated = $this->autoSchedule($validated);

        $schedule = ServiceSchedule::create($validated);

        if ($schedule->status === 'completed') {
            MaintenanceHistory::create([
                'company_id' => $schedule->company_id ?? auth()->user()->company_id,
                'branch_id' => $schedule->branch_id ?? auth()->user()->branch_id,
                'vehicle_id' => $schedule->vehicle_id,
                'service_schedule_id' => $schedule->id,
                'service_type' => $schedule->service_type,
                'service_date' => $schedule->last_service_date ?? now()->toDateString(),
                'current_km' => $schedule->last_service_km,
                'status' => 'completed',
                'notes' => 'Auto-generated from completed service schedule.',
            ]);
        }

        ActivityLog::log('service_schedule_created', "Service schedule created for vehicle", $schedule);

        return redirect()->route('admin.maintenance.service-schedule.index')
            ->with('success', 'Service schedule created successfully');
    }

    public function show(ServiceSchedule $serviceSchedule)
    {
        $serviceSchedule->load('vehicle', 'branch', 'company');

        return view('admin.maintenance.service-schedule.show', compact('serviceSchedule'));
    }

    public function edit(ServiceSchedule $serviceSchedule)
    {
        $vehicles = Vehicle::orderBy('vehicle_number')->get();

        return view('admin.maintenance.service-schedule.edit', compact('serviceSchedule', 'vehicles'));
    }

    public function update(Request $request, ServiceSchedule $serviceSchedule)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'service_type' => 'required|string|max:255',
            'scheduled_date' => "nullable|date|before_or_equal:9999-12-31",
            'scheduled_km' => 'nullable|numeric|min:0',
            'last_service_date' => "nullable|date|before_or_equal:9999-12-31",
            'last_service_km' => 'nullable|numeric|min:0',
            'interval_days' => 'nullable|integer|min:0',
            'interval_km' => 'nullable|numeric|min:0',
            'status' => 'required|in:upcoming,overdue,completed,cancelled',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated = $this->autoSchedule($validated);

        $serviceSchedule->update($validated);

        if ($serviceSchedule->status === 'completed') {
            $existingHistory = MaintenanceHistory::where('service_schedule_id', $serviceSchedule->id)->first();
            if (!$existingHistory) {
                MaintenanceHistory::create([
                    'company_id' => $serviceSchedule->company_id ?? auth()->user()->company_id,
                    'branch_id' => $serviceSchedule->branch_id ?? auth()->user()->branch_id,
                    'vehicle_id' => $serviceSchedule->vehicle_id,
                    'service_schedule_id' => $serviceSchedule->id,
                    'service_type' => $serviceSchedule->service_type,
                    'service_date' => $serviceSchedule->last_service_date ?? now()->toDateString(),
                    'current_km' => $serviceSchedule->last_service_km,
                    'status' => 'completed',
                    'notes' => 'Auto-generated from completed service schedule.',
                ]);
            }
        }

        ActivityLog::log('service_schedule_updated', "Service schedule updated for vehicle", $serviceSchedule);

        return redirect()->route('admin.maintenance.service-schedule.index')
            ->with('success', 'Service schedule updated successfully');
    }

    public function destroy(ServiceSchedule $serviceSchedule)
    {
        $serviceSchedule->delete();

        ActivityLog::log('service_schedule_deleted', "Service schedule deleted", $serviceSchedule);

        return redirect()->route('admin.maintenance.service-schedule.index')
            ->with('success', 'Service schedule deleted successfully');
    }

    public function trashed()
    {
        $schedules = ServiceSchedule::onlyTrashed()->with('vehicle')->latest()->paginate(15);

        return view('admin.maintenance.service-schedule.trashed', compact('schedules'));
    }

    public function restore($id)
    {
        $schedule = ServiceSchedule::onlyTrashed()->findOrFail($id);
        $schedule->restore();

        ActivityLog::log('service_schedule_restored', "Service schedule restored", $schedule);

        return redirect()->route('admin.maintenance.service-schedule.trashed')
            ->with('success', 'Service schedule restored successfully');
    }

    public function forceDelete($id)
    {
        $schedule = ServiceSchedule::onlyTrashed()->findOrFail($id);
        $schedule->forceDelete();

        ActivityLog::log('service_schedule_force_deleted', "Service schedule permanently deleted", $schedule);

        return redirect()->route('admin.maintenance.service-schedule.trashed')
            ->with('success', 'Service schedule permanently deleted');
    }

    public function markCompleted(ServiceSchedule $serviceSchedule)
    {
        $lastServiceDate = $serviceSchedule->scheduled_date ?? now()->toDateString();
        $lastServiceKm = $serviceSchedule->scheduled_km ?? $serviceSchedule->last_service_km;

        $serviceSchedule->update([
            'status' => 'completed',
            'last_service_date' => $lastServiceDate,
            'last_service_km' => $lastServiceKm,
        ]);

        $existingHistory = MaintenanceHistory::where('service_schedule_id', $serviceSchedule->id)->first();
        if (!$existingHistory) {
            MaintenanceHistory::create([
                'company_id' => $serviceSchedule->company_id ?? auth()->user()->company_id,
                'branch_id' => $serviceSchedule->branch_id ?? auth()->user()->branch_id,
                'vehicle_id' => $serviceSchedule->vehicle_id,
                'service_schedule_id' => $serviceSchedule->id,
                'service_type' => $serviceSchedule->service_type,
                'service_date' => $lastServiceDate,
                'current_km' => $lastServiceKm,
                'status' => 'completed',
                'notes' => 'Auto-generated from completed service schedule.',
            ]);
        }

        if ($serviceSchedule->interval_days || $serviceSchedule->interval_km) {
            $nextDate = $serviceSchedule->interval_days && $lastServiceDate
                ? Carbon::parse($lastServiceDate)->addDays($serviceSchedule->interval_days)->toDateString()
                : null;
            $nextKm = $serviceSchedule->interval_km && $lastServiceKm
                ? $lastServiceKm + $serviceSchedule->interval_km
                : null;

            ServiceSchedule::create([
                'vehicle_id' => $serviceSchedule->vehicle_id,
                'service_type' => $serviceSchedule->service_type,
                'scheduled_date' => $nextDate,
                'scheduled_km' => $nextKm,
                'last_service_date' => $lastServiceDate,
                'last_service_km' => $lastServiceKm,
                'interval_days' => $serviceSchedule->interval_days,
                'interval_km' => $serviceSchedule->interval_km,
                'status' => 'upcoming',
                'notes' => $serviceSchedule->notes,
            ]);
        }

        ActivityLog::log('service_schedule_completed', "Service completed, next schedule auto-created", $serviceSchedule);

        return redirect()->route('admin.maintenance.service-schedule.index')
            ->with('success', 'Service completed. Next schedule auto-created.');
    }

    private function autoSchedule(array $data): array
    {
        if (empty($data['scheduled_date']) && !empty($data['last_service_date']) && !empty($data['interval_days'])) {
            $data['scheduled_date'] = Carbon::parse($data['last_service_date'])
                ->addDays($data['interval_days'])
                ->toDateString();
        }

        if (empty($data['scheduled_km']) && !empty($data['last_service_km']) && !empty($data['interval_km'])) {
            $data['scheduled_km'] = $data['last_service_km'] + $data['interval_km'];
        }

        return $data;
    }
}
