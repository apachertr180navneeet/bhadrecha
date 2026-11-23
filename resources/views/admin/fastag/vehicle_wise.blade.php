@extends('admin.layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light">FASTag /</span> Vehicle-wise Transactions
            </h4>
            <p class="text-muted mb-0 small">Aggregated toll deduction totals, vehicle-level analytics, and individual synchronization.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.fastag.index') }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-1">
                <i class="bx bx-list-ul"></i> All Transactions
            </a>
            <form method="POST" action="{{ route('admin.fastag.sync-all') }}" onsubmit="this.querySelector('button').disabled = true; this.querySelector('button i').className='bx bx-loader-alt bx-spin';">
                @csrf
                <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1 shadow-sm">
                    <i class="bx bx-sync"></i> Sync All Vehicles
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Search filter -->
    <div class="card mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.fastag.vehicle-wise') }}" class="row g-2 align-items-center">
                <div class="col-md-5 col-sm-8">
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search vehicle number, owner name...">
                    </div>
                </div>
                <div class="col-md-3 col-sm-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Search</button>
                    @if(request('search'))
                        <a href="{{ route('admin.fastag.vehicle-wise') }}" class="btn btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Vehicles Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Registered Trucks Toll Overview</h5>
            <small class="text-muted">Showing {{ $vehicles->firstItem() ?? 0 }} to {{ $vehicles->lastItem() ?? 0 }} of {{ $vehicles->total() }} vehicles</small>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Vehicle Number</th>
                        <th>Type / Make Model</th>
                        <th>Owner</th>
                        <th class="text-center">Total Txns</th>
                        <th class="text-end">Total Toll Paid</th>
                        <th>Last Transaction</th>
                        <th>Last Sync Status</th>
                        <th class="text-center">Quick Sync</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vehicles as $veh)
                        @php
                            $st = $stats->get($veh->vehicle_number);
                            $sync = $recentSyncs->get($veh->vehicle_number);
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('admin.fastag.index', ['vehicle_number' => $veh->vehicle_number]) }}" class="fw-bold font-monospace text-primary">
                                    {{ $veh->vehicle_number }}
                                </a>
                            </td>
                            <td>
                                <span>{{ $veh->make_model ?: 'N/A' }}</span>
                                <br><small class="text-muted">{{ $veh->vehicle_type ?: 'Truck' }}</small>
                            </td>
                            <td>
                                <span>{{ $veh->owner_name ?: '-' }}</span>
                                @if($veh->owner_phone)
                                    <br><small class="text-muted font-monospace">{{ $veh->owner_phone }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($st && $st->total_count > 0)
                                    <a href="{{ route('admin.fastag.index', ['vehicle_number' => $veh->vehicle_number]) }}" class="badge bg-label-primary font-monospace">
                                        {{ number_format($st->total_count) }}
                                    </a>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td class="text-end font-monospace fw-bold">
                                @if($st && $st->total_debit > 0)
                                    <span class="text-danger">₹{{ number_format($st->total_debit, 2) }}</span>
                                @else
                                    <span class="text-muted">₹0.00</span>
                                @endif
                            </td>
                            <td>
                                @if($st && $st->last_txn_date)
                                    <span>{{ \Carbon\Carbon::parse($st->last_txn_date)->format('M d, Y') }}</span>
                                    <br><small class="text-muted font-monospace">{{ \Carbon\Carbon::parse($st->last_txn_date)->format('h:i A') }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($sync)
                                    @if($sync->sync_status === 'SUCCESS')
                                        <span class="badge bg-label-success">Synced</span>
                                    @elseif($sync->sync_status === 'FAILED')
                                        <span class="badge bg-label-danger" title="{{ $sync->error_message }}">Failed</span>
                                    @else
                                        <span class="badge bg-label-warning">{{ $sync->sync_status }}</span>
                                    @endif
                                    <br><small class="text-muted">{{ $sync->completed_at ? $sync->completed_at->diffForHumans() : $sync->created_at->diffForHumans() }}</small>
                                @else
                                    <span class="badge bg-label-secondary">Never</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <form method="POST" action="{{ route('admin.fastag.sync') }}" class="d-inline" onsubmit="this.querySelector('button').disabled = true;">
                                    @csrf
                                    <input type="hidden" name="vehicle_number" value="{{ $veh->vehicle_number }}">
                                    <button type="submit" class="btn btn-sm btn-icon btn-outline-primary" title="Sync this vehicle">
                                        <i class="bx bx-sync"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bx bx-truck fs-1 d-block mb-2 opacity-50"></i>
                                No registered vehicles found matching the criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($vehicles->hasPages())
            <div class="card-footer d-flex justify-content-end py-2">
                {{ $vehicles->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
