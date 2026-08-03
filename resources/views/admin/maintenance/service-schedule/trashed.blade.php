@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <div>
            <h5 class="fw-bold mb-1">Recycle Bin - Service Schedules</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Maintenance</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.maintenance.service-schedule.index') }}">Service Schedule</a></li>
                    <li class="breadcrumb-item active">Recycle Bin</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.maintenance.service-schedule.index') }}" class="btn btn-outline-primary"><i class="bx bx-arrow-back me-1"></i> Back</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Deleted Service Schedules</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Vehicle</th>
                        <th>Service Type</th>
                        <th>Deleted At</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schedules as $schedule)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $schedule->vehicle?->vehicle_number ?? 'N/A' }}</td>
                        <td>{{ $schedule->service_type }}</td>
                        <td>{{ $schedule->deleted_at->format('d-m-Y h:i A') }}</td>
                        <td class="text-center text-nowrap">
                            @if(auth()->user()->can('delete service schedules') || auth()->user()->isSuperAdmin())
                            <form method="POST" action="{{ route('admin.maintenance.service-schedule.restore', $schedule->id) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-icon btn-outline-success" title="Restore"><i class="bx bx-refresh"></i></button>
                            </form>
                            <form method="POST" action="{{ route('admin.maintenance.service-schedule.force-delete', $schedule->id) }}" class="d-inline" onsubmit="return confirm('Permanently delete this schedule? This cannot be undone.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Delete Permanently"><i class="bx bx-trash"></i></button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">No deleted schedules</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($schedules, 'links'))
        <div class="card-footer">
            {{ $schedules->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
