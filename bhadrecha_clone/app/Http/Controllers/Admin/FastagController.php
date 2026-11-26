<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FastagSyncLog;
use App\Models\FastagTransaction;
use App\Models\Vehicle;
use App\Services\BlackBuckFastagService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FastagController extends Controller
{
    protected BlackBuckFastagService $fastagService;

    public function __construct(BlackBuckFastagService $fastagService)
    {
        $this->fastagService = $fastagService;
    }

    /**
     * Screen A: FASTag Transactions listing with filters & KPI summary.
     */
    public function index(Request $request)
    {
        $query = FastagTransaction::with('vehicle')->orderBy('transaction_date', 'desc');

        if ($request->filled('vehicle_number')) {
            $query->forVehicle($request->vehicle_number);
        }

        if ($request->filled('from_date') || $request->filled('to_date')) {
            $query->dateBetween($request->from_date, $request->to_date);
        }

        if ($request->filled('status')) {
            $query->status($request->status);
        }

        if ($request->filled('plaza')) {
            $query->plaza($request->plaza);
        }

        if ($request->filled('type')) {
            $query->type($request->type);
        }

        if ($request->wantsJson() && !$request->has('page')) {
            $transactions = $query->limit(100)->get();
            return response()->json([
                'success' => true,
                'data' => $transactions,
            ]);
        }

        $transactions = $query->paginate(25)->withQueryString();

        // Calculate KPI summary figures
        $totalTransactions = FastagTransaction::count();
        $totalTollExpenses = (float) FastagTransaction::where('transaction_type', 'DEBIT')->sum('amount');
        $todayTollExpenses = (float) FastagTransaction::where('transaction_type', 'DEBIT')
            ->whereDate('transaction_date', Carbon::today())
            ->sum('amount');
        $registeredVehiclesCount = Vehicle::where('status', 'active')->count();
        $fastagVehiclesCount = FastagTransaction::distinct('vehicle_number')->count('vehicle_number');

        $lastSync = FastagSyncLog::successful()->orderBy('completed_at', 'desc')->first();

        // Plazas for filter dropdown
        $plazas = FastagTransaction::select('toll_plaza_name')
            ->whereNotNull('toll_plaza_name')
            ->distinct()
            ->orderBy('toll_plaza_name')
            ->limit(100)
            ->pluck('toll_plaza_name');

        // Available vehicles for modal dropdown
        $vehicles = Vehicle::orderBy('vehicle_number')->get(['id', 'vehicle_number', 'vehicle_type', 'make_model']);

        return view('admin.fastag.index', compact(
            'transactions',
            'totalTransactions',
            'totalTollExpenses',
            'todayTollExpenses',
            'registeredVehiclesCount',
            'fastagVehiclesCount',
            'lastSync',
            'plazas',
            'vehicles'
        ));
    }

    /**
     * Screen B: Dedicated Fetch Transactions screen with truck multi-select & datetime pickers.
     */
    public function fetch(Request $request)
    {
        $vehicles = Vehicle::orderBy('vehicle_number')->get(['id', 'vehicle_number', 'vehicle_type', 'make_model', 'owner_name']);
        $recentSyncs = FastagSyncLog::orderBy('id', 'desc')->limit(10)->get();
        $validation = $this->fastagService->validateConfiguration();

        return view('admin.fastag.fetch', compact('vehicles', 'recentSyncs', 'validation'));
    }

    /**
     * Vehicle-wise transaction statistics and fast sync.
     */
    public function vehicleWise(Request $request)
    {
        $vehicleQuery = Vehicle::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $clean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $search));
            $vehicleQuery->where(function ($q) use ($search, $clean) {
                $q->where('vehicle_number', 'like', "%{$search}%")
                  ->orWhere('owner_name', 'like', "%{$search}%")
                  ->orWhereRaw("UPPER(REPLACE(REPLACE(vehicle_number, ' ', ''), '-', '')) LIKE ?", ["%{$clean}%"]);
            });
        }

        $vehicles = $vehicleQuery->orderBy('vehicle_number')->paginate(20)->withQueryString();

        $vehicleNumbers = $vehicles->pluck('vehicle_number')->toArray();
        $stats = FastagTransaction::select(
            'vehicle_number',
            DB::raw('COUNT(*) as total_count'),
            DB::raw('SUM(CASE WHEN transaction_type = "DEBIT" THEN amount ELSE 0 END) as total_debit'),
            DB::raw('MAX(transaction_date) as last_txn_date')
        )
            ->whereIn('vehicle_number', $vehicleNumbers)
            ->groupBy('vehicle_number')
            ->get()
            ->keyBy('vehicle_number');

        $recentSyncs = FastagSyncLog::whereIn('vehicle_number', $vehicleNumbers)
            ->orderBy('id', 'desc')
            ->get()
            ->unique('vehicle_number')
            ->keyBy('vehicle_number');

        return view('admin.fastag.vehicle_wise', compact('vehicles', 'stats', 'recentSyncs'));
    }

    /**
     * Screen C: Reports (Daily, monthly, vehicle-wise, total deduction, count).
     */
    public function reports(Request $request)
    {
        $fromDate = $request->input('from_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $toDate = $request->input('to_date', Carbon::now()->format('Y-m-d'));

        // Daily toll expenses
        $dailyExpenses = FastagTransaction::select(
            DB::raw('DATE(transaction_date) as txn_date'),
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(amount) as total_amount')
        )
            ->where('transaction_type', 'DEBIT')
            ->whereBetween('transaction_date', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
            ->groupBy('txn_date')
            ->orderBy('txn_date', 'asc')
            ->get();

        // Monthly toll expenses (last 12 months)
        $monthlyExpenses = FastagTransaction::select(
            DB::raw('DATE_FORMAT(transaction_date, "%Y-%m") as txn_month'),
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(amount) as total_amount')
        )
            ->where('transaction_type', 'DEBIT')
            ->where('transaction_date', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->groupBy('txn_month')
            ->orderBy('txn_month', 'asc')
            ->get();

        // Vehicle-wise toll expenses
        $vehicleExpenses = FastagTransaction::select(
            'vehicle_number',
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(amount) as total_amount')
        )
            ->where('transaction_type', 'DEBIT')
            ->whereBetween('transaction_date', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
            ->groupBy('vehicle_number')
            ->orderBy('total_amount', 'desc')
            ->get();

        // Top toll plazas
        $topPlazas = FastagTransaction::select(
            'toll_plaza_name',
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(amount) as total_amount')
        )
            ->where('transaction_type', 'DEBIT')
            ->whereBetween('transaction_date', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
            ->groupBy('toll_plaza_name')
            ->orderBy('total_amount', 'desc')
            ->limit(10)
            ->get();

        // Totals
        $totalDeduction = (float) FastagTransaction::where('transaction_type', 'DEBIT')
            ->whereBetween('transaction_date', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
            ->sum('amount');

        $totalCount = FastagTransaction::where('transaction_type', 'DEBIT')
            ->whereBetween('transaction_date', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
            ->count();

        $totalRefund = (float) FastagTransaction::where('transaction_type', 'REFUND')
            ->whereBetween('transaction_date', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
            ->sum('amount');

        $failedCount = FastagTransaction::where('transaction_status', 'FAILED')
            ->whereBetween('transaction_date', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
            ->count();

        $disputedCount = FastagTransaction::where('transaction_status', 'DISPUTED')
            ->whereBetween('transaction_date', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
            ->count();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'daily' => $dailyExpenses,
                'monthly' => $monthlyExpenses,
                'vehicle_wise' => $vehicleExpenses,
                'top_plazas' => $topPlazas,
                'total_deduction' => $totalDeduction,
                'total_count' => $totalCount,
                'total_refund' => $totalRefund,
                'failed_count' => $failedCount,
                'disputed_count' => $disputedCount,
            ]);
        }

        return view('admin.fastag.reports', compact(
            'fromDate',
            'toDate',
            'dailyExpenses',
            'monthlyExpenses',
            'vehicleExpenses',
            'topPlazas',
            'totalDeduction',
            'totalCount',
            'totalRefund',
            'failedCount',
            'disputedCount'
        ));
    }

    /**
     * Screen D: Synchronization history audit trail.
     */
    public function syncLogs(Request $request)
    {
        $query = FastagSyncLog::orderBy('id', 'desc');

        if ($request->filled('vehicle_number')) {
            $query->where('vehicle_number', 'like', '%' . $request->vehicle_number . '%');
        }

        if ($request->filled('status')) {
            $query->where('sync_status', strtoupper($request->status));
        }

        $logs = $query->paginate(20)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $logs,
            ]);
        }

        return view('admin.fastag.sync_logs', compact('logs'));
    }

    /**
     * Screen E: BlackBuck API configuration & status screen.
     */
    public function config(Request $request)
    {
        $validation = $this->fastagService->validateConfiguration();
        $lastSync = FastagSyncLog::latest('id')->first();

        return view('admin.fastag.config', compact('validation', 'lastSync'));
    }

    /**
     * Test connection to BlackBuck API endpoint.
     */
    public function testConnection(): JsonResponse
    {
        $validation = $this->fastagService->validateConfiguration();

        if (!$validation['is_valid']) {
            return response()->json([
                'success' => false,
                'message' => 'API Configuration incomplete: ' . implode(', ', $validation['missing']),
                'validation' => $validation,
            ], 422);
        }

        $result = $this->fastagService->fetchTransactions(
            truckNumbers: ['RJ19GJ3506'],
            fromTime: now()->subDays(1),
            toTime: now()
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Successfully connected to BlackBuck FASTag API!',
                'data' => $result,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['error'] ?? 'Connection test failed',
            'details' => $result,
        ], 400);
    }

    /**
     * Show single transaction detail.
     */
    public function show($id)
    {
        $transaction = FastagTransaction::with('vehicle')->findOrFail($id);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $transaction,
            ]);
        }

        return view('admin.fastag.show', compact('transaction'));
    }

    /**
     * API KPI summary for dashboard widgets.
     */
    public function summary(): JsonResponse
    {
        $totalTransactions = FastagTransaction::count();
        $totalTollExpenses = (float) FastagTransaction::where('transaction_type', 'DEBIT')->sum('amount');
        $todayTollExpenses = (float) FastagTransaction::where('transaction_type', 'DEBIT')
            ->whereDate('transaction_date', Carbon::today())
            ->sum('amount');
        $totalRegisteredVehicles = Vehicle::where('status', 'active')->count();
        $lastSync = FastagSyncLog::successful()->latest('completed_at')->first();

        return response()->json([
            'success' => true,
            'summary' => [
                'total_transactions' => $totalTransactions,
                'total_toll_expenses' => $totalTollExpenses,
                'today_toll_expenses' => $todayTollExpenses,
                'total_registered_vehicles' => $totalRegisteredVehicles,
                'last_successful_sync' => $lastSync ? $lastSync->completed_at->toDateTimeString() : null,
            ],
        ]);
    }

    /**
     * Synchronize a specific vehicle or multi-selected vehicles.
     */
    public function sync(Request $request)
    {
        // Support both single vehicle_number and multiple truck_numbers
        $trucks = [];
        if ($request->has('truck_numbers') && is_array($request->truck_numbers)) {
            $trucks = array_filter($request->truck_numbers);
        } elseif ($request->filled('vehicle_number')) {
            $trucks = [$request->vehicle_number];
        }

        $validator = Validator::make([
            'truck_numbers' => $trucks,
            'from_time' => $request->from_time ?? $request->from_date,
            'to_time' => $request->to_time ?? $request->to_date,
        ], [
            'truck_numbers' => 'required|array|min:1',
            'truck_numbers.*' => 'string|max:50',
            'from_time' => 'nullable|date',
            'to_time' => 'nullable|date|after_or_equal:from_time',
        ], [
            'truck_numbers.required' => 'Please select at least one vehicle / truck number.',
            'to_time.after_or_equal' => 'The to date/time must be after or equal to the from date/time.',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                // Format errors properly for JSON
                $errors = $validator->errors();
                if ($errors->has('truck_numbers') && $request->has('vehicle_number')) {
                    $errors->add('vehicle_number', 'The vehicle number field is required.');
                }
                if ($errors->has('to_time') && $request->has('to_date')) {
                    $errors->add('to_date', $errors->first('to_time'));
                }
                return response()->json([
                    'success' => false,
                    'errors' => $errors,
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $fromTime = $request->from_time ?? $request->from_date;
        $toTime = $request->to_time ?? $request->to_date;

        $toParsed = $toTime ? Carbon::parse($toTime) : now()->endOfDay();
        $thirtyDaysAgo = Carbon::now()->subDays(30)->startOfDay();

        if ($toParsed->lt($thirtyDaysAgo)) {
            $msg = 'BlackBuck API only retains transaction data for the last 30 days (from ' . $thirtyDaysAgo->format('d M Y') . ' onwards). Please select dates within the last 30 days.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg, 'error' => $msg], 422);
            }
            return redirect()->back()->with('error', $msg);
        }

        $result = $this->fastagService->syncVehicles($trucks, $fromTime, $toTime);

        if ($request->wantsJson()) {
            return response()->json($result, $result['success'] ? 200 : 400);
        }

        if ($result['success']) {
            $truckCount = count($trucks);
            $msg = $truckCount === 1
                ? "FASTag synchronization successful for {$trucks[0]}! Received {$result['records_received']} records, saved {$result['records_saved']}."
                : "FASTag synchronization successful for {$truckCount} trucks! Received {$result['records_received']} records, saved {$result['records_saved']}.";

            return redirect()->back()->with('success', $msg);
        }

        return redirect()->back()->with(
            'error',
            "FASTag synchronization failed: " . ($result['error'] ?? 'Unknown error')
        );
    }

    /**
     * Synchronize transactions for all active vehicles.
     */
    public function syncAll(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_time' => 'nullable|date',
            'to_time' => 'nullable|date|after_or_equal:from_time',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $fromTime = $request->from_time ?? $request->from_date;
        $toTime = $request->to_time ?? $request->to_date;

        $toParsed = $toTime ? Carbon::parse($toTime) : now()->endOfDay();
        $thirtyDaysAgo = Carbon::now()->subDays(30)->startOfDay();

        if ($toParsed->lt($thirtyDaysAgo)) {
            $msg = 'BlackBuck API only retains transaction data for the last 30 days (from ' . $thirtyDaysAgo->format('d M Y') . ' onwards). Please select dates within the last 30 days.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg, 'error' => $msg], 422);
            }
            return redirect()->back()->with('error', $msg);
        }

        $result = $this->fastagService->syncAllVehicles($fromTime, $toTime);

        if ($request->wantsJson()) {
            return response()->json($result, $result['success'] ? 200 : 400);
        }

        if ($result['success']) {
            return redirect()->back()->with(
                'success',
                "All vehicles synchronized successfully! Processed {$result['vehicles_processed']} vehicles, received {$result['total_records_received']} records."
            );
        }

        return redirect()->back()->with(
            'warning',
            "Synchronization completed with status: {$result['status']}. Total records saved: {$result['total_records_saved']}."
        );
    }
}
