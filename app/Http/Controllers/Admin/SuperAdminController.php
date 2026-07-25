<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Company;
use App\Models\Branch;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Bulty;
use App\Models\Trip;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            $kpi = $this->getSuperAdminKPIs();
        } elseif ($user->isCompanyAdmin()) {
            $kpi = $this->getCompanyAdminKPIs($user->company_id);
        } else {
            $kpi = $this->getBranchManagerKPIs($user->branch_id);
        }

        $query = ActivityLog::with(['user', 'company', 'branch'])->orderBy('created_at', 'desc')->limit(10);

        if (!$user->isSuperAdmin()) {
            $query->where('company_id', $user->company_id);
        }

        $recentActivities = $query->get();

        $todayAttendance = Attendance::where('user_id', $user->id)
            ->where('date', today())
            ->first();

        return view('admin.dashboard.index', compact('kpi', 'recentActivities', 'todayAttendance'));
    }

    private function getSuperAdminKPIs()
    {
        $totalCompanies = Company::count();
        $activeCompanies = Company::where('status', 'active')->count();
        $totalBranches = Branch::count();
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'active')->count();
        $totalRoles = DB::table('roles')->count();
        $totalPermissions = DB::table('permissions')->count();

        $recentLogins = User::whereNotNull('last_login_at')
            ->orderBy('last_login_at', 'desc')
            ->limit(5)
            ->get();

        $companyStats = Company::select('companies.name', DB::raw('COUNT(branches.id) as branch_count'))
            ->leftJoin('branches', 'companies.id', '=', 'branches.company_id')
            ->groupBy('companies.id', 'companies.name')
            ->orderBy('branch_count', 'desc')
            ->limit(10)
            ->get();

        // Transport stats (no company scope for super admin)
        $totalVehicles = Vehicle::withoutGlobalScopes()->count();
        $totalDrivers  = Driver::withoutGlobalScopes()->count();
        $totalBulties  = Bulty::withoutGlobalScopes()->count();
        $totalTrips    = Trip::count();

        // Monthly LR trend – last 6 months
        $monthlyLRs = $this->getMonthlyLRTrend();

        // Monthly P&L trend – last 12 months (for chart)
        $monthlyPnL = $this->getMonthlyPnLTrend();

        // Expiring documents
        $expiringDocuments = collect();
        $threshold = now()->addDays(30);
        $documentFields = [
            'insurance_expiry' => 'Insurance',
            'fitness_expiry' => 'Fitness Certificate',
            'permit_expiry' => 'Permit',
            'pollution_expiry' => 'Pollution Certificate',
        ];
        $vehicles = Vehicle::withoutGlobalScopes()->where('status', 'active')->get();
        foreach ($vehicles as $vehicle) {
            foreach ($documentFields as $field => $label) {
                $expiryDate = $vehicle->$field;
                if ($expiryDate && $expiryDate <= $threshold) {
                    $expiringDocuments->push([
                        'vehicle_number' => $vehicle->vehicle_number,
                        'vehicle_id' => $vehicle->id,
                        'company_name' => $vehicle->company?->name ?? 'N/A',
                        'document' => $label,
                        'expiry_date' => $expiryDate,
                        'days_left' => now()->diffInDays($expiryDate, false),
                    ]);
                }
            }
        }
        $expiringDocuments = $expiringDocuments->sortBy('days_left')->values();

        return [
            'total_companies'    => $totalCompanies,
            'active_companies'   => $activeCompanies,
            'total_branches'     => $totalBranches,
            'total_users'        => $totalUsers,
            'active_users'       => $activeUsers,
            'total_roles'        => $totalRoles,
            'total_permissions'  => $totalPermissions,
            'recent_logins'      => $recentLogins,
            'company_stats'      => $companyStats,
            // transport
            'total_vehicles'     => $totalVehicles,
            'total_drivers'      => $totalDrivers,
            'total_bulties'      => $totalBulties,
            'total_trips'        => $totalTrips,
            'monthly_lrs'        => $monthlyLRs,
            'monthly_pnl'        => $monthlyPnL,
            'expiring_documents' => $expiringDocuments,
        ];
    }

    /** Last 6 months LR count (all companies, no scope). */
    private function getMonthlyLRTrend(): array
    {
        $months = [];
        $counts = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M Y');
            $counts[] = Bulty::withoutGlobalScopes()
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
        }
        return ['months' => $months, 'counts' => $counts];
    }

    /** Monthly P&L trend – last 12 months (for dashboard chart). */
    private function getMonthlyPnLTrend(): array
    {
        $months = [];
        $incomeData = [];
        $expenseData = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $year = $date->year;
            $month = $date->month;
            $months[] = $date->format('M Y');

            // Total income from bulties (delivered/released only)
            $mBultyIds = Bulty::withoutGlobalScopes()
                ->whereNotIn('status', ['pending', 'planned'])
                ->whereYear('lr_date', $year)
                ->whereMonth('lr_date', $month)
                ->pluck('id');

            $income = Bulty::withoutGlobalScopes()
                ->whereIn('id', $mBultyIds)
                ->sum('total_amount');

            $tripExpenses = Trip::whereIn('builty_id', $mBultyIds)
                ->selectRaw('COALESCE(SUM(fuel_amount),0) + COALESCE(SUM(fasttag_total_amount),0) + COALESCE(SUM(adblue_total_amount),0) + COALESCE(SUM(other_amount),0) + COALESCE(SUM(advance_total_amount),0) as total')
                ->value('total') ?? 0;

            // Commission on bulties
            $commission = Bulty::withoutGlobalScopes()
                ->whereIn('id', $mBultyIds)
                ->sum('bilty_commission');

            $incomeData[] = round((float)$income, 0);
            $expenseData[] = round((float)$tripExpenses + (float)$commission, 0);
        }
        return ['months' => $months, 'income' => $incomeData, 'expense' => $expenseData];
    }

    private function getCompanyAdminKPIs($companyId)
    {
        $company = Company::find($companyId);
        $totalBranches = Branch::where('company_id', $companyId)->count();
        $totalUsers    = User::where('company_id', $companyId)->count();
        $activeUsers   = User::where('company_id', $companyId)->where('status', 'active')->count();

        $totalVehicles = Vehicle::withoutGlobalScopes()->count();
        $totalDrivers  = Driver::withoutGlobalScopes()->count();
        $totalBulties  = Bulty::withoutGlobalScopes()->where('company_id', $companyId)->count();
        $totalTrips    = Bulty::withoutGlobalScopes()->where('company_id', $companyId)
            ->has('trip')->count();

        $monthlyLRs = $this->getCompanyMonthlyLRTrend($companyId);

        return [
            'company'        => $company,
            'total_branches' => $totalBranches,
            'total_users'    => $totalUsers,
            'active_users'   => $activeUsers,
            'total_vehicles' => $totalVehicles,
            'total_drivers'  => $totalDrivers,
            'total_bulties'  => $totalBulties,
            'total_trips'    => $totalTrips,
            'monthly_lrs'    => $monthlyLRs,
        ];
    }

    private function getCompanyMonthlyLRTrend($companyId): array
    {
        $months = [];
        $counts = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M Y');
            $counts[] = Bulty::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
        }
        return ['months' => $months, 'counts' => $counts];
    }

    private function getBranchManagerKPIs($branchId)
    {
        $branch      = Branch::find($branchId);
        $companyId   = $branch->company_id ?? null;
        $totalUsers  = User::where('branch_id', $branchId)->count();
        $activeUsers = User::where('branch_id', $branchId)->where('status', 'active')->count();

        $totalVehicles = Vehicle::withoutGlobalScopes()->count();
        $totalDrivers  = Driver::withoutGlobalScopes()->count();
        $totalBulties  = Bulty::withoutGlobalScopes()->where('branch_id', $branchId)->count();
        $totalTrips    = Bulty::withoutGlobalScopes()->where('branch_id', $branchId)
            ->has('trip')->count();

        $monthlyLRs = $this->getBranchMonthlyLRTrend($branchId);

        return [
            'branch'         => $branch,
            'total_users'    => $totalUsers,
            'active_users'   => $activeUsers,
            'total_vehicles' => $totalVehicles,
            'total_drivers'  => $totalDrivers,
            'total_bulties'  => $totalBulties,
            'total_trips'    => $totalTrips,
            'monthly_lrs'    => $monthlyLRs,
        ];
    }

    private function getBranchMonthlyLRTrend($branchId): array
    {
        $months = [];
        $counts = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M Y');
            $counts[] = Bulty::withoutGlobalScopes()
                ->where('branch_id', $branchId)
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
        }
        return ['months' => $months, 'counts' => $counts];
    }

    public function activityLogs(Request $request)
    {
        $query = ActivityLog::with(['user', 'company', 'branch']);

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if (!auth()->user()->isSuperAdmin()) {
            $query->where('company_id', auth()->user()->company_id);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(20);
        $actions = cache()->remember('activity_log_actions', 3600, fn() => ActivityLog::distinct()->pluck('action'));
        $users = User::active()->get();

        return view('admin.activity_logs.index', compact('logs', 'actions', 'users'));
    }

    public function systemSettings()
    {
        $settings = Setting::pluck('value', 'key')->toArray();

        return view('admin.settings.index', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'app_timezone' => 'required|string',
            'app_email' => 'nullable|email',
            'app_phone' => 'nullable|string|max:50',
            'app_address' => 'nullable|string',
            'app_logo' => 'nullable|image|max:2048',
        ]);

        $keys = ['app_name', 'app_timezone', 'app_email', 'app_phone', 'app_address'];

        foreach ($keys as $key) {
            Setting::updateOrCreate(['key' => $key], ['value' => $request->input($key) ?? '']);
        }

        if ($request->hasFile('app_logo')) {
            $path = $request->file('app_logo')->store('settings', 'uploads');
            Setting::updateOrCreate(['key' => 'app_logo'], ['value' => $path]);
        }

        ActivityLog::log('settings_update', 'System settings updated');

        return back()->with('success', 'Settings updated successfully.');
    }

    public function switchCompany(Request $request)
    {
        $companyId = $request->company_id;

        if ($companyId === 'all') {
            if (!auth()->user()->isSuperAdmin()) {
                return back()->with('error', 'Unauthorized: only super admin can view all companies.');
            }
            session(['current_company_id' => 'all', 'current_branch_id' => null]);
        } elseif ($companyId) {
            $user = auth()->user();
            if (!$user->isSuperAdmin() && !$user->canAccessCompany($companyId)) {
                return back()->with('error', 'Unauthorized company access.');
            }
            session(['current_company_id' => $companyId, 'current_branch_id' => null]);
        }

        return redirect()->back();
    }

    public function switchYear(Request $request)
    {
        $year = $request->year;
        if ($year === 'all' || ($year >= 2025 && $year <= date('Y') + 5)) {
            session(['current_year' => $year]);
        }
        return redirect()->back();
    }
}
