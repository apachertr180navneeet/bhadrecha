@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <div>
            <h5 class="fw-bold mb-1">Recycle Bin - Maintenance History</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Maintenance</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.maintenance.maintenance-history.index') }}">Maintenance History</a></li>
                    <li class="breadcrumb-item active">Recycle Bin</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.maintenance.maintenance-history.index') }}" class="btn btn-outline-primary"><i class="bx bx-arrow-back me-1"></i> Back</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Deleted Maintenance Records</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Vehicle</th>
                        <th>Service Type</th>
                        <th>Service Date</th>
                        <th>Deleted At</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($histories as $history)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $history->vehicle?->vehicle_number ?? 'N/A' }}</td>
                        <td>{{ $history->service_type }}</td>
                        <td>{{ $history->service_date?->format('d-m-Y') ?? '-' }}</td>
                        <td>{{ $history->deleted_at->format('d-m-Y h:i A') }}</td>
                        <td class="text-center text-nowrap">
                            <form method="POST" action="{{ route('admin.maintenance.maintenance-history.restore', $history->id) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-icon btn-outline-success" title="Restore"><i class="bx bx-refresh"></i></button>
                            </form>
                            <form method="POST" action="{{ route('admin.maintenance.maintenance-history.force-delete', $history->id) }}" class="d-inline" onsubmit="return confirm('Permanently delete this record? This cannot be undone.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Delete Permanently"><i class="bx bx-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No deleted records</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($histories, 'links'))
        <div class="card-footer">
            {{ $histories->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
