<?php

namespace App\Http\Controllers\Admin\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\SparePart;
use App\Models\Vehicle;
use App\Models\Supplier;
use App\Models\ActivityLog;
use App\Models\MaintenanceHistory;
use Illuminate\Http\Request;

class SparePartController extends Controller
{
    public function index()
    {
        $parts = SparePart::with('vehicle', 'supplier')->latest()->paginate(15);
        $vehicles = Vehicle::orderBy('vehicle_number')->get();

        return view('admin.maintenance.spare-part.index', compact('parts', 'vehicles'));
    }

    public function create()
    {
        $vehicles = Vehicle::orderBy('vehicle_number')->get();
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();

        return view('admin.maintenance.spare-part.create', compact('vehicles', 'suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'part_number' => 'nullable|string|max:255',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'quantity' => 'required|integer|min:0',
            'unit_price' => 'required|numeric|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'description' => 'nullable|string|max:1000',
            'part_change_date' => "nullable|date|before_or_equal:9999-12-31",
        ]);

        $validated['amount'] = ($validated['quantity'] ?? 0) * ($validated['unit_price'] ?? 0);
        $part = SparePart::create($validated);

        if ($part->vehicle_id) {
            MaintenanceHistory::create([
                'company_id' => $part->company_id ?? auth()->user()->company_id,
                'branch_id' => $part->branch_id ?? auth()->user()->branch_id,
                'vehicle_id' => $part->vehicle_id,
                'spare_part_id' => $part->id,
                'vendor_id' => $part->supplier_id,
                'service_type' => 'Spare Part Replacement: ' . $part->name,
                'service_date' => $part->part_change_date ?? now()->toDateString(),
                'cost' => $part->amount,
                'status' => 'completed',
                'description' => "Quantity: {$part->quantity}, Unit Price: {$part->unit_price}",
                'notes' => 'Auto-generated from spare part entry.',
            ]);
        }

        ActivityLog::log('spare_part_created', "Spare part created: {$part->name}", $part);

        return redirect()->route('admin.maintenance.spare-part.index')
            ->with('success', 'Spare part created successfully');
    }

    public function show(SparePart $sparePart)
    {
        $sparePart->load('vehicle', 'supplier');

        return view('admin.maintenance.spare-part.show', compact('sparePart'));
    }

    public function edit(SparePart $sparePart)
    {
        $vehicles = Vehicle::orderBy('vehicle_number')->get();
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();

        return view('admin.maintenance.spare-part.edit', compact('sparePart', 'vehicles', 'suppliers'));
    }

    public function update(Request $request, SparePart $sparePart)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'part_number' => 'nullable|string|max:255',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'quantity' => 'required|integer|min:0',
            'unit_price' => 'required|numeric|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'description' => 'nullable|string|max:1000',
            'part_change_date' => "nullable|date|before_or_equal:9999-12-31",
        ]);

        $validated['amount'] = ($validated['quantity'] ?? 0) * ($validated['unit_price'] ?? 0);
        $sparePart->update($validated);

        if ($sparePart->vehicle_id) {
            $existingHistory = MaintenanceHistory::where('spare_part_id', $sparePart->id)->first();
            
            $historyData = [
                'company_id' => $sparePart->company_id ?? auth()->user()->company_id,
                'branch_id' => $sparePart->branch_id ?? auth()->user()->branch_id,
                'vehicle_id' => $sparePart->vehicle_id,
                'spare_part_id' => $sparePart->id,
                'vendor_id' => $sparePart->supplier_id,
                'service_type' => 'Spare Part Replacement: ' . $sparePart->name,
                'service_date' => $sparePart->part_change_date ?? now()->toDateString(),
                'cost' => $sparePart->amount,
                'status' => 'completed',
                'description' => "Quantity: {$sparePart->quantity}, Unit Price: {$sparePart->unit_price}",
                'notes' => 'Auto-generated from spare part entry.',
            ];

            if ($existingHistory) {
                $existingHistory->update($historyData);
            } else {
                MaintenanceHistory::create($historyData);
            }
        } else {
            MaintenanceHistory::where('spare_part_id', $sparePart->id)->delete();
        }

        ActivityLog::log('spare_part_updated', "Spare part updated: {$sparePart->name}", $sparePart);

        return redirect()->route('admin.maintenance.spare-part.index')
            ->with('success', 'Spare part updated successfully');
    }

    public function destroy(SparePart $sparePart)
    {
        $sparePart->delete();

        ActivityLog::log('spare_part_deleted', "Spare part deleted: {$sparePart->name}", $sparePart);

        return redirect()->route('admin.maintenance.spare-part.index')
            ->with('success', 'Spare part deleted successfully');
    }

    public function trashed()
    {
        $parts = SparePart::onlyTrashed()->with('vehicle', 'supplier')->latest()->paginate(15);

        return view('admin.maintenance.spare-part.trashed', compact('parts'));
    }

    public function restore($id)
    {
        $part = SparePart::onlyTrashed()->findOrFail($id);
        $part->restore();

        ActivityLog::log('spare_part_restored', "Spare part restored: {$part->name}", $part);

        return redirect()->route('admin.maintenance.spare-part.trashed')
            ->with('success', 'Spare part restored successfully');
    }

    public function forceDelete($id)
    {
        $part = SparePart::onlyTrashed()->findOrFail($id);
        $part->forceDelete();

        ActivityLog::log('spare_part_force_deleted', "Spare part permanently deleted: {$part->name}", $part);

        return redirect()->route('admin.maintenance.spare-part.trashed')
            ->with('success', 'Spare part permanently deleted');
    }
}
