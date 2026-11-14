@extends('admin.layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light">FASTag /</span> Synchronization History
            </h4>
            <p class="text-muted mb-0 small">Audit trail of API synchronization runs, execution status, and record counts.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.fastag.index') }}" class="btn btn-primary d-inline-flex align-items-center gap-1 shadow-sm">
                <i class="bx bx-receipt"></i> View Transactions
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.fastag.sync-logs') }}" class="row g-2 align-items-center">
                <div class="col-md-4 col-sm-6">
                    <input type="text" name="vehicle_number" value="{{ request('vehicle_number') }}" class="form-control form-control-sm text-uppercase" placeholder="Filter by vehicle number...">
                </div>
                <div class="col-md-3 col-sm-6">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="SUCCESS" {{ request('status') === 'SUCCESS' ? 'selected' : '' }}>SUCCESS</option>
                        <option value="FAILED" {{ request('status') === 'FAILED' ? 'selected' : '' }}>FAILED</option>
                        <option value="PARTIAL" {{ request('status') === 'PARTIAL' ? 'selected' : '' }}>PARTIAL</option>
                        <option value="PENDING" {{ request('status') === 'PENDING' ? 'selected' : '' }}>PENDING</option>
                    </select>
                </div>
                <div class="col-md-3 col-sm-6 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                    @if(request()->anyFilled(['vehicle_number', 'status']))
                        <a href="{{ route('admin.fastag.sync-logs') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">API Synchronization Runs</h5>
            <small class="text-muted">Showing {{ $logs->firstItem() ?? 0 }} to {{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }} logs</small>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#ID</th>
                        <th>Target Vehicle</th>
                        <th>Sync Date Range</th>
                        <th>Status</th>
                        <th class="text-center">Received</th>
                        <th class="text-center">Saved</th>
                        <th>Execution Time</th>
                        <th>Error Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>
                                <span class="font-monospace text-muted small">#{{ $log->id }}</span>
                            </td>
                            <td>
                                @if($log->vehicle_number === 'ALL_VEHICLES')
                                    <span class="badge bg-label-primary"><i class="bx bx-globe me-1"></i>All Trucks</span>
                                @else
                                    <span class="badge bg-label-dark font-monospace fw-bold">{{ $log->vehicle_number }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="small font-monospace">
                                    {{ $log->sync_from ? $log->sync_from->format('M d, Y') : '-' }} &rarr;
                                    {{ $log->sync_to ? $log->sync_to->format('M d, Y') : '-' }}
                                </span>
                            </td>
                            <td>
                                @if($log->sync_status === 'SUCCESS')
                                    <span class="badge bg-label-success"><i class="bx bx-check-circle me-1"></i>SUCCESS</span>
                                @elseif($log->sync_status === 'FAILED')
                                    <span class="badge bg-label-danger"><i class="bx bx-x-circle me-1"></i>FAILED</span>
                                @elseif($log->sync_status === 'PARTIAL')
                                    <span class="badge bg-label-warning"><i class="bx bx-error me-1"></i>PARTIAL</span>
                                @else
                                    <span class="badge bg-label-secondary">{{ $log->sync_status }}</span>
                                @endif
                            </td>
                            <td class="text-center font-monospace">
                                <span class="fw-semibold">{{ number_format($log->total_records_received) }}</span>
                            </td>
                            <td class="text-center font-monospace">
                                <span class="fw-bold text-success">{{ number_format($log->total_records_saved) }}</span>
                            </td>
                            <td>
                                <span class="small">{{ $log->started_at ? $log->started_at->format('M d, Y h:i A') : '-' }}</span>
                                @if($log->started_at && $log->completed_at)
                                    <br><small class="text-muted font-monospace" style="font-size: 0.72rem;">Duration: {{ $log->started_at->diffInSeconds($log->completed_at) }}s</small>
                                @endif
                            </td>
                            <td>
                                @if($log->error_message)
                                    <span class="small text-danger text-truncate d-inline-block" style="max-width: 250px;" title="{{ $log->error_message }}">
                                        {{ Str::limit($log->error_message, 45) }}
                                    </span>
                                @else
                                    <span class="text-muted small">None</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bx bx-sync fs-1 d-block mb-2 opacity-50"></i>
                                No synchronization logs recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="card-footer d-flex justify-content-end py-2">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
