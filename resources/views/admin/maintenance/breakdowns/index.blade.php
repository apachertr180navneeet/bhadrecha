@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <div>
            <h5 class="fw-bold mb-1">Breakdown Management</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Maintenance</a></li>
                    <li class="breadcrumb-item active">Breakdowns</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.maintenance.breakdowns.trashed') }}" class="btn btn-outline-danger"><i class="bx bx-trash me-1"></i> Recycle Bin</a>
            <a href="{{ route('admin.maintenance.breakdowns.create') }}" class="btn btn-primary"><i class="bx bx-plus me-1"></i> Report Breakdown</a>
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
                        <option value="reported" {{ request('status') == 'reported' ? 'selected' : '' }}>Reported</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="towed" {{ request('status') == 'towed' ? 'selected' : '' }}>Towed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Severity</label>
                    <select name="severity" class="form-select">
                        <option value="">All Severity</option>
                        <option value="minor" {{ request('severity') == 'minor' ? 'selected' : '' }}>Minor</option>
                        <option value="major" {{ request('severity') == 'major' ? 'selected' : '' }}>Major</option>
                        <option value="critical" {{ request('severity') == 'critical' ? 'selected' : '' }}>Critical</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Location, issue..." value="{{ request('search') }}">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-filter me-1"></i> Filter</button>
                    @if(request()->hasAny(['vehicle_id','status','severity','search']))
                    <a href="{{ route('admin.maintenance.breakdowns.index') }}" class="btn btn-outline-secondary">Clear</a>
                    @endif
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
                        <th>Date</th>
                        <th>Location</th>
                        <th>Issue Type</th>
                        <th>Severity</th>
                        <th class="text-end">Repair Cost</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($breakdowns as $breakdown)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $breakdown->vehicle?->vehicle_number ?? 'N/A' }}</strong></td>
                        <td>{{ $breakdown->breakdown_date?->format('d-m-Y') ?? '-' }}</td>
                        <td style="max-width:200px" class="text-truncate">{{ $breakdown->location }}</td>
                        <td>{{ $breakdown->issue_type }}</td>
                        <td>
                            @php
                                $sevBadge = ['minor' => 'success', 'major' => 'warning', 'critical' => 'danger'];
                            @endphp
                            <span class="badge bg-label-{{ $sevBadge[$breakdown->severity] ?? 'secondary' }}">{{ ucfirst($breakdown->severity) }}</span>
                        </td>
                        <td class="text-end">{{ $breakdown->repair_cost ? '₹ ' . number_format($breakdown->repair_cost, 2) : '-' }}</td>
                        <td>
                            @php
                                $badge = ['reported' => 'danger', 'in_progress' => 'warning', 'resolved' => 'success', 'towed' => 'secondary'];
                            @endphp
                            <span class="badge bg-label-{{ $badge[$breakdown->status] ?? 'secondary' }}">{{ str_replace('_', ' ', ucfirst($breakdown->status)) }}</span>
                        </td>
                        <td class="text-center text-nowrap">
                            <a href="{{ route('admin.maintenance.breakdowns.show', $breakdown) }}" class="btn btn-sm btn-icon btn-outline-info" title="View"><i class="bx bx-show"></i></a>
                            <a href="{{ route('admin.maintenance.breakdowns.edit', $breakdown) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Edit"><i class="bx bx-edit"></i></a>
                            @if($breakdown->status !== 'resolved')
                            <form method="POST" action="{{ route('admin.maintenance.breakdowns.mark-resolved', $breakdown) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-icon btn-outline-success" title="Mark Resolved"><i class="bx bx-check"></i></button>
                            </form>
                            @endif
                            <form method="POST" action="{{ route('admin.maintenance.breakdowns.destroy', $breakdown) }}" class="d-inline" onsubmit="return confirm('Delete this breakdown record?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Delete"><i class="bx bx-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">No breakdown records found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($breakdowns, 'links'))
        <div class="card-footer">
            {{ $breakdowns->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
