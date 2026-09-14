@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <div>
            <h5 class="fw-bold mb-1">Trips</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Trips</li>
                </ol>
            </nav>
        </div>
        <div>
            @canany(['create trips', 'import trip data'])
            <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#importTripModal"><i class="bx bx-import me-1"></i> Import</button>
            <a href="{{ route('admin.transport.trips.download-template') }}" class="btn btn-outline-secondary"><i class="bx bx-download me-1"></i> Template</a>
            @endcanany
        </div>
    </div>

    <!-- Status Cards -->
    @php
        $activeStatus = request('status');
        $statusCards = [
            'pending' => ['label' => 'Pending', 'icon' => 'bx bx-time', 'bg' => 'bg-warning'],
            'complete' => ['label' => 'Complete', 'icon' => 'bx bx-check-circle', 'bg' => 'bg-success'],
            'reject' => ['label' => 'Reject', 'icon' => 'bx bx-x-circle', 'bg' => 'bg-danger'],
        ];
    @endphp
    <div class="row g-2 mb-4">
        <div class="col-md-3 col-6">
            <a href="{{ route('admin.transport.trips.index', request()->except('status', 'page')) }}" class="text-decoration-none">
                <div class="card shadow-sm border-0 h-100 {{ !$activeStatus ? 'border-primary' : '' }}">
                    <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                        <div class="flex-shrink-0 rounded-3 p-2 bg-dark">
                            <i class="bx bx-list-ul text-white"></i>
                        </div>
                        <div>
                            <div class="fw-bold">{{ $totalTrips ?? 0 }}</div>
                            <div class="small text-muted">All</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @foreach($statusCards as $status => $card)
            @php $count = $statusCounts[$status] ?? 0; $isActive = $activeStatus === $status; @endphp
            <div class="col-md-3 col-6">
                <a href="{{ route('admin.transport.trips.index', array_merge(request()->except('status', 'page'), ['status' => $status])) }}" class="text-decoration-none">
                    <div class="card shadow-sm border-0 h-100 {{ $isActive ? 'border-primary' : '' }}">
                        <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                            <div class="flex-shrink-0 rounded-3 p-2 {{ $card['bg'] }}">
                                <i class="{{ $card['icon'] }} text-white"></i>
                            </div>
                            <div>
                                <div class="fw-bold">{{ $count }}</div>
                                <div class="small text-muted">{{ $card['label'] }}</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.transport.trips.index') }}" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search LR No, Truck No, Consignor or Consignee..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="complete" {{ request('status') == 'complete' ? 'selected' : '' }}>Complete</option>
                        <option value="reject" {{ request('status') == 'reject' ? 'selected' : '' }}>Reject</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="date_sort" class="form-select" onchange="this.form.submit()">
                        <option value="latest" {{ request('date_sort', 'latest') == 'latest' ? 'selected' : '' }}>Latest to Oldest</option>
                        <option value="oldest" {{ request('date_sort') == 'oldest' ? 'selected' : '' }}>Oldest to Latest</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
                @if(request()->filled('search') || request()->filled('status') || request('date_sort') === 'oldest')
                <div class="col-md-2">
                    <a href="{{ route('admin.transport.trips.index') }}" class="btn btn-outline-secondary w-100">Clear</a>
                </div>
                @endif
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th style="width: 70px;">Actions</th>
                        <th>LR No</th>
                        <th>
                            <a href="{{ request()->fullUrlWithQuery(['date_sort' => request('date_sort') === 'oldest' ? 'latest' : 'oldest']) }}" class="text-dark d-flex align-items-center gap-1 text-decoration-none">
                                Date <i class="bx {{ request('date_sort') === 'oldest' ? 'bx-sort-up text-primary' : 'bx-sort-down text-primary' }}"></i>
                            </a>
                        </th>
                        <th>Truck No</th>
                        <th>Consignor</th>
                        <th>Consignee</th>
                        <th>From → To</th>
                        <th>Weight</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($trips as $trip)
                    <tr>
                        <td>
                            <div class="d-inline-flex gap-1">
                                @if($trip->trip)
                                    @can('edit trips')
                                    <a href="{{ route('admin.transport.trips.edit', $trip->trip->id) }}" class="btn btn-sm btn-icon btn-outline-warning" title="Edit Trip">
                                        <i class="bx bx-edit"></i>
                                    </a>
                                    @endcan
                                    @can('delete trips')
                                    <form action="{{ route('admin.transport.trips.destroy', $trip->trip->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this trip record?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Delete Trip">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                @else
                                    @can('create trips')
                                    <a href="{{ route('admin.transport.trips.create', $trip->id) }}" class="btn btn-sm btn-icon btn-outline-success" title="Add Trip">
                                        <i class="bx bx-plus-circle"></i>
                                    </a>
                                    @endcan
                                @endif
                                @can('view bulties')
                                <a href="{{ route('admin.transport.bulties.show', $trip->id) }}" class="btn btn-sm btn-icon btn-outline-primary" title="View">
                                    <i class="bx bx-show"></i>
                                </a>
                                @endcan
                                @php
                                    $firstMatDoc = $trip->material_documents_list[0] ?? null;
                                @endphp
                                @if($firstMatDoc)
                                <a href="{{ $firstMatDoc }}" target="_blank" class="btn btn-sm btn-icon btn-outline-info" title="View Document">
                                    <i class="bx bx-file"></i>
                                </a>
                                @endif
                            </div>
                        </td>
                        <td class="fw-semibold">{{ $trip->lr_no }}</td>
                        <td>{{ $trip->lr_date ? date('d M Y', strtotime($trip->lr_date)) : '-' }}</td>
                        <td><strong>{{ $trip->vehicle->vehicle_number ?? '-' }}</strong></td>
                        <td>{{ $trip->consignor->name ?? '-' }}</td>
                        <td>{{ $trip->consignee->name ?? '-' }}</td>
                        <td>{{ $trip->originCity->name ?? '-' }} <i class="bx bx-chevron-right mx-1 text-muted"></i> {{ $trip->destinationCity->name ?? '-' }}</td>
                        <td>{{ number_format($trip->bultyItems->sum('weight'), 2) }} {{ $trip->bultyItems->pluck('unit')->filter()->unique()->values()->first() ?? 'kg' }}</td>
                        <td>₹{{ number_format($trip->total_amount, 2) }}</td>
                        <td>
                            @php
                                $tripStatus = $trip->trip?->status ?? 'pending';
                            @endphp
                            @if($trip->trip)
                                @can('edit trips')
                                <select class="form-select form-select-sm status-select" data-id="{{ $trip->trip->id }}" style="min-width:90px" {{ $tripStatus == 'complete' ? 'disabled' : '' }}>
                                    <option value="pending" {{ $tripStatus == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="complete" {{ $tripStatus == 'complete' ? 'selected' : '' }}>Complete</option>
                                    <option value="reject" {{ $tripStatus == 'reject' ? 'selected' : '' }}>Reject</option>
                                </select>
                                @else
                                @php
                                    $statusBadges = [
                                        'pending' => 'bg-label-warning',
                                        'complete' => 'bg-label-success',
                                        'reject' => 'bg-label-danger'
                                    ];
                                @endphp
                                <span class="badge {{ $statusBadges[$tripStatus] ?? 'bg-label-warning' }}">{{ ucfirst($tripStatus) }}</span>
                                @endcan
                            @else
                            <span class="badge bg-label-warning">Pending</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-4">
                            <p class="text-muted mb-0">No Trips with Material Documents Found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($trips->hasPages())
        <div class="card-footer bg-transparent border-top py-3">
            {{ $trips->links() }}
        </div>
        @endif
    </div>
</div>

<div class="modal fade" id="importTripModal" tabindex="-1" aria-labelledby="importTripModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.transport.trips.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="importTripModalLabel">Import Builty & Trips</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">Upload an Excel/CSV file to create or update <strong>Builty (LR)</strong> and <strong>Trip</strong> records together in the same sheet.</p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Choose Excel File (.xlsx, .xls, .csv) <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    </div>
                    <div class="alert alert-info py-2 mb-0">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="small"><i class="bx bx-info-circle me-1"></i> Need sample format?</span>
                            <a href="{{ route('admin.transport.trips.download-template') }}" class="btn btn-sm btn-link text-primary p-0 text-decoration-none fw-semibold">Download Template</a>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-upload me-1"></i> Import Trips</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).on('change', '.status-select', function() {
    const id = $(this).data('id');
    const status = $(this).val();
    $.ajax({
        url: '{{ url("admin/transport/trips") }}/' + id + '/toggle-status',
        method: 'POST',
        data: { _token: '{{ csrf_token() }}', status: status },
        success: function(res) {
            location.reload();
        }
    });
});
</script>
@endsection
