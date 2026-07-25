@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <div>
            <h5 class="fw-bold mb-1">MIS Report</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Reports</a></li>
                    <li class="breadcrumb-item active">MIS Report</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.reports.vehicle-utilization') }}" class="btn btn-outline-primary btn-sm"><i class="bx bx-car me-1"></i> Vehicle Utilization</a>
            <a href="{{ route('admin.reports.mis.export', 'excel') . '?' . http_build_query(request()->except('page')) }}" class="btn btn-success btn-sm"><i class="bx bx-spreadsheet me-1"></i> Excel</a>
            <a href="{{ route('admin.reports.mis.export', 'pdf') . '?' . http_build_query(request()->except('page')) }}" class="btn btn-danger btn-sm"><i class="bx bxs-file-pdf me-1"></i> PDF</a>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.reports.mis') }}" class="mb-4">
        <div class="card">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">From Date (LR Date)</label>
                        <input type="date" max="9999-12-31" name="from_date" class="form-control" value="{{ request('from_date', $fromDate) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">To Date (LR Date)</label>
                        <input type="date" max="9999-12-31" name="to_date" class="form-control" value="{{ request('to_date', $toDate) }}">
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1"><i class="bx bx-filter-alt me-1"></i>Filter</button>
                        <a href="{{ route('admin.reports.mis') }}" class="btn btn-outline-secondary flex-grow-1"><i class="bx bx-reset me-1"></i>Reset</a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- KPI Cards Row 1 --}}
    <div class="row mb-4">
        <div class="col-md-3 col-6 mb-2">
            <div class="card bg-label-primary">
                <div class="card-body text-center py-3">
                    <h6 class="mb-1">Total LR</h6>
                    <h3 class="mb-0">{{ $totalLR }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-2">
            <div class="card bg-label-success">
                <div class="card-body text-center py-3">
                    <h6 class="mb-1">Total Revenue</h6>
                    <h4 class="mb-0">₹ {{ number_format($totalRevenue, 0) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-2">
            <div class="card bg-label-warning">
                <div class="card-body text-center py-3">
                    <h6 class="mb-1">Bilty Advance</h6>
                    <h4 class="mb-0">₹ {{ number_format($totalAdvance, 0) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-2">
            <div class="card bg-label-danger">
                <div class="card-body text-center py-3">
                    <h6 class="mb-1">Total Due</h6>
                    <h4 class="mb-0">₹ {{ number_format($totalDue, 0) }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- KPI Cards Row 2 --}}
    <div class="row mb-4">
        <div class="col-md-2 col-6 mb-2">
            <div class="card bg-label-info">
                <div class="card-body text-center py-3">
                    <h6 class="mb-1">Vehicles</h6>
                    <h3 class="mb-0">{{ $totalVehicles }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6 mb-2">
            <div class="card bg-label-secondary">
                <div class="card-body text-center py-3">
                    <h6 class="mb-1">Drivers</h6>
                    <h3 class="mb-0">{{ $totalDrivers }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6 mb-2">
            <div class="card bg-label-warning">
                <div class="card-body text-center py-3">
                    <h6 class="mb-1">Active Trips</h6>
                    <h3 class="mb-0">{{ $activeTrips }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-2">
            <div class="card bg-label-primary">
                <div class="card-body text-center py-3">
                    <h6 class="mb-1">This Month LR</h6>
                    <h3 class="mb-0">{{ $monthLR }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-2">
            <div class="card bg-label-success">
                <div class="card-body text-center py-3">
                    <h6 class="mb-1">Month Revenue</h6>
                    <h4 class="mb-0">₹ {{ number_format($monthRevenue, 0) }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Expenses Row --}}
    <div class="row mb-4">
        <div class="col-md-3 col-6 mb-2">
            <div class="card border border-info">
                <div class="card-body text-center py-2">
                    <h6 class="mb-1">Total Fuel</h6>
                    <h5 class="mb-0">{{ number_format($totalFuelQty, 2) }} L</h5>
                    <small class="text-muted">₹ {{ number_format($totalFuelAmt, 0) }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-2">
            <div class="card border border-warning">
                <div class="card-body text-center py-2">
                    <h6 class="mb-1">FastTag</h6>
                    <h5 class="mb-0">₹ {{ number_format($totalFastTag, 0) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-2">
            <div class="card border border-primary">
                <div class="card-body text-center py-2">
                    <h6 class="mb-1">AdBlue</h6>
                    <h5 class="mb-0">₹ {{ number_format($totalAdBlue, 0) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-2">
            <div class="card border border-danger">
                <div class="card-body text-center py-2">
                    <h6 class="mb-1">Other Expenses</h6>
                    <h5 class="mb-0">₹ {{ number_format($totalOtherExp, 0) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-2">
            <div class="card border border-success">
                <div class="card-body text-center py-2">
                    <h6 class="mb-1">Driver Trip Advance</h6>
                    <h5 class="mb-0">₹ {{ number_format($totalTripAdvance, 0) }}</h5>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Top Vehicles --}}
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Top 10 Vehicles by Trips</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Vehicle</th>
                                <th class="text-center">Trips</th>
                                <th class="text-end">Freight</th>
                                <th class="text-end">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topVehicles as $i => $tv)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td><strong>{{ $tv->vehicle?->vehicle_number ?? 'Unknown' }}</strong></td>
                                <td class="text-center"><span class="badge bg-label-primary">{{ $tv->trip_count }}</span></td>
                                <td class="text-end">₹ {{ number_format($tv->freight, 0) }}</td>
                                <td class="text-end">₹ {{ number_format($tv->revenue, 0) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center">No data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Recent Trips --}}
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Recent Trips</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
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
                                <td><strong>{{ $rt->lr_no }}</strong></td>
                                <td>{{ $rt->vehicle?->vehicle_number ?? '-' }}</td>
                                <td>{{ $rt->driver?->name ?? '-' }}</td>
                                <td>{{ $rt->originCity?->name ?? $rt->from_city }} → {{ $rt->destinationCity?->name ?? $rt->to_city }}</td>
                                <td>
                                    @if($rt->trip)
                                        <span class="badge bg-label-{{ $rt->trip->status === 'complete' ? 'success' : ($rt->trip->status === 'reject' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($rt->trip->status) }}
                                        </span>
                                    @else
                                        <span class="badge bg-label-secondary">No Trip</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center">No recent trips</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
