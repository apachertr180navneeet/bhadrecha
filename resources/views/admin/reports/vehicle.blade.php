@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <div>
            <h5 class="fw-bold mb-1">Vehicle Report</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Reports</a></li>
                    <li class="breadcrumb-item active">Vehicle Performance</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.reports.trip') }}" class="btn btn-outline-primary"><i class="bx bx-trip me-1"></i> Trip Report</a>
            <a href="{{ route('admin.reports.driver-trip') }}" class="btn btn-outline-primary"><i class="bx bx-user me-1"></i> Driver Trip Report</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Vehicle & Driver Fuel Report</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.vehicle') }}" class="mb-4">
                <div class="row g-3">
                    @if($companies->isNotEmpty())
                    <div class="col-md-3">
                        <label class="form-label">Company</label>
                        <select name="company_id" class="form-select">
                            <option value="">All Companies</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-md-3">
                        <label class="form-label">Vehicle</label>
                        <select name="vehicle_id" class="form-select">
                            <option value="">All Vehicles</option>
                            @foreach($vehicleList as $v)
                                <option value="{{ $v->id }}" {{ request('vehicle_id') == $v->id ? 'selected' : '' }}>{{ $v->vehicle_number }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.reports.vehicle') }}" class="btn btn-outline-secondary">Reset</a>
                    <div class="float-end">
                        <a href="{{ route('admin.reports.vehicle.export', 'excel') . '?' . http_build_query(request()->except('page')) }}" class="btn btn-success btn-sm"><i class="bx bx-spreadsheet me-1"></i>Excel</a>
                        <a href="{{ route('admin.reports.vehicle.export', 'pdf') . '?' . http_build_query(request()->except('page')) }}" class="btn btn-danger btn-sm"><i class="bx bxs-file-pdf me-1"></i>PDF</a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Vehicle</th>
                            <th>Type</th>
                            <th class="text-center">Trips</th>
                            <th class="text-end">Total Fuel (L)</th>
                            <th class="text-end">Total Amount (₹)</th>
                            <th class="text-end">Total KM</th>
                            <th class="text-end">Avg KM/L</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vehicles as $vehicle)
                            @php
                                $avgKmL = $vehicle->total_fuel_qty > 0 
                                    ? round($vehicle->total_km / $vehicle->total_fuel_qty, 2) 
                                    : 0;
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><strong>{{ $vehicle->vehicle_number }}</strong></td>
                                <td>{{ $vehicle->vehicle_type ?? '-' }}</td>
                                <td class="text-center"><span class="badge bg-label-primary">{{ $vehicle->total_trips }}</span></td>
                                <td class="text-end">{{ number_format($vehicle->total_fuel_qty, 2) }}</td>
                                <td class="text-end">{{ number_format($vehicle->total_fuel_amount, 2) }}</td>
                                <td class="text-end">{{ number_format($vehicle->total_km, 2) }}</td>
                                <td class="text-end">
                                    @if($avgKmL > 0)
                                        <span class="badge bg-label-success">{{ $avgKmL }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No data found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($vehicles, 'links'))
                <div class="mt-3">
                    {{ $vehicles->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
