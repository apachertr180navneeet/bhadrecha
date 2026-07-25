<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Bulty;
use App\Models\ActivityLog;
use App\Imports\VehicleImport;
use App\Exports\VehicleTemplateExport;
use App\Exports\VehiclesExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehicle::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('vehicle_number', 'like', "%{$search}%")
                  ->orWhere('vehicle_type', 'like', "%{$search}%")
                  ->orWhere('owner_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $vehicles = $query->orderBy('created_at', 'desc')->paginate(15);
        return view('admin.masters.vehicles.index', compact('vehicles'));
    }

    public function create()
    {
        return view('admin.masters.vehicles.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_number' => 'required|string|max:20|unique:vehicles,vehicle_number',
            'vehicle_type' => 'nullable|string|max:50',
            'make_model' => 'nullable|string|max:100',
            'capacity_tons' => 'nullable|numeric',
            'owner_name' => 'nullable|string|max:255',
            'owner_phone' => 'nullable|string|max:20',
            'insurance_expiry' => "nullable|date|before_or_equal:9999-12-31",
            'fitness_expiry' => "nullable|date|before_or_equal:9999-12-31",
            'permit_expiry' => "nullable|date|before_or_equal:9999-12-31",
            'pollution_expiry' => "nullable|date|before_or_equal:9999-12-31",
            'registration_cert' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'insurance_doc' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'fitness_doc' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'permit_doc' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'pollution_cert' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $validated['status'] = 'active';

        $docFields = ['registration_cert', 'insurance_doc', 'fitness_doc', 'permit_doc', 'pollution_cert'];
        $vehicle = Vehicle::create(array_diff_key($validated, array_flip($docFields)));
        $this->uploadDocuments($request, $vehicle);
        ActivityLog::log('vehicle_created', "Created vehicle: {$vehicle->vehicle_number}", $vehicle);

        return redirect()->route('admin.masters.vehicles.index')->with('success', 'Vehicle created successfully.');
    }

    public function edit(Vehicle $vehicle)
    {
        return view('admin.masters.vehicles.edit', compact('vehicle'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'vehicle_number' => 'required|string|max:20|unique:vehicles,vehicle_number,' . $vehicle->id,
            'vehicle_type' => 'nullable|string|max:50',
            'make_model' => 'nullable|string|max:100',
            'capacity_tons' => 'nullable|numeric',
            'owner_name' => 'nullable|string|max:255',
            'owner_phone' => 'nullable|string|max:20',
            'insurance_expiry' => "nullable|date|before_or_equal:9999-12-31",
            'fitness_expiry' => "nullable|date|before_or_equal:9999-12-31",
            'permit_expiry' => "nullable|date|before_or_equal:9999-12-31",
            'pollution_expiry' => "nullable|date|before_or_equal:9999-12-31",
            'registration_cert' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'insurance_doc' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'fitness_doc' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'permit_doc' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'pollution_cert' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $docFields = ['registration_cert', 'insurance_doc', 'fitness_doc', 'permit_doc', 'pollution_cert'];
        $vehicle->update(array_diff_key($validated, array_flip($docFields)));
        $this->uploadDocuments($request, $vehicle);
        ActivityLog::log('vehicle_updated', "Updated vehicle: {$vehicle->vehicle_number}", $vehicle);

        return redirect()->route('admin.masters.vehicles.index')->with('success', 'Vehicle updated successfully.');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);

        $import = new VehicleImport;
        try {
            Excel::import($import, $request->file('file'));
            $imported = $import->getImportedCount();
            $skipped = $import->getSkippedCount();
            $failures = $import->getFailures();
            $headings = $import->getHeadings();

            $message = "{$imported} vehicle(s) imported successfully.";
            if ($skipped > 0) $message .= " {$skipped} row(s) skipped (duplicate vehicle number).";
            if (!empty($failures)) {
                $errs = [];
                foreach ($failures as $f) $errs[] = "Row {$f->row()}: " . implode(', ', $f->errors());
                $message .= ' Errors: ' . implode(' | ', array_slice($errs, 0, 5));
                if (count($errs) > 5) $message .= ' ... and ' . (count($errs) - 5) . ' more.';
            }
            if ($imported === 0 && $skipped === 0 && empty($failures))
                $message .= ' No data found. Detected headers: ' . (!empty($headings) ? implode(', ', $headings) : 'none');

            ActivityLog::log('vehicles_imported', "Imported {$imported} vehicles from Excel, {$skipped} skipped");
            return redirect()->route('admin.masters.vehicles.index')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('admin.masters.vehicles.index')->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        ActivityLog::log('vehicle_template_downloaded', 'Downloaded vehicle import template');
        return Excel::download(new VehicleTemplateExport, 'vehicle_import_template.xlsx');
    }

    public function export()
    {
        ActivityLog::log('vehicles_exported', 'Exported vehicles to Excel');
        return Excel::download(new VehiclesExport, 'vehicles_export.xlsx');
    }

    public function trashed()
    {
        $vehicles = Vehicle::onlyTrashed()->paginate(15);
        return view('admin.masters.vehicles.trashed', compact('vehicles'));
    }

    public function restore($id)
    {
        $vehicle = Vehicle::withTrashed()->findOrFail($id);
        $vehicle->restore();
        ActivityLog::log('vehicle_restored', "Restored vehicle: {$vehicle->vehicle_number}");
        return redirect()->route('admin.masters.vehicles.trashed')->with('success', 'Vehicle restored successfully.');
    }

    public function forceDelete($id)
    {
        $vehicle = Vehicle::withTrashed()->findOrFail($id);
        ActivityLog::log('vehicle_force_deleted', "Force deleted vehicle: {$vehicle->vehicle_number}");
        $vehicle->forceDelete();
        return redirect()->route('admin.masters.vehicles.trashed')->with('success', 'Vehicle permanently deleted.');
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();
        ActivityLog::log('vehicle_deleted', "Deleted vehicle: {$vehicle->vehicle_number}");
        return redirect()->route('admin.masters.vehicles.index')->with('success', 'Vehicle deleted successfully.');
    }

    public function toggleStatus(Vehicle $vehicle)
    {
        $vehicle->status = $vehicle->status === 'active' ? 'inactive' : 'active';
        $vehicle->save();
        ActivityLog::log('vehicle_status_changed', "Changed status of vehicle: {$vehicle->vehicle_number}", $vehicle);
        return back()->with('success', 'Vehicle status updated.');
    }

    public function getDetailsByNumber(Request $request)
    {
        $vehicle = Vehicle::where('vehicle_number', $request->vehicle_number)->first();
        if ($vehicle) {
            $inUse = Bulty::where('vehicle_id', $vehicle->id)
                ->whereHas('trip', fn($q) => $q->where('status', 'pending'))
                ->exists();
            return response()->json([
                'success' => true,
                'vehicle' => $vehicle,
                'in_use' => $inUse,
            ]);
        }
        return response()->json(['success' => false]);
    }

    public function search(Request $request)
    {
        $term = $request->term;
        $excludeIds = Bulty::whereHas('trip', fn($q) => $q->where('status', 'pending'))
            ->pluck('vehicle_id')
            ->filter()
            ->unique();

        $vehicles = Vehicle::whereNotIn('id', $excludeIds)
            ->where(function ($q) use ($term) {
                $q->where('vehicle_number', 'like', "%{$term}%")
                  ->orWhere('owner_name', 'like', "%{$term}%")
                  ->orWhere('vehicle_type', 'like', "%{$term}%");
            })
            ->limit(10)
            ->get();

        return response()->json($vehicles);
    }

    private function uploadDocuments(Request $request, Vehicle $vehicle)
    {
        $docFields = ['registration_cert', 'insurance_doc', 'fitness_doc', 'permit_doc', 'pollution_cert'];
        $uploadPath = 'uploads/vehicles/' . $vehicle->id;

        foreach ($docFields as $field) {
            if ($request->hasFile($field)) {
                if ($vehicle->{$field}) {
                    $oldPath = str_replace(asset('uploads/'), '', $vehicle->{$field});
                    Storage::disk('uploads')->delete($oldPath);
                }
                $path = $request->file($field)->store($uploadPath, 'uploads');
                $fullUrl = asset('uploads/' . $path);
                $vehicle->update([$field => $fullUrl]);
            }
        }
    }

    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'vehicle_number' => 'required|string|max:20|unique:vehicles,vehicle_number',
            'vehicle_type' => 'nullable|string|max:50',
            'make_model' => 'nullable|string|max:100',
            'capacity_tons' => 'nullable|numeric',
            'owner_name' => 'nullable|string|max:255',
            'owner_phone' => 'nullable|string|max:20',
            'insurance_expiry' => "nullable|date|before_or_equal:9999-12-31",
            'fitness_expiry' => "nullable|date|before_or_equal:9999-12-31",
            'permit_expiry' => "nullable|date|before_or_equal:9999-12-31",
        ]);

        $validated['status'] = 'active';

        $vehicle = Vehicle::create($validated);
        ActivityLog::log('vehicle_created', "Quick created vehicle: {$vehicle->vehicle_number}", $vehicle);

        return response()->json([
            'success' => true,
            'vehicle' => $vehicle
        ]);
    }
}
