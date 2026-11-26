<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Package;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Duration;

class PackageController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view packages')->only(['index', 'show']);
        $this->middleware('permission:create packages')->only(['create', 'store']);
        $this->middleware('permission:edit packages')->only(['edit', 'update', 'toggleStatus']);
        $this->middleware('permission:delete packages')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $packages = Package::with(['customer', 'duration'])->latest()->get();
        $services = Service::all()->keyBy('id');
        return view('admin.packages.index', compact('packages', 'services'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $services = Service::orderBy('service_name')->get();
        $durations = Duration::where('status', 1)->orderBy('name')->get();
        
        return view('admin.packages.create', compact('customers', 'services', 'durations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'duration_id' => 'required|exists:durations,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'nullable|array',
            'payment_amount' => 'nullable|array',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $data = $request->all();
        
        // Process per-service quantities
        $serviceItems = [];
        $totalServicesQty = 0;

        if ($request->has('service_ids') && is_array($request->service_ids)) {
            $quantities = $request->input('service_quantities', []);
            $prices = $request->input('service_prices', []);
            foreach ($request->service_ids as $index => $sId) {
                if (!empty($sId)) {
                    $qty = isset($quantities[$index]) ? (int)$quantities[$index] : (isset($quantities[$sId]) ? (int)$quantities[$sId] : 1);
                    $qty = max(1, $qty);
                    $price = isset($prices[$index]) ? (float)$prices[$index] : (isset($prices[$sId]) ? (float)$prices[$sId] : null);
                    $itemData = [
                        'service_id' => (int)$sId,
                        'qty' => $qty
                    ];
                    if ($price !== null) {
                        $itemData['price'] = max(0, $price);
                    }
                    $serviceItems[] = $itemData;
                    $totalServicesQty += $qty;
                }
            }
        } elseif ($request->has('services') && is_array($request->services)) {
            foreach ($request->services as $sVal) {
                if (is_array($sVal)) {
                    $sId = (int)($sVal['service_id'] ?? $sVal['id']);
                    $qty = max(1, (int)($sVal['qty'] ?? 1));
                } else {
                    $sId = (int)$sVal;
                    $qty = 1;
                }
                $serviceItems[] = [
                    'service_id' => $sId,
                    'qty' => $qty
                ];
                $totalServicesQty += $qty;
            }
        }

        $data['services'] = $serviceItems;
        $data['qty'] = $totalServicesQty > 0 ? $totalServicesQty : (int)($request->input('qty', 1));

        $payment_history = [];
        $total_advance = 0;

        if ($request->has('payment_date') && $request->has('payment_amount')) {
            $dates = $request->payment_date;
            $amounts = $request->payment_amount;
            
            for ($i = 0; $i < count($dates); $i++) {
                if (!empty($dates[$i]) && $amounts[$i] > 0) {
                    $payment_history[] = [
                        'date' => $dates[$i],
                        'amount' => (float) $amounts[$i]
                    ];
                    $total_advance += (float) $amounts[$i];
                }
            }
        }

        $data['payment_history'] = $payment_history;
        $data['advance'] = $total_advance;
        $data['remaining'] = max(0, $request->amount - $total_advance);

        Package::create($data);

        return redirect()->route('admin.packages.index')->with('success', 'Package created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $package = Package::findOrFail($id);
        $customers = Customer::orderBy('name')->get();
        $services = Service::orderBy('service_name')->get();
        $durations = Duration::where('status', 1)->orderBy('name')->get();
        
        return view('admin.packages.edit', compact('package', 'customers', 'services', 'durations'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'duration_id' => 'required|exists:durations,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'nullable|array',
            'payment_amount' => 'nullable|array',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $package = Package::findOrFail($id);
        $data = $request->all();
        
        // Process per-service quantities
        $serviceItems = [];
        $totalServicesQty = 0;

        if ($request->has('service_ids') && is_array($request->service_ids)) {
            $quantities = $request->input('service_quantities', []);
            $prices = $request->input('service_prices', []);
            foreach ($request->service_ids as $index => $sId) {
                if (!empty($sId)) {
                    $qty = isset($quantities[$index]) ? (int)$quantities[$index] : (isset($quantities[$sId]) ? (int)$quantities[$sId] : 1);
                    $qty = max(1, $qty);
                    $price = isset($prices[$index]) ? (float)$prices[$index] : (isset($prices[$sId]) ? (float)$prices[$sId] : null);
                    $itemData = [
                        'service_id' => (int)$sId,
                        'qty' => $qty
                    ];
                    if ($price !== null) {
                        $itemData['price'] = max(0, $price);
                    }
                    $serviceItems[] = $itemData;
                    $totalServicesQty += $qty;
                }
            }
        } elseif ($request->has('services') && is_array($request->services)) {
            foreach ($request->services as $sVal) {
                if (is_array($sVal)) {
                    $sId = (int)($sVal['service_id'] ?? $sVal['id']);
                    $qty = max(1, (int)($sVal['qty'] ?? 1));
                } else {
                    $sId = (int)$sVal;
                    $qty = 1;
                }
                $serviceItems[] = [
                    'service_id' => $sId,
                    'qty' => $qty
                ];
                $totalServicesQty += $qty;
            }
        }

        $data['services'] = $serviceItems;
        $data['qty'] = $totalServicesQty > 0 ? $totalServicesQty : (int)($request->input('qty', 1));

        $payment_history = [];
        $total_advance = 0;

        if ($request->has('payment_date') && $request->has('payment_amount')) {
            $dates = $request->payment_date;
            $amounts = $request->payment_amount;
            
            for ($i = 0; $i < count($dates); $i++) {
                if (!empty($dates[$i]) && $amounts[$i] > 0) {
                    $payment_history[] = [
                        'date' => $dates[$i],
                        'amount' => (float) $amounts[$i]
                    ];
                    $total_advance += (float) $amounts[$i];
                }
            }
        }

        $data['payment_history'] = $payment_history;
        $data['advance'] = $total_advance;
        $data['remaining'] = max(0, $request->amount - $total_advance);
        
        $package->update($data);

        return redirect()->route('admin.packages.index')->with('success', 'Package updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $package = Package::findOrFail($id);
        $package->delete();

        return response()->json(['success' => true, 'message' => 'Package deleted successfully.']);
    }

    /**
     * Add a new payment to the package from the modal.
     */
    public function addPayment(Request $request, string $id)
    {
        $request->validate([
            'payment_date' => 'required|date',
            'payment_amount' => 'required|numeric|min:1',
        ]);

        $package = Package::findOrFail($id);
        
        $payment_history = is_array($package->payment_history) ? $package->payment_history : [];
        
        // Append new payment
        $payment_history[] = [
            'date' => $request->payment_date,
            'amount' => (float) $request->payment_amount
        ];

        // Recalculate advance and remaining
        $total_advance = 0;
        foreach ($payment_history as $payment) {
            $total_advance += (float) ($payment['amount'] ?? 0);
        }

        $package->update([
            'payment_history' => $payment_history,
            'advance' => $total_advance,
            'remaining' => $package->amount - $total_advance
        ]);

        return redirect()->back()->with('success', 'Payment added successfully.');
    }

    /**
     * Get usage details of a package for AJAX modal.
     */
    public function usageDetails(string $id)
    {
        $package = Package::with(['customer', 'duration'])->findOrFail($id);
        $usage = $package->getUsageDetails();

        $appointmentsData = $usage['appointments']->map(function($apt) {
            $serviceNames = [];
            $staffNames = [];

            if ($apt->appointmentServices && $apt->appointmentServices->count() > 0) {
                foreach ($apt->appointmentServices as $item) {
                    if ($item->service && !empty($item->service->service_name)) {
                        $serviceNames[] = $item->service->service_name;
                    }
                    $members = $item->staffMembers();
                    $sNames = $members->pluck('full_name')->filter()->all();
                    if (!empty($sNames)) {
                        $staffNames = array_merge($staffNames, $sNames);
                    } elseif ($item->staff) {
                        $name = $item->staff->full_name ?: ($item->staff->name ?? '');
                        if (!empty($name)) {
                            $staffNames[] = $name;
                        }
                    }
                }
            }

            if (empty($serviceNames) && $apt->service) {
                $serviceNames[] = $apt->service->service_name;
            }

            if (empty($staffNames) && $apt->staff) {
                $name = $apt->staff->full_name ?: ($apt->staff->name ?? '');
                if (!empty($name)) {
                    $staffNames[] = $name;
                }
            }

            $serviceName = !empty($serviceNames) ? implode(', ', array_unique(array_filter($serviceNames))) : 'N/A';
            $staffName = !empty($staffNames) ? implode(', ', array_unique(array_filter($staffNames))) : 'N/A';

            return [
                'id' => $apt->id,
                'appointment_number' => $apt->appointment_number ?? ('APT-' . $apt->id),
                'appointment_date' => $apt->appointment_date ? \Carbon\Carbon::parse($apt->appointment_date)->format('d-m-Y h:i A') : '-',
                'service_name' => $serviceName,
                'staff_name' => $staffName,
                'status' => ucfirst($apt->status),
                'final_amount' => number_format($apt->final_amount, 2),
            ];
        });

        return response()->json([
            'success' => true,
            'package' => [
                'id' => $package->id,
                'customer_name' => $package->customer->name ?? 'N/A',
                'start_date' => $package->start_date ? \Carbon\Carbon::parse($package->start_date)->format('d-m-Y') : '-',
                'end_date' => $package->end_date ? \Carbon\Carbon::parse($package->end_date)->format('d-m-Y') : '-',
                'amount' => number_format($package->amount, 2),
            ],
            'usage' => [
                'services_breakdown' => $usage['services_breakdown'],
                'total_allocated_qty' => $usage['total_allocated_qty'],
                'total_used_qty' => $usage['total_used_qty'],
                'total_remaining_qty' => $usage['total_remaining_qty'],
                'status' => $usage['status'],
                'appointments' => $appointmentsData,
            ]
        ]);
    }
}

