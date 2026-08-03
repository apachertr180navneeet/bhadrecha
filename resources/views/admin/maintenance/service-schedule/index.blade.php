@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <div>
            <h5 class="fw-bold mb-1">Service Schedule</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Maintenance</a></li>
                    <li class="breadcrumb-item active">Service Schedule</li>
                </ol>
            </nav>
        </div>
        <div>
            @if(auth()->user()->can('delete service schedules') || auth()->user()->isSuperAdmin())
            <a href="{{ route('admin.maintenance.service-schedule.trashed') }}" class="btn btn-outline-danger"><i class="bx bx-trash me-1"></i> Recycle Bin</a>
            @endif
            @if(auth()->user()->can('create service schedules') || auth()->user()->isSuperAdmin())
            <a href="{{ route('admin.maintenance.service-schedule.create') }}" class="btn btn-primary"><i class="bx bx-plus me-1"></i> New Schedule</a>
            @endif
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
                <div class="col-md-4">
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
                        <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Service type..." value="{{ request('search') }}">
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
                        <th>Scheduled Date</th>
                        <th class="text-end">Scheduled KM</th>
                        <th>Last Service</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schedules as $schedule)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $schedule->vehicle?->vehicle_number ?? 'N/A' }}</strong></td>
                        <td>{{ $schedule->service_type }}</td>
                        <td>{{ $schedule->scheduled_date?->format('d-m-Y') ?? '-' }}</td>
                        <td class="text-end">{{ $schedule->scheduled_km ? number_format($schedule->scheduled_km, 0) . ' km' : '-' }}</td>
                        <td>{{ $schedule->last_service_date?->format('d-m-Y') ?? '-' }}</td>
                        <td>
                            @php
                                $badge = ['upcoming' => 'primary', 'overdue' => 'danger', 'completed' => 'success', 'cancelled' => 'secondary'];
                            @endphp
                            <span class="badge bg-label-{{ $badge[$schedule->status] ?? 'secondary' }}">{{ ucfirst($schedule->status) }}</span>
                        </td>
                        <td class="text-center text-nowrap">
                            @if(auth()->user()->can('view service schedules') || auth()->user()->isSuperAdmin())
                            <a href="{{ route('admin.maintenance.service-schedule.show', $schedule) }}" class="btn btn-sm btn-icon btn-outline-info" title="View"><i class="bx bx-show"></i></a>
                            @endif
                            @if(auth()->user()->can('edit service schedules') || auth()->user()->isSuperAdmin())
                            <a href="{{ route('admin.maintenance.service-schedule.edit', $schedule) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Edit"><i class="bx bx-edit"></i></a>
                            @endif
                            @if($schedule->status !== 'completed' && (auth()->user()->can('mark service schedules completed') || auth()->user()->can('edit service schedules') || auth()->user()->isSuperAdmin()))
                            <form method="POST" action="{{ route('admin.maintenance.service-schedule.mark-completed', $schedule) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-icon btn-outline-success" title="Mark Completed"><i class="bx bx-check"></i></button>
                            </form>
                            @endif
                            @if(auth()->user()->can('delete service schedules') || auth()->user()->isSuperAdmin())
                            <form method="POST" action="{{ route('admin.maintenance.service-schedule.destroy', $schedule) }}" class="d-inline" onsubmit="return confirm('Delete this schedule?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Delete"><i class="bx bx-trash"></i></button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">No service schedules found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($schedules, 'links'))
        <div class="card-footer">
            {{ $schedules->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
