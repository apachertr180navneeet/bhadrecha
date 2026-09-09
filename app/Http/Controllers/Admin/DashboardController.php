<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Staff;
use App\Models\Expense;
use App\Models\Branch;
use App\Models\Store;
use Carbon\Carbon;
use DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $userStoreId = $user->store_id;
        $userBranchId = $user->branch_id;

        $canViewAllStores = $user->hasRole('Admin')
            || $user->hasRole('Super Admin')
            || in_array(strtolower($user->role ?? ''), ['admin', 'super_admin'])
            || $user->can('view stores');

        $canViewAllBranches = $user->hasRole('Admin')
            || $user->hasRole('Super Admin')
            || in_array(strtolower($user->role ?? ''), ['admin', 'super_admin'])
            || $user->can('view branches');

        $selectedStoreId = ($userStoreId && !$canViewAllStores) ? $userStoreId : $request->get('store_id');
        $selectedBranchId = ($userBranchId && !$canViewAllBranches) ? $userBranchId : $request->get('branch_id');

        $query = Appointment::query();
        if ($selectedStoreId) {
            $query->where(function ($sub) use ($selectedStoreId) {
                $sub->where('store_id', $selectedStoreId)
                    ->orWhereHas('branch', fn($bq) => $bq->where('store_id', $selectedStoreId))
                    ->orWhereHas('staff', fn($sq) => $sq->where('store_id', $selectedStoreId))
                    ->orWhereHas('appointmentServices.staff', fn($asq) => $asq->where('store_id', $selectedStoreId));
            });
        }
        if ($selectedBranchId) {
            $query->where(function ($sub) use ($selectedBranchId) {
                $sub->where('branch_id', $selectedBranchId)
                    ->orWhereHas('staff', fn($sq) => $sq->where('branch_id', $selectedBranchId))
                    ->orWhereHas('appointmentServices.staff', fn($asq) => $asq->where('branch_id', $selectedBranchId));
            });
        }

        $today = Carbon::today();
        $todayRevenue = (clone $query)->whereDate('appointment_date', $today)->where('status', 'completed')->sum('final_amount');
        $monthlyRevenue = (clone $query)->whereMonth('appointment_date', $today->month)->whereYear('appointment_date', $today->year)->where('status', 'completed')->sum('final_amount');
        $totalCustomers = Customer::count();
        $newCustomers = Customer::whereDate('created_at', $today)->count();
        $todayAppointments = (clone $query)->whereDate('appointment_date', $today)->count();
        $completedAppointments = (clone $query)->whereDate('appointment_date', $today)->where('status', 'completed')->count();
        $pendingAppointments = (clone $query)->whereDate('appointment_date', $today)->where('status', 'pending')->count();
        $staffCount = Staff::when($selectedStoreId, fn($q) => $q->where('store_id', $selectedStoreId))
            ->when($selectedBranchId, fn($q) => $q->where('branch_id', $selectedBranchId))
            ->where('status', true)->count();

        $totalExpense = Expense::when($selectedStoreId, fn($q) => $q->where('store_id', $selectedStoreId))
            ->when($selectedBranchId, fn($q) => $q->where('branch_id', $selectedBranchId))
            ->whereMonth('date', $today->month)->whereYear('date', $today->year)->sum('amount');

        $dailyRevenue = Appointment::select(
            DB::raw('DATE(appointment_date) as date'),
            DB::raw('SUM(final_amount) as total')
        )->when($selectedStoreId, fn($q) => $q->where('store_id', $selectedStoreId))
            ->when($selectedBranchId, fn($q) => $q->where('branch_id', $selectedBranchId))
            ->whereMonth('appointment_date', $today->month)
            ->whereYear('appointment_date', $today->year)
            ->where('status', 'completed')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $monthlyRevenueData = Appointment::select(
            DB::raw("DATE_FORMAT(appointment_date, '%Y-%m') as month"),
            DB::raw('SUM(final_amount) as total')
        )->when($selectedStoreId, fn($q) => $q->where('store_id', $selectedStoreId))
            ->when($selectedBranchId, fn($q) => $q->where('branch_id', $selectedBranchId))
            ->whereYear('appointment_date', $today->year)
            ->where('status', 'completed')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $appointmentTrend = Appointment::select(
            DB::raw('DATE(appointment_date) as date'),
            DB::raw('COUNT(*) as total')
        )->when($selectedStoreId, fn($q) => $q->where('store_id', $selectedStoreId))
            ->when($selectedBranchId, fn($q) => $q->where('branch_id', $selectedBranchId))
            ->whereMonth('appointment_date', $today->month)
            ->whereYear('appointment_date', $today->year)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $customerGrowth = Customer::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as total')
        )->whereMonth('created_at', $today->month)
            ->whereYear('created_at', $today->year)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $dailyRevenueDates = $dailyRevenue->pluck('date');
        $dailyRevenueTotals = $dailyRevenue->pluck('total');
        $monthlyRevenueLabels = $monthlyRevenueData->pluck('month');
        $monthlyRevenueTotals = $monthlyRevenueData->pluck('total');
        $appointmentTrendDates = $appointmentTrend->pluck('date');
        $appointmentTrendTotals = $appointmentTrend->pluck('total');
        $customerGrowthDates = $customerGrowth->pluck('date');
        $customerGrowthTotals = $customerGrowth->pluck('total');

        $stores = Store::all();
        $branches = Branch::all();

        $recentAppointments = Appointment::with(['customer', 'staff', 'service', 'branch'])
            ->when($selectedStoreId, fn($q) => $q->where('store_id', $selectedStoreId))
            ->when($selectedBranchId, fn($q) => $q->where('branch_id', $selectedBranchId))
            ->latest()->take(10)->get();

        return view('admin.dashboard.index', compact(
            'todayRevenue', 'monthlyRevenue', 'totalCustomers', 'newCustomers',
            'todayAppointments', 'completedAppointments', 'pendingAppointments',
            'staffCount', 'totalExpense', 'dailyRevenue', 'monthlyRevenueData',
            'appointmentTrend', 'customerGrowth', 'stores', 'branches', 'recentAppointments',
            'dailyRevenueDates', 'dailyRevenueTotals',
            'monthlyRevenueLabels', 'monthlyRevenueTotals',
            'appointmentTrendDates', 'appointmentTrendTotals',
            'customerGrowthDates', 'customerGrowthTotals',
            'selectedStoreId', 'selectedBranchId', 'canViewAllStores', 'canViewAllBranches'
        ));
    }
}
