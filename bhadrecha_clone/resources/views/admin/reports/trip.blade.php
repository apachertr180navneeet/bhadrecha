@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <div>
            <h5 class="fw-bold mb-1">Trip Report</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Reports</a></li>
                    <li class="breadcrumb-item active">Trip Report</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.reports.vehicle') }}" class="btn btn-outline-primary"><i class="bx bx-car me-1"></i> Vehicle Report</a>
            <a href="{{ route('admin.reports.driver-trip') }}" class="btn btn-outline-primary"><i class="bx bx-user me-1"></i> Driver Trip Report</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">All Trips Report</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.trip') }}" class="mb-3 row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label">Trip Status</label>
                    <select name="trip_status" class="form-select">
                        <option value="">All</option>
                        <option value="pending" {{ request('trip_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="complete" {{ request('trip_status') == 'complete' ? 'selected' : '' }}>Complete</option>
                        <option value="reject" {{ request('trip_status') == 'reject' ? 'selected' : '' }}>Reject</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.reports.trip') }}" class="btn btn-outline-secondary">Reset</a>
                    <a href="{{ route('admin.reports.trip.export', 'excel') . '?' . http_build_query(request()->except('page')) }}" class="btn btn-success btn-sm"><i class="bx bx-spreadsheet me-1"></i> Excel</a>
                    <a href="{{ route('admin.reports.trip.export', 'pdf') . '?' . http_build_query(request()->except('page')) }}" class="btn btn-danger btn-sm"><i class="bx bxs-file-pdf me-1"></i> PDF</a>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>LR No</th>
                            <th>Trip Status</th>
                            <th class="text-end">Fuel (L)</th>
                            <th class="text-end">Fuel Amt</th>
                            <th class="text-end">FastTag</th>
                            <th class="text-end">AdBlue</th>
                            <th class="text-end">Other Exp</th>
                            <th class="text-end">Trip Advance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trips as $builty)
                        @php
                            $trip = $builty->trip;
                            $totalFuelQty = $trip?->fuelDetails->sum('quantity') ?? 0;
                            $totalFuelAmt = $trip?->fuelDetails->sum('amount') ?? 0;
                        @endphp
                        <tr>
                            <td><strong>{{ $builty->lr_no }}</strong></td>
                            <td>
                                @if($trip)
                                    <span class="badge bg-label-{{ $trip->status === 'complete' ? 'success' : ($trip->status === 'reject' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($trip->status) }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end">{{ number_format($totalFuelQty, 2) }}</td>
                            <td class="text-end">{{ number_format($totalFuelAmt, 2) }}</td>
                            <td class="text-end">{{ $trip ? number_format($trip->fasttag_total_amount, 2) : '-' }}</td>
                            <td class="text-end">{{ $trip ? number_format($trip->adblue_total_amount, 2) : '-' }}</td>
                            <td class="text-end">{{ $trip ? number_format($trip->other_amount, 2) : '-' }}</td>
                            <td class="text-end">{{ $trip ? number_format($trip->advance_total_amount, 2) : '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No data found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($trips, 'links'))
                <div class="mt-3">
                    {{ $trips->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
