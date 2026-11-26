<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\Customer;
use App\Models\Staff;
use App\Models\Service;
use App\Models\Store;
use App\Models\Branch;
use App\Models\WhatsappLog;
use Carbon\Carbon;
use Validator, Exception, DB;

class AppointmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view appointments')->only(['index', 'show']);
        $this->middleware('permission:create appointments')->only(['create', 'store']);
        $this->middleware('permission:edit appointments')->only(['edit', 'update', 'updateStatus', 'reschedule']);
        $this->middleware('permission:delete appointments')->only('destroy');
    }

    private function getStoreBranchPermissions(Request $request = null)
    {
        $user = auth()->user();
        $userStoreId = $user->store_id;
        $userBranchId = $user->branch_id;

        $isManager = $user->hasRole('Manager')
            || $user->hasRole('Store Manager')
            || in_array(strtolower($user->role ?? ''), ['manager', 'store_manager', 'store manager']);

        $isAdmin = ($user->hasRole('Admin')
            || $user->hasRole('Super Admin')
            || in_array(strtolower($user->role ?? ''), ['admin', 'super_admin']))
            && !$isManager;

        $canViewAllStores = $isAdmin;
        $canViewAllBranches = $isAdmin;

        $selectedStoreId = (!$canViewAllStores && $userStoreId) 
            ? $userStoreId 
            : ($request ? $request->get('store_id') : null);

        $selectedBranchId = (!$canViewAllBranches && $userBranchId) 
            ? $userBranchId 
            : ($request ? $request->get('branch_id') : null);

        if (!$selectedStoreId && $selectedBranchId) {
            $branchObj = Branch::find($selectedBranchId);
            if ($branchObj) {
                $selectedStoreId = $branchObj->store_id;
            }
        }

        return [
            'user' => $user,
            'isAdmin' => $isAdmin,
            'isManager' => $isManager,
            'canViewAllStores' => $canViewAllStores,
            'canViewAllBranches' => $canViewAllBranches,
            'selectedStoreId' => $selectedStoreId,
            'selectedBranchId' => $selectedBranchId,
            'userStoreId' => $userStoreId,
            'userBranchId' => $userBranchId,
        ];
    }

    public function index(Request $request)
    {
        $perm = $this->getStoreBranchPermissions($request);
        $canViewAllStores = $perm['canViewAllStores'];
        $canViewAllBranches = $perm['canViewAllBranches'];
        $selectedStoreId = $perm['selectedStoreId'];
        $selectedBranchId = $perm['selectedBranchId'];

        $appointments = Appointment::with(['customer', 'staff', 'service', 'store', 'branch', 'appointmentServices.service', 'appointmentServices.staff'])
            ->when($selectedStoreId, function ($q) use ($selectedStoreId) {
                $q->where(function ($sub) use ($selectedStoreId) {
                    $sub->where('store_id', $selectedStoreId)
                        ->orWhereHas('branch', fn($bq) => $bq->where('store_id', $selectedStoreId))
                        ->orWhereHas('staff', fn($sq) => $sq->where('store_id', $selectedStoreId))
                        ->orWhereHas('appointmentServices.staff', fn($asq) => $asq->where('store_id', $selectedStoreId));
                });
            })
            ->when($selectedBranchId, function ($q) use ($selectedBranchId) {
                $q->where(function ($sub) use ($selectedBranchId) {
                    $sub->where('branch_id', $selectedBranchId)
                        ->orWhereHas('staff', fn($sq) => $sq->where('branch_id', $selectedBranchId))
                        ->orWhereHas('appointmentServices.staff', fn($asq) => $asq->where('branch_id', $selectedBranchId));
                });
            })
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('customer_id'), fn($q) => $q->where('customer_id', $request->customer_id))
            ->when($request->filled('staff_id'), function ($q) use ($request) {
                $staffId = (int) $request->staff_id;
                $q->where(function ($sub) use ($staffId) {
                    $sub->where('staff_id', $staffId)
                        ->orWhereHas('appointmentServices', function ($asQ) use ($staffId) {
                            $asQ->where('staff_id', $staffId)
                                ->orWhereJsonContains('staff_ids', $staffId)
                                ->orWhereJsonContains('staff_ids', (string)$staffId);
                        });
                });
            })
            ->when($request->filled('date_range'), function ($q) use ($request) {
                $dates = explode(' to ', $request->date_range);
                if (count($dates) === 2) {
                    $q->whereBetween('appointment_date', [
                        Carbon::parse(trim($dates[0]))->startOfDay(),
                        Carbon::parse(trim($dates[1]))->endOfDay(),
                    ]);
                } elseif (count($dates) === 1 && !empty(trim($dates[0]))) {
                    $q->whereDate('appointment_date', Carbon::parse(trim($dates[0]))->format('Y-m-d'));
                }
            })
            ->latest()->get();

        $customers = Customer::all();
        $staffs = Staff::where('status', true)->get();
        $services = Service::where('status', true)->get();
        $stores = Store::all();
        $branches = Branch::when($selectedStoreId, fn($q) => $q->where('store_id', $selectedStoreId))->get();

        $adjustedBalances = Appointment::calculateAdjustedBalancesForAppointments($appointments);

        return view('admin.appointments.index', compact('appointments', 'adjustedBalances', 'customers', 'staffs', 'services', 'stores', 'branches', 'selectedStoreId', 'selectedBranchId', 'canViewAllStores', 'canViewAllBranches'));
    }

    public function calendar(Request $request)
    {
        $perm = $this->getStoreBranchPermissions($request);
        $canViewAllStores = $perm['canViewAllStores'];
        $canViewAllBranches = $perm['canViewAllBranches'];
        $selectedStoreId = $perm['selectedStoreId'];
        $selectedBranchId = $perm['selectedBranchId'];

        $appointments = Appointment::with(['customer', 'staff', 'service', 'branch', 'appointmentServices.service', 'appointmentServices.staff'])
            ->when($selectedStoreId, function ($q) use ($selectedStoreId) {
                $q->where(function ($sub) use ($selectedStoreId) {
                    $sub->where('store_id', $selectedStoreId)
                        ->orWhereHas('branch', fn($bq) => $bq->where('store_id', $selectedStoreId))
                        ->orWhereHas('staff', fn($sq) => $sq->where('store_id', $selectedStoreId))
                        ->orWhereHas('appointmentServices.staff', fn($asq) => $asq->where('store_id', $selectedStoreId));
                });
            })
            ->when($selectedBranchId, function ($q) use ($selectedBranchId) {
                $q->where(function ($sub) use ($selectedBranchId) {
                    $sub->where('branch_id', $selectedBranchId)
                        ->orWhereHas('staff', fn($sq) => $sq->where('branch_id', $selectedBranchId))
                        ->orWhereHas('appointmentServices.staff', fn($asq) => $asq->where('branch_id', $selectedBranchId));
                });
            })
            ->get();

        $events = $appointments->map(function ($app) {
            $color = [
                'pending' => '#ffc107',
                'confirmed' => '#0dcaf0',
                'checked_in' => '#696cff',
                'in_progress' => '#0d6efd',
                'completed' => '#198754',
                'cancelled' => '#dc3545',
                'no_show' => '#8592a3',
            ];
            
            $serviceName = $app->appointmentServices->count() > 0 
                ? $app->appointmentServices->map(fn($s) => $s->service->service_name ?? 'N/A')->implode(', ')
                : ($app->service->service_name ?? 'N/A');

            return [
                'id' => $app->id,
                'title' => ($app->customer->name ?? 'N/A') . ' - ' . $serviceName,
                'start' => $app->appointment_date ? $app->appointment_date->format('Y-m-d\TH:i:s') : null,
                'end' => $app->end_time ? $app->end_time->format('Y-m-d\TH:i:s') : null,
                'backgroundColor' => $color[$app->status] ?? '#6c757d',
                'borderColor' => $color[$app->status] ?? '#6c757d',
                'extendedProps' => [
                    'status' => $app->status,
                    'customer' => $app->customer->name ?? 'N/A',
                    'staff' => $app->staff->full_name ?? 'N/A',
                    'service' => $serviceName,
                    'branch' => $app->branch->name ?? 'N/A',
                ],
            ];
        });

        $stores = Store::all();
        $branches = Branch::when($selectedStoreId, fn($q) => $q->where('store_id', $selectedStoreId))->get();

        return view('admin.appointments.calendar', compact('events', 'stores', 'branches', 'selectedStoreId', 'selectedBranchId', 'canViewAllStores', 'canViewAllBranches'));
    }

    public function show($id)
    {
        try {
            $appointment = Appointment::with(['customer', 'staff', 'service', 'appointmentServices.service', 'appointmentServices.staff'])->findOrFail($id);

            $perm = $this->getStoreBranchPermissions();
            if (!$perm['canViewAllStores']) {
                if ($perm['selectedStoreId'] && $appointment->store_id && $appointment->store_id != $perm['selectedStoreId']) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized store access.'], 403);
                }
                if ($perm['selectedBranchId'] && $appointment->branch_id && $appointment->branch_id != $perm['selectedBranchId']) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized branch access.'], 403);
                }
            }
            
            $servicesList = $appointment->appointmentServices->map(function ($item) {
                $staffMembers = $item->staffMembers();
                $staffNames = $staffMembers->pluck('full_name')->filter()->implode(', ');
                if (empty($staffNames) && $item->staff) {
                    $staffNames = $item->staff->full_name;
                }

                return [
                    'service_id' => $item->service_id,
                    'service_name' => $item->service ? $item->service->service_name : 'N/A',
                    'staff_id' => $item->staff_id,
                    'staff_ids' => $item->staff_ids ?? ($item->staff_id ? [$item->staff_id] : []),
                    'staff_name' => !empty($staffNames) ? $staffNames : 'Unassigned',
                    'price' => number_format((float)$item->price, 2),
                    'duration' => $item->duration,
                ];
            });

            if ($servicesList->isEmpty() && $appointment->service) {
                $servicesList = collect([[
                    'service_id' => $appointment->service_id,
                    'service_name' => $appointment->service->service_name,
                    'staff_id' => $appointment->staff_id,
                    'staff_ids' => $appointment->staff_id ? [$appointment->staff_id] : [],
                    'staff_name' => $appointment->staff ? $appointment->staff->full_name : 'Unassigned',
                    'price' => number_format((float)$appointment->total_amount, 2),
                    'duration' => $appointment->service->duration ?? 0,
                ]]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $appointment->id,
                    'appointment_number' => $appointment->appointment_number ?? '#' . $appointment->id,
                    'customer' => $appointment->customer ? ['name' => $appointment->customer->name] : null,
                    'staff' => $appointment->staff ? ['name' => $appointment->staff->full_name] : null,
                    'service' => $appointment->service ? ['name' => $appointment->service->service_name] : null,
                    'services' => $servicesList,
                    'date_time' => $appointment->appointment_date ? $appointment->appointment_date->format('d M Y, h:i A') : 'N/A',
                    'total_amount' => $appointment->total_amount,
                    'discount' => $appointment->discount,
                    'final_amount' => $appointment->final_amount,
                    'paid_amount' => $appointment->paid_amount ?? 0,
                    'credit_amount' => $appointment->credit_amount,
                    'debit_amount' => $appointment->debit_amount,
                    'adjusted_balance' => (Appointment::calculateAdjustedBalancesForAppointments(collect([$appointment])))[$appointment->id] ?? [
                        'raw_credit' => $appointment->credit_amount,
                        'raw_debit' => $appointment->debit_amount,
                        'adjusted_credit' => $appointment->credit_amount,
                        'adjusted_debit' => $appointment->debit_amount,
                        'credit_used' => 0,
                        'credit_covered' => 0,
                    ],
                    'customer_balance' => $appointment->customer ? $appointment->customer->getBalanceDetails() : null,
                    'amount' => $appointment->final_amount ?? $appointment->total_amount,
                    'status' => $appointment->status,
                    'payment_type' => $appointment->payment_type,
                    'cash_amount' => (float)($appointment->cash_amount ?? 0),
                    'upi_amount' => (float)($appointment->upi_amount ?? 0),
                    'card_amount' => (float)($appointment->card_amount ?? 0),
                    'upi_reference' => $appointment->upi_reference,
                    'notes' => $appointment->notes,
                ]
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function create()
    {
        $perm = $this->getStoreBranchPermissions();
        $canViewAllStores = $perm['canViewAllStores'];
        $canViewAllBranches = $perm['canViewAllBranches'];
        $selectedStoreId = $perm['selectedStoreId'];
        $selectedBranchId = $perm['selectedBranchId'];

        $customers = Customer::all();
        $staffs = Staff::where('status', true)->get();
        $services = Service::where('status', true)->get();
        $stores = Store::all();
        $branches = Branch::when($selectedStoreId, fn($q) => $q->where('store_id', $selectedStoreId))->get();

        return view('admin.appointments.create', compact('customers', 'staffs', 'services', 'stores', 'branches', 'selectedStoreId', 'selectedBranchId', 'canViewAllStores', 'canViewAllBranches'));
    }

    public function store(Request $request)
    {
        try {
            $rules = [
                'store_id' => 'required|exists:stores,id',
                'branch_id' => 'required|exists:branches,id',
                'customer_id' => 'required|exists:customers,id',
                'appointment_date' => 'required|date',
                'total_amount' => 'nullable|numeric|min:0',
                'discount' => 'nullable|numeric|min:0',
                'final_amount' => 'nullable|numeric|min:0',
                'paid_amount' => 'nullable|numeric|min:0',
            ];

            if ($request->has('items') && is_array($request->items)) {
                $rules['items'] = 'required|array|min:1';
                $rules['items.*.service_id'] = 'required|exists:services,id';
                $rules['items.*.price'] = 'nullable|numeric|min:0';
            } else {
                $rules['service_id'] = 'required|exists:services,id';
                $rules['staff_id'] = 'nullable|exists:staffs,id';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }
                return redirect()->back()->withInput()->withErrors($validator->errors());
            }

            $items = $request->input('items', []);
            if (empty($items) && $request->filled('service_id')) {
                $items = [[
                    'service_id' => $request->service_id,
                    'staff_id' => $request->staff_id,
                    'staff_ids' => $request->has('staff_ids') ? (array)$request->staff_ids : [],
                    'price' => $request->total_amount,
                    'duration' => null,
                ]];
            }

            $totalDuration = 0;
            $calcTotal = 0;
            $processedItems = [];

            foreach ($items as $item) {
                $service = Service::find($item['service_id']);
                if (!$service) continue;
                $price = isset($item['price']) && $item['price'] !== '' ? (float)$item['price'] : (float)$service->price;
                $duration = isset($item['duration']) && $item['duration'] !== '' ? (int)$item['duration'] : (int)$service->duration;

                $staffIds = [];
                if (!empty($item['staff_ids']) && is_array($item['staff_ids'])) {
                    $staffIds = array_values(array_filter(array_map('intval', $item['staff_ids'])));
                } elseif (!empty($item['staff_id'])) {
                    $staffIds = [(int)$item['staff_id']];
                }
                $primaryStaffId = !empty($staffIds) ? $staffIds[0] : null;

                $totalDuration += $duration;
                $calcTotal += $price;

                $processedItems[] = [
                    'service_id' => $service->id,
                    'staff_id' => $primaryStaffId,
                    'staff_ids' => $staffIds,
                    'price' => $price,
                    'duration' => $duration,
                ];
            }

            $startTime = Carbon::parse($request->appointment_date);
            $endTime = $startTime->copy()->addMinutes($totalDuration > 0 ? $totalDuration : 30);
            
            $totalAmount = $request->filled('total_amount') ? (float)$request->total_amount : $calcTotal;
            $discount = $request->filled('discount') ? (float)$request->discount : 0;
            $finalAmount = $request->filled('final_amount') ? (float)$request->final_amount : max(0, $totalAmount - $discount);

            $perm = $this->getStoreBranchPermissions($request);
            $storeId = (!$perm['canViewAllStores'] && $perm['selectedStoreId']) ? $perm['selectedStoreId'] : $request->store_id;
            $branchId = (!$perm['canViewAllBranches'] && $perm['selectedBranchId']) ? $perm['selectedBranchId'] : $request->branch_id;
            if ($branchId && !$storeId) {
                $branch = Branch::find($branchId);
                if ($branch) {
                    $storeId = $branch->store_id;
                }
            }
            if (!$storeId) {
                $storeId = auth()->user()->store_id;
            }

            $primaryServiceId = $processedItems[0]['service_id'] ?? $request->service_id;
            $primaryStaffId = $processedItems[0]['staff_id'] ?? $request->staff_id;

            DB::beginTransaction();

            $paymentType = $request->input('payment_type');
            $cashAmount = 0.0;
            $upiAmount = 0.0;
            $cardAmount = 0.0;
            $upiReference = $request->input('upi_reference');

            if ($paymentType === 'split') {
                $cashAmount = (float)$request->input('cash_amount', 0);
                $upiAmount = (float)$request->input('upi_amount', 0);
                $cardAmount = (float)$request->input('card_amount', 0);
                $paidAmount = $cashAmount + $upiAmount + $cardAmount;
            } else {
                $paidAmount = $request->filled('paid_amount') ? (float)$request->paid_amount : 0.0;
                if ($paymentType === 'cash') {
                    $cashAmount = $paidAmount;
                } elseif ($paymentType === 'upi') {
                    $upiAmount = $paidAmount;
                } elseif ($paymentType === 'card') {
                    $cardAmount = $paidAmount;
                }
            }

            $paymentStatus = 'pending';
            if ($paidAmount >= $finalAmount && $finalAmount >= 0) {
                $paymentStatus = 'paid';
            } elseif ($paidAmount > 0) {
                $paymentStatus = 'partial';
            }

            $appointment = Appointment::create([
                'store_id' => $storeId,
                'branch_id' => $branchId,
                'customer_id' => $request->customer_id,
                'staff_id' => $primaryStaffId,
                'service_id' => $primaryServiceId,
                'appointment_date' => $startTime,
                'end_time' => $endTime,
                'status' => 'pending',
                'notes' => $request->notes,
                'total_amount' => $totalAmount,
                'discount' => $discount,
                'final_amount' => $finalAmount,
                'paid_amount' => $paidAmount,
                'payment_status' => $paymentStatus,
                'payment_type' => $paymentType,
                'cash_amount' => $cashAmount,
                'upi_amount' => $upiAmount,
                'card_amount' => $cardAmount,
                'upi_reference' => $upiReference,
            ]);

            foreach ($processedItems as $pItem) {
                $appointment->appointmentServices()->create([
                    'service_id' => $pItem['service_id'],
                    'staff_id' => $pItem['staff_id'],
                    'staff_ids' => $pItem['staff_ids'],
                    'price' => $pItem['price'],
                    'duration' => $pItem['duration'],
                ]);
            }

            DB::commit();

            logActivity('Created', 'Appointment', "Created appointment #{$appointment->id}");

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Appointment created successfully']);
            }
            return redirect()->route('admin.appointments.index')->with('success', 'Appointment created successfully');
        } catch (Exception $e) {
            DB::rollBack();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function edit($id, Request $request)
    {
        $appointment = Appointment::with(['customer', 'staff', 'service', 'store', 'branch', 'appointmentServices.service', 'appointmentServices.staff'])->findOrFail($id);

        $perm = $this->getStoreBranchPermissions($request);
        if (!$perm['canViewAllStores']) {
            if ($perm['selectedStoreId'] && $appointment->store_id && $appointment->store_id != $perm['selectedStoreId']) {
                abort(403, 'Unauthorized store access.');
            }
            if ($perm['selectedBranchId'] && $appointment->branch_id && $appointment->branch_id != $perm['selectedBranchId']) {
                abort(403, 'Unauthorized branch access.');
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($appointment);
        }

        $canViewAllStores = $perm['canViewAllStores'];
        $canViewAllBranches = $perm['canViewAllBranches'];
        $selectedStoreId = $perm['selectedStoreId'] ?: $appointment->store_id;
        $selectedBranchId = $perm['selectedBranchId'] ?: $appointment->branch_id;

        $customers = Customer::all();
        $staffs = Staff::where('status', true)->get();
        $services = Service::where('status', true)->get();
        $stores = Store::all();
        $branches = Branch::when($selectedStoreId, fn($q) => $q->where('store_id', $selectedStoreId))->get();

        return view('admin.appointments.edit', compact('appointment', 'customers', 'staffs', 'services', 'stores', 'branches', 'selectedStoreId', 'selectedBranchId', 'canViewAllStores', 'canViewAllBranches'));
    }

    public function update(Request $request, $id)
    {
        try {
            $appointment = Appointment::findOrFail($id);

            $rules = [
                'store_id' => 'required|exists:stores,id',
                'branch_id' => 'required|exists:branches,id',
                'customer_id' => 'required|exists:customers,id',
                'appointment_date' => 'required|date',
                'total_amount' => 'nullable|numeric|min:0',
                'discount' => 'nullable|numeric|min:0',
                'final_amount' => 'nullable|numeric|min:0',
                'paid_amount' => 'nullable|numeric|min:0',
            ];

            if ($request->has('items') && is_array($request->items)) {
                $rules['items'] = 'required|array|min:1';
                $rules['items.*.service_id'] = 'required|exists:services,id';
                $rules['items.*.price'] = 'nullable|numeric|min:0';
            } else {
                $rules['service_id'] = 'nullable|exists:services,id';
                $rules['staff_id'] = 'nullable|exists:staffs,id';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }
                return redirect()->back()->withInput()->withErrors($validator->errors());
            }

            $data = $request->except(['items']);

            $perm = $this->getStoreBranchPermissions($request);
            if (!$perm['canViewAllStores'] && $perm['selectedStoreId']) {
                $data['store_id'] = $perm['selectedStoreId'];
            }
            if (!$perm['canViewAllBranches'] && $perm['selectedBranchId']) {
                $data['branch_id'] = $perm['selectedBranchId'];
            }

            if ($request->filled('status')) {
                $data['status'] = $request->status;
                if ($request->status == 'checked_in' && !$appointment->check_in_time) {
                    $data['check_in_time'] = Carbon::now();
                }
                if ($request->status == 'completed') {
                    if (!$request->filled('paid_amount')) {
                        $data['paid_amount'] = (float)($appointment->paid_amount ?? 0);
                    }
                    if (!$appointment->completed_time) {
                        $data['completed_time'] = Carbon::now();
                    }
                }
            }

            if ($request->has('branch_id') && $perm['canViewAllBranches']) {
                $data['branch_id'] = $request->branch_id;
                if ($request->filled('branch_id') && !$request->filled('store_id')) {
                    $branch = Branch::find($request->branch_id);
                    if ($branch) {
                        $data['store_id'] = $branch->store_id;
                    }
                }
            }
            if ($request->has('store_id') && $perm['canViewAllStores']) {
                $data['store_id'] = $request->store_id;
            }

            DB::beginTransaction();

            if ($request->has('items') && is_array($request->items)) {
                $items = $request->items;
                $totalDuration = 0;
                $calcTotal = 0;
                $processedItems = [];

                foreach ($items as $item) {
                    $service = Service::find($item['service_id']);
                    if (!$service) continue;
                    $price = isset($item['price']) && $item['price'] !== '' ? (float)$item['price'] : (float)$service->price;
                    $duration = isset($item['duration']) && $item['duration'] !== '' ? (int)$item['duration'] : (int)$service->duration;

                    $staffIds = [];
                    if (!empty($item['staff_ids']) && is_array($item['staff_ids'])) {
                        $staffIds = array_values(array_filter(array_map('intval', $item['staff_ids'])));
                    } elseif (!empty($item['staff_id'])) {
                        $staffIds = [(int)$item['staff_id']];
                    }
                    $primaryStaffId = !empty($staffIds) ? $staffIds[0] : null;

                    $totalDuration += $duration;
                    $calcTotal += $price;

                    $processedItems[] = [
                        'service_id' => $service->id,
                        'staff_id' => $primaryStaffId,
                        'staff_ids' => $staffIds,
                        'price' => $price,
                        'duration' => $duration,
                    ];
                }

                if ($request->has('appointment_date')) {
                    $startTime = Carbon::parse($request->appointment_date);
                    $data['end_time'] = $startTime->copy()->addMinutes($totalDuration > 0 ? $totalDuration : 30);
                }

                if (!empty($processedItems)) {
                    $data['service_id'] = $processedItems[0]['service_id'];
                    $data['staff_id'] = $processedItems[0]['staff_id'];

                    $appointment->appointmentServices()->delete();
                    foreach ($processedItems as $pItem) {
                        $appointment->appointmentServices()->create([
                            'service_id' => $pItem['service_id'],
                            'staff_id' => $pItem['staff_id'],
                            'staff_ids' => $pItem['staff_ids'],
                            'price' => $pItem['price'],
                            'duration' => $pItem['duration'],
                        ]);
                    }
                }
            } elseif ($request->has('appointment_date')) {
                $serviceId = $request->service_id ?? $appointment->service_id;
                $service = Service::find($serviceId);
                if ($service) {
                    $startTime = Carbon::parse($request->appointment_date);
                    $data['end_time'] = $startTime->copy()->addMinutes($service->duration);
                }
            }

            if ($request->has('total_amount')) {
                $data['total_amount'] = (float)$request->total_amount;
            }
            if ($request->has('discount')) {
                $data['discount'] = (float)$request->discount;
            }
            if ($request->has('final_amount')) {
                $data['final_amount'] = (float)$request->final_amount;
            } elseif ($request->has('total_amount') || $request->has('discount')) {
                $tot = isset($data['total_amount']) ? $data['total_amount'] : $appointment->total_amount;
                $disc = isset($data['discount']) ? $data['discount'] : $appointment->discount;
                $data['final_amount'] = max(0, $tot - $disc);
            }
            if ($request->filled('paid_amount')) {
                $data['paid_amount'] = (float)$request->paid_amount;
            }
            
            $checkPaid = isset($data['paid_amount']) ? (float)$data['paid_amount'] : (float)$appointment->paid_amount;
            $checkFinal = isset($data['final_amount']) ? (float)$data['final_amount'] : (float)$appointment->final_amount;
            if (($data['status'] ?? $appointment->status) === 'cancelled') {
                $data['payment_status'] = 'cancelled';
            } elseif ($checkPaid >= $checkFinal) {
                $data['payment_status'] = 'paid';
            } elseif ($checkPaid > 0) {
                $data['payment_status'] = 'partial';
            } else {
                $data['payment_status'] = 'pending';
            }

            if ($request->has('payment_type')) {
                $data['payment_type'] = $request->payment_type;
                if ($request->payment_type === 'split') {
                    $data['cash_amount'] = (float)$request->input('cash_amount', 0);
                    $data['upi_amount'] = (float)$request->input('upi_amount', 0);
                    $data['card_amount'] = (float)$request->input('card_amount', 0);
                    $data['paid_amount'] = $data['cash_amount'] + $data['upi_amount'] + $data['card_amount'];
                } elseif ($request->payment_type === 'cash') {
                    $data['cash_amount'] = (float)($data['paid_amount'] ?? $appointment->paid_amount ?? 0);
                    $data['upi_amount'] = 0;
                    $data['card_amount'] = 0;
                } elseif ($request->payment_type === 'upi') {
                    $data['upi_amount'] = (float)($data['paid_amount'] ?? $appointment->paid_amount ?? 0);
                    $data['cash_amount'] = 0;
                    $data['card_amount'] = 0;
                } elseif ($request->payment_type === 'card') {
                    $data['card_amount'] = (float)($data['paid_amount'] ?? $appointment->paid_amount ?? 0);
                    $data['cash_amount'] = 0;
                    $data['upi_amount'] = 0;
                }
            } elseif ($request->has('cash_amount') || $request->has('upi_amount')) {
                $data['cash_amount'] = (float)$request->input('cash_amount', 0);
                $data['upi_amount'] = (float)$request->input('upi_amount', 0);
                $data['card_amount'] = (float)$request->input('card_amount', 0);
                if (($appointment->payment_type ?? '') === 'split') {
                    $data['paid_amount'] = $data['cash_amount'] + $data['upi_amount'] + $data['card_amount'];
                }
            }
            if ($request->has('upi_reference')) {
                $data['upi_reference'] = $request->upi_reference;
            }

            $appointment->update($data);
            DB::commit();

            logActivity('Updated', 'Appointment', "Updated appointment #{$appointment->id}");

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Appointment updated successfully']);
            }
            return redirect()->route('admin.appointments.index')->with('success', 'Appointment updated successfully');
        } catch (Exception $e) {
            DB::rollBack();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:pending,confirmed,checked_in,in_progress,completed,cancelled,no_show',
                'paid_amount' => 'nullable|numeric|min:0',
                'payment_type' => 'nullable|in:cash,upi,card,split',
                'cash_amount' => 'nullable|numeric|min:0',
                'upi_amount' => 'nullable|numeric|min:0',
                'card_amount' => 'nullable|numeric|min:0',
                'upi_reference' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }

            $appointment = Appointment::with('customer')->findOrFail($id);

            $perm = $this->getStoreBranchPermissions($request);
            if (!$perm['canViewAllStores']) {
                if ($perm['selectedStoreId'] && $appointment->store_id && $appointment->store_id != $perm['selectedStoreId']) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized store access.'], 403);
                }
                if ($perm['selectedBranchId'] && $appointment->branch_id && $appointment->branch_id != $perm['selectedBranchId']) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized branch access.'], 403);
                }
            }
            
            $updateData = ['status' => $request->status];
            
            if ($request->status == 'checked_in' && !$appointment->check_in_time) {
                $updateData['check_in_time'] = Carbon::now();
            }

            $whatsappUrl = null;

            if ($request->status == 'cancelled') {
                $updateData['payment_status'] = 'cancelled';
            } else {
                $finalAmt = (float)$appointment->final_amount;
                $paymentType = $request->input('payment_type');

                if ($paymentType === 'split') {
                    $cashAmount = (float)$request->input('cash_amount', 0);
                    $upiAmount = (float)$request->input('upi_amount', 0);
                    $cardAmount = (float)$request->input('card_amount', 0);
                    $paidAmt = $cashAmount + $upiAmount + $cardAmount;
                    $updateData['cash_amount'] = $cashAmount;
                    $updateData['upi_amount'] = $upiAmount;
                    $updateData['card_amount'] = $cardAmount;
                } else {
                    $paidAmt = $request->filled('paid_amount') ? (float)$request->paid_amount : (float)($appointment->paid_amount ?? 0);
                    if ($paymentType === 'cash') {
                        $updateData['cash_amount'] = $paidAmt;
                        $updateData['upi_amount'] = 0;
                        $updateData['card_amount'] = 0;
                    } elseif ($paymentType === 'upi') {
                        $updateData['upi_amount'] = $paidAmt;
                        $updateData['cash_amount'] = 0;
                        $updateData['card_amount'] = 0;
                    } elseif ($paymentType === 'card') {
                        $updateData['card_amount'] = $paidAmt;
                        $updateData['cash_amount'] = 0;
                        $updateData['upi_amount'] = 0;
                    }
                }

                if ($request->status == 'completed' || $request->filled('paid_amount') || $paymentType) {
                    $updateData['paid_amount'] = $paidAmt;
                }
                if ($paidAmt >= $finalAmt) {
                    $updateData['payment_status'] = 'paid';
                } elseif ($paidAmt > 0) {
                    $updateData['payment_status'] = 'partial';
                } else {
                    $updateData['payment_status'] = 'pending';
                }

                if ($request->status == 'completed') {
                    if ($request->filled('payment_type')) {
                        $updateData['payment_type'] = $request->payment_type;
                    }
                    if ($request->has('upi_reference')) {
                        $updateData['upi_reference'] = $request->upi_reference;
                    }
                    if (!$appointment->completed_time) {
                        $updateData['completed_time'] = Carbon::now();
                    }

                    $customer = $appointment->customer;
                    if ($customer && !$customer->has_received_link) {
                        $shareLink = 'https://share.google/ISMz8SagOj2HeQUp1';
                        $message = "Dear {$customer->name}, thank you for visiting! Here is your link: {$shareLink}";

                        WhatsappLog::create([
                            'customer_id' => $customer->id,
                            'mobile' => $customer->mobile,
                            'message' => $message,
                            'type' => 'booking_confirmation',
                            'status' => 'sent',
                        ]);

                        $customer->has_received_link = true;
                        $customer->save();

                        if ($customer->mobile) {
                            $cleanMobile = preg_replace('/[^0-9]/', '', $customer->mobile);
                            if (strlen($cleanMobile) == 10) {
                                $cleanMobile = '91' . $cleanMobile;
                            }
                            $whatsappUrl = "https://wa.me/{$cleanMobile}?text=" . urlencode($message);
                        }
                    }
                }
            }

            $appointment->update($updateData);

            logActivity('Updated Status', 'Appointment', "Updated appointment #{$appointment->id} status to {$request->status}");
            
            $response = ['success' => true, 'message' => 'Status updated successfully'];
            if ($whatsappUrl) {
                $response['whatsapp_url'] = $whatsappUrl;
            }

            return response()->json($response);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $appointment = Appointment::findOrFail($id);

            $perm = $this->getStoreBranchPermissions();
            if (!$perm['canViewAllStores']) {
                if ($perm['selectedStoreId'] && $appointment->store_id && $appointment->store_id != $perm['selectedStoreId']) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized store access.'], 403);
                }
                if ($perm['selectedBranchId'] && $appointment->branch_id && $appointment->branch_id != $perm['selectedBranchId']) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized branch access.'], 403);
                }
            }

            $appointment->delete();
            logActivity('Deleted', 'Appointment', "Deleted appointment #{$appointment->id}");
            return response()->json(['success' => true, 'message' => 'Appointment deleted successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getCustomerPackages(Request $request)
    {
        try {
            $customerId = $request->customer_id;
            $excludeAppointmentId = $request->exclude_appointment_id;
            if (!$customerId) {
                return response()->json(['success' => false, 'covered_services' => []]);
            }

            $packageData = \App\Models\Package::getActivePackagesForCustomer($customerId, $excludeAppointmentId);

            return response()->json([
                'success' => true,
                'has_active_package' => count($packageData['active_packages']) > 0,
                'covered_services' => $packageData['covered_services'],
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function getCustomerBalance(Request $request)
    {
        try {
            $customerId = $request->customer_id;
            if (!$customerId) {
                return response()->json([
                    'success' => false,
                    'balance' => [
                        'type' => 'zero',
                        'credit_balance' => 0,
                        'debit_balance' => 0,
                        'net_balance' => 0,
                        'text' => 'Balance: ₹0.00 (Clear)',
                        'badge_class' => 'bg-label-secondary'
                    ]
                ]);
            }

            $customer = Customer::find($customerId);
            if (!$customer) {
                return response()->json(['success' => false, 'message' => 'Customer not found'], 404);
            }

            return response()->json([
                'success' => true,
                'balance' => $customer->getBalanceDetails(),
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function ajaxSearchCustomers(Request $request)
    {
        $q = trim($request->get('q', ''));
        $customers = Customer::when($q, function ($query) use ($q) {
            $query->where(function($sub) use ($q) {
                $sub->where('name', 'LIKE', "%{$q}%")
                    ->orWhere('mobile', 'LIKE', "%{$q}%")
                    ->orWhere('email', 'LIKE', "%{$q}%");
            });
        })->limit(30)->get();

        return response()->json($customers->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'phone' => $c->mobile ?? '',
            ];
        }));
    }

    public function ajaxSearchServices(Request $request)
    {
        $q = trim($request->get('q', ''));
        $services = Service::where('status', true)
            ->when($q, function ($query) use ($q) {
                $query->where('service_name', 'LIKE', "%{$q}%");
            })->limit(30)->get();

        return response()->json($services->map(function ($s) {
            return [
                'id' => $s->id,
                'name' => $s->service_name,
                'price' => (float)$s->price,
                'duration' => (int)$s->duration,
            ];
        }));
    }

    public function ajaxSearchStaffs(Request $request)
    {
        $q = trim($request->get('q', ''));
        $perm = $this->getStoreBranchPermissions($request);
        $selectedStoreId = $perm['selectedStoreId'];
        $selectedBranchId = $perm['selectedBranchId'];

        $staffs = Staff::where(function ($s) {
                $s->where('status', true)->orWhere('status', 1);
            })
            ->when($q, function ($query) use ($q) {
                $query->where(function($sub) use ($q) {
                    $sub->where('first_name', 'LIKE', "%{$q}%")
                        ->orWhere('last_name', 'LIKE', "%{$q}%")
                        ->orWhere('phone', 'LIKE', "%{$q}%")
                        ->orWhere('position', 'LIKE', "%{$q}%")
                        ->orWhere(DB::raw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))"), 'LIKE', "%{$q}%");
                });
            })->limit(50)->get();

        return response()->json($staffs->map(function ($st) {
            $fullName = trim(($st->first_name ?? '') . ' ' . ($st->last_name ?? ''));
            return [
                'id' => $st->id,
                'name' => !empty($fullName) ? $fullName : ($st->name ?? 'Staff #' . $st->id),
            ];
        }));
    }
}




