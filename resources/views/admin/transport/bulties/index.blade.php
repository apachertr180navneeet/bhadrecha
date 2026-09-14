@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <div>
            <h5 class="fw-bold mb-1">Bilties (Lorry Receipts)</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Bilties</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @canany(['create trips', 'create bulties', 'import trip data'])
            <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#importTripModal"><i class="bx bx-import me-1"></i> Import</button>
            <a href="{{ route('admin.transport.trips.download-template') }}" class="btn btn-outline-secondary"><i class="bx bx-download me-1"></i> Template</a>
            @endcanany
            @canany(['restore bulties', 'force delete bulties'])
            <a href="{{ route('admin.transport.bulties.trashed') }}" class="btn btn-outline-danger"><i class="bx bx-trash me-1"></i> Recycle Bin</a>
            @endcanany
            @can('create bulties')
            <a href="{{ route('admin.transport.bulties.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> New Bilty
            </a>
            @endcan
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.transport.bulties.index') }}" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search LR No, Truck No, Consignor or Consignee..." value="{{ request('search') }}">
                </div>

                <div class="col-md-2">

                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" placeholder="Start Date">

                </div>

                <div class="col-md-2">

                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}" placeholder="End Date">

                </div>
                <div class="col-md-2">
                    <select name="date_sort" class="form-select" onchange="this.form.submit()">
                        <option value="latest" {{ request('date_sort', 'latest') == 'latest' ? 'selected' : '' }}>Latest to Oldest</option>
                        <option value="oldest" {{ request('date_sort') == 'oldest' ? 'selected' : '' }}>Oldest to Latest</option>
                    </select>
                </div>

                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
                @if(request()->filled('search') || request()->filled('from_date') || request()->filled('to_date') || request('date_sort') === 'oldest')
                <div class="col-md-2">
                    <a href="{{ route('admin.transport.bulties.index') }}" class="btn btn-outline-secondary w-100">Clear</a>
                </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Status Cards -->
    @php
        $activeStatus = request('status');
        $statusCards = [
            'pending' => ['label' => 'Pending', 'icon' => 'bx bx-time', 'bg' => 'bg-secondary'],
            'planned' => ['label' => 'Planned', 'icon' => 'bx bx-calendar-check', 'bg' => 'bg-info'],
            'dispatched' => ['label' => 'Dispatched', 'icon' => 'bx bx-log-out', 'bg' => 'bg-warning'],
            'in_transit' => ['label' => 'In Transit', 'icon' => 'bx bx-transfer', 'bg' => 'bg-primary'],
            'partially_delivered' => ['label' => 'Partial Delivered', 'icon' => 'bx bx-package', 'bg' => 'bg-warning'],
            'delivered' => ['label' => 'Delivered', 'icon' => 'bx bx-check-circle', 'bg' => 'bg-success'],
            'rejected' => ['label' => 'Rejected', 'icon' => 'bx bx-x-circle', 'bg' => 'bg-danger'],
        ];
    @endphp
    <div class="row g-2 mb-4">
        <div class="col-md col-4">
            <a href="{{ route('admin.transport.bulties.index') }}" class="text-decoration-none">
                <div class="card shadow-sm border-0 h-100 {{ !$activeStatus ? 'border-primary' : '' }}">
                    <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                        <div class="flex-shrink-0 rounded-3 p-2 bg-dark">
                            <i class="bx bx-list-ul text-white"></i>
                        </div>
                        <div>
                            <div class="fw-bold">{{ array_sum($statusCounts) }}</div>
                            <div class="small text-muted">All</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @foreach($statusCards as $status => $card)
            @php $count = $statusCounts[$status] ?? 0; $isActive = $activeStatus === $status; @endphp
            <div class="col-md col-4">
                <a href="{{ route('admin.transport.bulties.index', array_merge(request()->except('status'), ['status' => $status])) }}" class="text-decoration-none">
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

    <!-- Table -->
    <div class="card" style="overflow: visible;">
        <div class="table-responsive text-nowrap" style="overflow: visible; position: relative; z-index: 5;">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">Actions</th>
                        <th>LR No</th>
                        <th>
                            <a href="{{ request()->fullUrlWithQuery(['date_sort' => request('date_sort') === 'oldest' ? 'latest' : 'oldest']) }}" class="text-dark d-flex align-items-center gap-1 text-decoration-none">
                                Date <i class="bx {{ request('date_sort') === 'oldest' ? 'bx-sort-up text-primary' : 'bx-sort-down text-primary' }}"></i>
                            </a>
                        </th>
                        <th>Truck No</th>
                        <th>Company</th>
                        <th>Branch</th>
                        <th>Consignor</th>
                        <th>Consignee</th>
                        <th>From → To</th>
                        <th>Weight</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bulties as $bulty)
                    <tr>
                        <td>
                            @canany(['view bulties', 'edit bulties', 'cancel bulties', 'delete bulties'])
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown" data-bs-boundary="viewport"><i class="bx bx-dots-vertical-rounded"></i></button>
                                <div class="dropdown-menu">
                                    @can('view bulties')
                                    <a class="dropdown-item" href="{{ route('admin.transport.bulties.show', $bulty->id) }}"><i class="bx bx-show me-1"></i> View</a>
                                    @endcan
                                    @can('edit bulties')
                                    <a class="dropdown-item" href="{{ route('admin.transport.bulties.edit', $bulty->id) }}"><i class="bx bx-edit me-1"></i> Edit</a>
                                    @endcan
                                    @can('cancel bulties')
                                    @if(in_array($bulty->status, ['pending', 'planned']))
                                    <button type="button" class="dropdown-item text-danger" onclick="handleReject({{ $bulty->id }}, '{{ $bulty->lr_no }}')"><i class="bx bx-x-circle me-1"></i> Reject</button>
                                    @endif
                                    @endcan
                                    @can('delete bulties')
                                    @if(in_array($bulty->status, ['pending', 'planned']))
                                    <button type="button" class="dropdown-item text-danger" onclick="handleDelete({{ $bulty->id }}, '{{ $bulty->lr_no }}')"><i class="bx bx-trash me-1"></i> Delete</button>
                                    @endif
                                    @endcan
                                </div>
                            </div>
                            @endcanany
                        </td>
                        <td class="fw-semibold">{{ $bulty->lr_no }}</td>
                        <td>{{ $bulty->lr_date ? date('d M Y', strtotime($bulty->lr_date)) : '-' }}</td>
                        <td><strong>{{ $bulty->vehicle->vehicle_number ?? '-' }}</strong></td>
                        <td>{{ $bulty->company->name ?? '-' }}</td>
                        <td>{{ $bulty->branch->name ?? '-' }}</td>
                        <td>{{ $bulty->consignor->name ?? '-' }}</td>
                        <td>{{ $bulty->consignee->name ?? '-' }}</td>
                        <td>{{ $bulty->originCity->name ?? '-' }} <i class="bx bx-chevron-right mx-1 text-muted"></i> {{ $bulty->destinationCity->name ?? '-' }}</td>
                        <td>{{ number_format($bulty->bultyItems->sum('weight'), 2) }} {{ $bulty->bultyItems->first()?->unit ?? 'kg' }}</td>
                        <td>₹{{ number_format($bulty->total_amount, 2) }}</td>
                        <td>
                            @php
                                $statusColors = [
                                    'pending' => 'secondary', 'planned' => 'info',
                                    'dispatched' => 'warning', 'in_transit' => 'primary',
                                    'partially_delivered' => 'warning',
                                    'delivered' => 'success', 'rejected' => 'danger'
                                ];
                                $color = $statusColors[$bulty->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-label-{{ $color }}">{{ ucfirst(str_replace('_', ' ', $bulty->status)) }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="text-center py-4">
                            <p class="text-muted mb-0">No Bilties Found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($bulties->hasPages())
        <div class="card-footer bg-transparent border-top py-3">
            {{ $bulties->links() }}
        </div>
        @endif
    </div>
</div>

<form id="delete-form" method="POST" style="display: none;">@csrf @method('DELETE')</form>
<form id="reject-form" method="POST" style="display: none;">@csrf</form>

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
                    <button type="submit" class="btn btn-primary"><i class="bx bx-upload me-1"></i> Import Bilties & Trips</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    function handleDelete(id, lrNo) {
        Swal.fire({
            title: 'Delete Bilty?',
            text: "Are you sure you want to delete Bilty '" + lrNo + "'?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, Delete it',
            cancelButtonText: 'Cancel',
            customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-secondary' },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('delete-form');
                form.action = "{{ url('admin/transport/bulties') }}/" + id;
                form.submit();
            }
        })
    }

    function handleReject(id, lrNo) {
        Swal.fire({
            title: 'Reject Bilty?',
            text: "Are you sure you want to reject Bilty '" + lrNo + "'? This will move it to Recycle Bin.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, Reject it',
            cancelButtonText: 'Cancel',
            customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-secondary' },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('reject-form');
                form.action = "{{ url('admin/transport/bulties') }}/" + id + "/reject";
                form.submit();
            }
        })
    }
</script>
@endsection