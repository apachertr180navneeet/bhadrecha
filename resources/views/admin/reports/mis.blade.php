@extends('admin.layouts.app')

@section('content')
<style>
    .mis-card {
        transition: all 0.25s ease-in-out;
        border: 1px solid rgba(0, 0, 0, 0.07);
        border-radius: 0.625rem;
    }
    .mis-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.25rem 1rem rgba(0, 0, 0, 0.08) !important;
    }
    .kpi-icon-avatar {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .kpi-sm-avatar {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    .border-left-primary { border-left: 4px solid #696cff !important; }
    .border-left-success { border-left: 4px solid #71dd37 !important; }
    .border-left-warning { border-left: 4px solid #ffab00 !important; }
    .border-left-danger { border-left: 4px solid #ff3e1d !important; }
</style>

<div class="container-fluid flex-grow-1 container-p-y">
    {{-- Header Banner --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="fw-bold mb-0">MIS Report</h4>
                <span class="badge bg-label-primary rounded-pill">Analytics</span>
            </div>
            <nav aria-label="breadcrumb" class="mt-1">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt me-1"></i>Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Reports</a></li>
                    <li class="breadcrumb-item active">MIS Report</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.reports.vehicle-utilization') }}" class="btn btn-outline-primary shadow-sm">
                <i class="bx bx-car me-1"></i> Vehicle Utilization
            </a>
            <a href="{{ route('admin.reports.mis.export', 'excel') . '?' . http_build_query(request()->except('page')) }}" class="btn btn-success shadow-sm">
                <i class="bx bx-spreadsheet me-1"></i> Export Excel
            </a>
            <a href="{{ route('admin.reports.mis.export', 'pdf') . '?' . http_build_query(request()->except('page')) }}" class="btn btn-danger shadow-sm">
                <i class="bx bxs-file-pdf me-1"></i> Export PDF
            </a>
        </div>
    </div>

    {{-- Filter Form --}}
    <div class="card mis-card mb-4 shadow-sm">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.reports.mis') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4 col-lg-3">
                        <label class="form-label fw-semibold text-muted small"><i class="bx bx-calendar me-1"></i>From Date (LR Date)</label>
                        <input type="date" max="9999-12-31" name="from_date" class="form-control" value="{{ request('from_date', $fromDate) }}">
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <label class="form-label fw-semibold text-muted small"><i class="bx bx-calendar me-1"></i>To Date (LR Date)</label>
                        <input type="date" max="9999-12-31" name="to_date" class="form-control" value="{{ request('to_date', $toDate) }}">
                    </div>
                    <div class="col-md-4 col-lg-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary shadow-sm"><i class="bx bx-filter-alt me-1"></i> Apply Filter</button>
                        <a href="{{ route('admin.reports.mis') }}" class="btn btn-outline-secondary"><i class="bx bx-refresh me-1"></i> Reset</a>
                    </div>
                    <div class="col-lg-2 text-lg-end text-muted small">
                        <i class="bx bx-info-circle me-1"></i> {{ $fromDate ? \Carbon\Carbon::parse($fromDate)->format('d M Y') : 'All Time' }} to {{ $toDate ? \Carbon\Carbon::parse($toDate)->format('d M Y') : 'Today' }}
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Primary Financial KPIs --}}
    <div class="mb-4">
        <h6 class="text-uppercase text-muted fw-bold mb-3 small"><i class="bx bx-pie-chart-alt-2 me-1"></i> Financial Overview</h6>
        <div class="row g-3">
            <div class="col-xl-3 col-md-6">
                <div class="card mis-card border-left-primary shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted fw-semibold d-block mb-1 small text-uppercase">Total LRs</span>
                                <h3 class="fw-bold mb-0 text-primary">{{ number_format($totalLR) }}</h3>
                            </div>
                            <div class="kpi-icon-avatar bg-label-primary">
                                <i class="bx bx-file"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card mis-card border-left-success shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted fw-semibold d-block mb-1 small text-uppercase">Total Revenue</span>
                                <h3 class="fw-bold mb-0 text-success">₹ {{ number_format($totalRevenue, 0) }}</h3>
                            </div>
                            <div class="kpi-icon-avatar bg-label-success">
                                <i class="bx bx-rupee"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card mis-card border-left-warning shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted fw-semibold d-block mb-1 small text-uppercase">Bilty Advance</span>
                                <h3 class="fw-bold mb-0 text-warning">₹ {{ number_format($totalAdvance, 0) }}</h3>
                            </div>
                            <div class="kpi-icon-avatar bg-label-warning">
                                <i class="bx bx-wallet"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card mis-card border-left-danger shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted fw-semibold d-block mb-1 small text-uppercase">Total Due / Outstanding</span>
                                <h3 class="fw-bold mb-0 text-danger">₹ {{ number_format($totalDue, 0) }}</h3>
                            </div>
                            <div class="kpi-icon-avatar bg-label-danger">
                                <i class="bx bx-error-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Operations & Fleet Metrics --}}
    <div class="mb-4">
        <h6 class="text-uppercase text-muted fw-bold mb-3 small"><i class="bx bx-truck me-1"></i> Fleet & Monthly Operations</h6>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-3">
            <div class="col">
                <div class="card mis-card shadow-sm h-100">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="kpi-sm-avatar bg-label-info">
                            <i class="bx bx-car"></i>
                        </div>
                        <div>
                            <span class="text-muted d-block small fw-semibold">Active Vehicles</span>
                            <h4 class="mb-0 fw-bold">{{ number_format($totalVehicles) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card mis-card shadow-sm h-100">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="kpi-sm-avatar bg-label-secondary">
                            <i class="bx bx-user"></i>
                        </div>
                        <div>
                            <span class="text-muted d-block small fw-semibold">Active Drivers</span>
                            <h4 class="mb-0 fw-bold">{{ number_format($totalDrivers) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card mis-card shadow-sm h-100">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="kpi-sm-avatar bg-label-warning">
                            <i class="bx bx-navigation"></i>
                        </div>
                        <div>
                            <span class="text-muted d-block small fw-semibold">Active Trips</span>
                            <h4 class="mb-0 fw-bold text-warning">{{ number_format($activeTrips) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card mis-card shadow-sm h-100">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="kpi-sm-avatar bg-label-primary">
                            <i class="bx bx-calendar-event"></i>
                        </div>
                        <div>
                            <span class="text-muted d-block small fw-semibold">This Month LR</span>
                            <h4 class="mb-0 fw-bold text-primary">{{ number_format($monthLR) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card mis-card shadow-sm h-100">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="kpi-sm-avatar bg-label-success">
                            <i class="bx bx-trending-up"></i>
                        </div>
                        <div>
                            <span class="text-muted d-block small fw-semibold">Month Revenue</span>
                            <h4 class="mb-0 fw-bold text-success">₹ {{ number_format($monthRevenue, 0) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Expense Breakdown --}}
    @php
        $totalOperatingExp = $totalFuelAmt + $totalFastTag + $totalAdBlue + $totalOtherExp + $totalTripAdvance;
    @endphp
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="text-uppercase text-muted fw-bold mb-0 small"><i class="bx bx-receipt me-1"></i> Fleet Operating Expenses</h6>
            <span class="badge bg-label-dark fs-6 fw-semibold">Total Operating Cost: ₹ {{ number_format($totalOperatingExp, 0) }}</span>
        </div>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-3">
            <div class="col">
                <div class="card mis-card shadow-sm h-100">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small fw-semibold">Fuel Expense</span>
                            <span class="badge bg-label-info rounded-circle p-1"><i class="bx bx-gas-pump"></i></span>
                        </div>
                        <h5 class="fw-bold mb-0">₹ {{ number_format($totalFuelAmt, 0) }}</h5>
                        <div class="text-muted small mt-1"><i class="bx bx-droplet me-1"></i>{{ number_format($totalFuelQty, 2) }} Liters</div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card mis-card shadow-sm h-100">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small fw-semibold">FastTag Toll</span>
                            <span class="badge bg-label-warning rounded-circle p-1"><i class="bx bx-credit-card"></i></span>
                        </div>
                        <h5 class="fw-bold mb-0">₹ {{ number_format($totalFastTag, 0) }}</h5>
                        <div class="text-muted small mt-1">Toll charges</div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card mis-card shadow-sm h-100">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small fw-semibold">AdBlue Fluid</span>
                            <span class="badge bg-label-primary rounded-circle p-1"><i class="bx bx-water"></i></span>
                        </div>
                        <h5 class="fw-bold mb-0">₹ {{ number_format($totalAdBlue, 0) }}</h5>
                        <div class="text-muted small mt-1">AdBlue total</div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card mis-card shadow-sm h-100">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small fw-semibold">Driver Advance</span>
                            <span class="badge bg-label-success rounded-circle p-1"><i class="bx bx-user-voice"></i></span>
                        </div>
                        <h5 class="fw-bold mb-0">₹ {{ number_format($totalTripAdvance, 0) }}</h5>
                        <div class="text-muted small mt-1">Trip advances</div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card mis-card shadow-sm h-100">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small fw-semibold">Other Expenses</span>
                            <span class="badge bg-label-danger rounded-circle p-1"><i class="bx bx-dots-horizontal-rounded"></i></span>
                        </div>
                        <h5 class="fw-bold mb-0">₹ {{ number_format($totalOtherExp, 0) }}</h5>
                        <div class="text-muted small mt-1">Miscellaneous</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Data Insight Tables --}}
    <div class="row">
        {{-- Top Vehicles --}}
        <div class="col-lg-6 mb-4">
            <div class="card mis-card shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between py-3">
                    <h5 class="card-title mb-0 fs-6 fw-bold"><i class="bx bx-trophy text-warning me-2"></i>Top 10 Vehicles by Activity</h5>
                    <span class="badge bg-label-primary">Leaderboard</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Vehicle</th>
                                <th class="text-center">Trips</th>
                                <th class="text-end">Freight</th>
                                <th class="text-end">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topVehicles as $i => $tv)
                            <tr>
                                <td>
                                    @if($i == 0)
                                        <span class="badge bg-warning text-dark rounded-circle px-2 py-1">1</span>
                                    @elseif($i == 1)
                                        <span class="badge bg-secondary text-white rounded-circle px-2 py-1">2</span>
                                    @elseif($i == 2)
                                        <span class="badge bg-danger text-white rounded-circle px-2 py-1">3</span>
                                    @else
                                        <span class="text-muted fw-semibold ms-1">{{ $i + 1 }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-xs me-2">
                                            <span class="avatar-initial rounded bg-label-secondary"><i class="bx bx-car"></i></span>
                                        </div>
                                        <span class="fw-bold">{{ $tv->vehicle?->vehicle_number ?? 'Unknown' }}</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-label-primary rounded-pill px-3 py-1">{{ $tv->trip_count }}</span>
                                </td>
                                <td class="text-end fw-semibold">₹ {{ number_format($tv->freight, 0) }}</td>
                                <td class="text-end fw-bold text-success">₹ {{ number_format($tv->revenue, 0) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="bx bx-folder-open display-6 d-block mb-2 text-muted opacity-50"></i>
                                    No vehicle activity records found
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Recent Trips --}}
        <div class="col-lg-6 mb-4">
            <div class="card mis-card shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between py-3">
                    <h5 class="card-title mb-0 fs-6 fw-bold"><i class="bx bx-time text-primary me-2"></i>Recent Trip Entries</h5>
                    <span class="badge bg-label-info">Latest</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>LR No</th>
                                <th>Vehicle</th>
                                <th>Driver</th>
                                <th>Route</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTrips as $rt)
                            <tr>
                                <td>
                                    <span class="fw-bold text-primary">#{{ $rt->lr_no }}</span>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ $rt->vehicle?->vehicle_number ?? '-' }}</span>
                                </td>
                                <td>
                                    <small class="text-body fw-medium">{{ $rt->driver?->name ?? '-' }}</small>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ $rt->originCity?->name ?? $rt->from_city }}
                                        <i class="bx bx-right-arrow-alt text-primary mx-1"></i>
                                        {{ $rt->destinationCity?->name ?? $rt->to_city }}
                                    </small>
                                </td>
                                <td>
                                    @if($rt->trip)
                                        @php
                                            $st = strtolower($rt->trip->status);
                                            $badgeClass = match($st) {
                                                'complete', 'completed' => 'bg-label-success',
                                                'running', 'in_transit' => 'bg-label-primary',
                                                'reject', 'cancelled' => 'bg-label-danger',
                                                default => 'bg-label-warning'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }} rounded-pill">
                                            {{ ucfirst($rt->trip->status) }}
                                        </span>
                                    @else
                                        <span class="badge bg-label-secondary rounded-pill">No Trip</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="bx bx-map-pin display-6 d-block mb-2 text-muted opacity-50"></i>
                                    No recent trip entries found
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

