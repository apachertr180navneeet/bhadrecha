@extends('admin.layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light">FASTag /</span> Transaction Reports & Analytics
            </h4>
            <p class="text-muted mb-0 small">Daily and monthly toll expenditure breakdown, top plazas, and vehicle costs.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.fastag.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                <i class="bx bx-arrow-back"></i> Back to Transactions
            </a>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="card mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.fastag.reports') }}" class="row g-3 align-items-center">
                <div class="col-md-3 col-sm-6">
                    <label class="form-label small mb-1">From Date</label>
                    <input type="date" name="from_date" value="{{ $fromDate }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label small mb-1">To Date</label>
                    <input type="date" name="to_date" value="{{ $toDate }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-4 col-sm-12 d-flex gap-2 align-items-end">
                    <button type="submit" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1">
                        <i class="bx bx-filter"></i> Filter Report
                    </button>
                    <a href="{{ route('admin.fastag.reports', ['from_date' => now()->subDays(7)->format('Y-m-d'), 'to_date' => now()->format('Y-m-d')]) }}" class="btn btn-sm btn-outline-secondary">Last 7 Days</a>
                    <a href="{{ route('admin.fastag.reports', ['from_date' => now()->subDays(30)->format('Y-m-d'), 'to_date' => now()->format('Y-m-d')]) }}" class="btn btn-sm btn-outline-secondary">Last 30 Days</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <small class="text-muted d-block mb-1">Total Deductions</small>
                    <h4 class="card-title text-danger mb-1 font-monospace">₹{{ number_format($totalDeduction, 2) }}</h4>
                    <small class="text-muted">In selected period</small>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <small class="text-muted d-block mb-1">Refunded / Reversals</small>
                    <h4 class="card-title text-success mb-1 font-monospace">₹{{ number_format($totalRefund, 2) }}</h4>
                    <small class="text-muted">Credit / refund adjustments</small>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <small class="text-muted d-block mb-1">Failed Transactions</small>
                    <h4 class="card-title text-warning mb-1 font-monospace">{{ $failedCount }}</h4>
                    <small class="text-muted">Failed toll deduction attempts</small>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <small class="text-muted d-block mb-1">Disputed Deductions</small>
                    <h4 class="card-title text-info mb-1 font-monospace">{{ $disputedCount }}</h4>
                    <small class="text-muted">Flagged / under review</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Expenses & Top Plazas -->
    <div class="row g-4 mb-4">
        <!-- Daily Expenses Table -->
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                        <i class="bx bx-calendar text-primary fs-4"></i>
                        <span>Daily Toll Expenses</span>
                    </h5>
                    <small class="text-muted">{{ count($dailyExpenses) }} days recorded</small>
                </div>
                <div class="table-responsive text-nowrap" style="max-height: 380px; overflow-y: auto;">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Date</th>
                                <th class="text-center">Txns</th>
                                <th class="text-end">Toll Deducted</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dailyExpenses as $day)
                                <tr>
                                    <td>
                                        <span class="fw-semibold">{{ \Carbon\Carbon::parse($day->txn_date)->format('M d, Y (D)') }}</span>
                                    </td>
                                    <td class="text-center font-monospace">
                                        <span class="badge bg-label-primary">{{ $day->count }}</span>
                                    </td>
                                    <td class="text-end font-monospace fw-bold text-danger">
                                        ₹{{ number_format($day->total_amount, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">No toll expenses recorded in this period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top Toll Plazas -->
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                        <i class="bx bx-map-pin text-danger fs-4"></i>
                        <span>Top Toll Plazas</span>
                    </h5>
                </div>
                <div class="table-responsive text-nowrap" style="max-height: 380px; overflow-y: auto;">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Plaza Name</th>
                                <th class="text-center">Count</th>
                                <th class="text-end">Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topPlazas as $plaza)
                                <tr>
                                    <td>
                                        <span class="fw-semibold small text-truncate d-inline-block" style="max-width: 180px;" title="{{ $plaza->toll_plaza_name }}">
                                            {{ $plaza->toll_plaza_name ?: 'Unknown Plaza' }}
                                        </span>
                                    </td>
                                    <td class="text-center font-monospace">
                                        <small class="badge bg-label-secondary">{{ $plaza->count }}</small>
                                    </td>
                                    <td class="text-end font-monospace fw-semibold text-danger">
                                        ₹{{ number_format($plaza->total_amount, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">No plaza data available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Trend & Top Vehicles -->
    <div class="row g-4">
        <!-- Monthly Breakdown -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                        <i class="bx bx-bar-chart-alt text-success fs-4"></i>
                        <span>Monthly Toll Expenditure (Last 12 Months)</span>
                    </h5>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Month</th>
                                <th class="text-center">Transactions</th>
                                <th class="text-end">Toll Deducted</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($monthlyExpenses as $m)
                                <tr>
                                    <td>
                                        <span class="fw-semibold">{{ \Carbon\Carbon::parse($m->txn_month . '-01')->format('F Y') }}</span>
                                    </td>
                                    <td class="text-center font-monospace">
                                        <span class="badge bg-label-info">{{ $m->count }}</span>
                                    </td>
                                    <td class="text-end font-monospace fw-bold text-danger">
                                        ₹{{ number_format($m->total_amount, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">No monthly records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top Vehicles by Toll Cost -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                        <i class="bx bx-truck text-warning fs-4"></i>
                        <span>Top Vehicles by Toll Deductions</span>
                    </h5>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Vehicle Number</th>
                                <th class="text-center">Txns</th>
                                <th class="text-end">Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topVehicles as $v)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.fastag.index', ['vehicle_number' => $v->vehicle_number]) }}" class="font-monospace fw-bold text-primary">
                                            {{ $v->vehicle_number }}
                                        </a>
                                    </td>
                                    <td class="text-center font-monospace">
                                        <span class="badge bg-label-primary">{{ $v->count }}</span>
                                    </td>
                                    <td class="text-end font-monospace fw-bold text-danger">
                                        ₹{{ number_format($v->total_amount, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">No vehicle data available.</td>
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
