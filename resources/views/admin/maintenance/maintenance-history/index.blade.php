@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <div>
            <h5 class="fw-bold mb-1">Maintenance History</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Maintenance</a></li>
                    <li class="breadcrumb-item active">Maintenance History</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.maintenance.maintenance-history.trashed') }}" class="btn btn-outline-danger"><i class="bx bx-trash me-1"></i> Recycle Bin</a>
            <a href="{{ route('admin.maintenance.maintenance-history.create') }}" class="btn btn-primary"><i class="bx bx-plus me-1"></i> New Record</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Vehicle</label>
                    <select name="vehicle_id" class="form-select">
                        <option value="">All Vehicles</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" {{ request('vehicle_id') == $vehicle->id ? 'selected' : '' }}>{{ $vehicle->vehicle_number }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Service type, vendor..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Vehicle</th>
                        <th>Service Type</th>
                        <th>Service Date</th>
                        <th class="text-end">Current KM</th>
                        <th>Vendor/Workshop</th>
                        <th class="text-end">Cost</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($histories as $history)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $history->vehicle?->vehicle_number ?? 'N/A' }}</strong></td>
                        <td>{{ $history->service_type }}</td>
                        <td>{{ $history->service_date?->format('d-m-Y') ?? '-' }}</td>
                        <td class="text-end">{{ $history->current_km ? number_format($history->current_km, 0) . ' km' : '-' }}</td>
                        <td>{{ $history->vendor?->name ?? $history->vendor_name ?? '-' }}</td>
                        <td class="text-end">{{ $history->cost ? '₹ ' . number_format($history->cost, 2) : '-' }}</td>
                        <td>
                            @php
                                $badge = ['completed' => 'success', 'pending' => 'warning', 'cancelled' => 'secondary'];
                            @endphp
                            <span class="badge bg-label-{{ $badge[$history->status] ?? 'secondary' }}">{{ ucfirst($history->status) }}</span>
                        </td>
                        <td class="text-center text-nowrap">
                            <a href="{{ route('admin.maintenance.maintenance-history.show', $history) }}" class="btn btn-sm btn-icon btn-outline-info" title="View"><i class="bx bx-show"></i></a>
                            <a href="{{ route('admin.maintenance.maintenance-history.edit', $history) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Edit"><i class="bx bx-edit"></i></a>
                            <form method="POST" action="{{ route('admin.maintenance.maintenance-history.destroy', $history) }}" class="d-inline" onsubmit="return confirm('Delete this record?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Delete"><i class="bx bx-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">No maintenance history records found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($histories, 'links'))
        <div class="card-footer">
            {{ $histories->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
