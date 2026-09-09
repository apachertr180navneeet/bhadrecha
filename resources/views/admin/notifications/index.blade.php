@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1"><i class="bx bx-bell me-2 text-primary"></i> Expiring Documents & E-Way Bill Alerts</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Expiry Alerts & Notifications</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <form method="POST" action="{{ route('admin.notifications.sync-check') }}">
                @csrf
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bx bx-sync me-1"></i> Scan For Expiring Records
                </button>
            </form>
            @if(($counts['unread'] ?? 0) > 0)
            <form method="POST" action="{{ route('admin.notifications.mark-all-read') }}">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="bx bx-check-double me-1"></i> Mark All as Read
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Alert / Feedback -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Metric Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl-2">
            <a href="{{ route('admin.notifications.index', ['type' => 'all', 'status' => $status]) }}" class="text-decoration-none">
                <div class="card shadow-sm border-0 h-100 {{ $type === 'all' ? 'border-primary border-2' : '' }}">
                    <div class="card-body p-3 text-center">
                        <div class="avatar avatar-sm mx-auto mb-2 bg-label-primary rounded-circle">
                            <i class="bx bx-bell fs-5"></i>
                        </div>
                        <h6 class="text-muted mb-1 small">Total Alerts</h6>
                        <h4 class="mb-0 fw-bold text-dark">{{ number_format($counts['all'] ?? 0) }}</h4>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-md-4 col-xl-2">
            <a href="{{ route('admin.notifications.index', ['type' => 'eway', 'status' => $status]) }}" class="text-decoration-none">
                <div class="card shadow-sm border-0 h-100 {{ $type === 'eway' ? 'border-danger border-2' : '' }}">
                    <div class="card-body p-3 text-center">
                        <div class="avatar avatar-sm mx-auto mb-2 bg-label-danger rounded-circle">
                            <i class="bx bx-receipt fs-5"></i>
                        </div>
                        <h6 class="text-muted mb-1 small">E-Way Bills</h6>
                        <h4 class="mb-0 fw-bold text-danger">{{ number_format($counts['eway'] ?? 0) }}</h4>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-md-4 col-xl-2">
            <a href="{{ route('admin.notifications.index', ['type' => 'vehicle', 'status' => $status]) }}" class="text-decoration-none">
                <div class="card shadow-sm border-0 h-100 {{ $type === 'vehicle' ? 'border-info border-2' : '' }}">
                    <div class="card-body p-3 text-center">
                        <div class="avatar avatar-sm mx-auto mb-2 bg-label-info rounded-circle">
                            <i class="bx bx-car fs-5"></i>
                        </div>
                        <h6 class="text-muted mb-1 small">Vehicle Docs</h6>
                        <h4 class="mb-0 fw-bold text-info">{{ number_format($counts['vehicle'] ?? 0) }}</h4>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-md-4 col-xl-2">
            <a href="{{ route('admin.notifications.index', ['type' => 'driver', 'status' => $status]) }}" class="text-decoration-none">
                <div class="card shadow-sm border-0 h-100 {{ $type === 'driver' ? 'border-success border-2' : '' }}">
                    <div class="card-body p-3 text-center">
                        <div class="avatar avatar-sm mx-auto mb-2 bg-label-success rounded-circle">
                            <i class="bx bx-id-card fs-5"></i>
                        </div>
                        <h6 class="text-muted mb-1 small">Driver Licenses</h6>
                        <h4 class="mb-0 fw-bold text-success">{{ number_format($counts['driver'] ?? 0) }}</h4>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-md-4 col-xl-2">
            <a href="{{ route('admin.notifications.index', ['type' => 'dms', 'status' => $status]) }}" class="text-decoration-none">
                <div class="card shadow-sm border-0 h-100 {{ $type === 'dms' ? 'border-warning border-2' : '' }}">
                    <div class="card-body p-3 text-center">
                        <div class="avatar avatar-sm mx-auto mb-2 bg-label-warning rounded-circle">
                            <i class="bx bx-file fs-5"></i>
                        </div>
                        <h6 class="text-muted mb-1 small">DMS Docs</h6>
                        <h4 class="mb-0 fw-bold text-warning">{{ number_format($counts['dms'] ?? 0) }}</h4>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-md-4 col-xl-2">
            <a href="{{ route('admin.notifications.index', ['type' => $type, 'status' => 'unread']) }}" class="text-decoration-none">
                <div class="card shadow-sm border-0 h-100 {{ $status === 'unread' ? 'border-danger border-2' : '' }}">
                    <div class="card-body p-3 text-center">
                        <div class="avatar avatar-sm mx-auto mb-2 bg-label-secondary rounded-circle">
                            <i class="bx bx-envelope fs-5"></i>
                        </div>
                        <h6 class="text-muted mb-1 small">Unread Alerts</h6>
                        <h4 class="mb-0 fw-bold text-danger">{{ number_format($counts['unread'] ?? 0) }}</h4>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Main Card with Filters and List -->
    <div class="card shadow-sm border-0">
        <!-- Filter Header -->
        <div class="card-header border-bottom bg-light py-3">
            <form method="GET" action="{{ route('admin.notifications.index') }}" class="row g-2 align-items-center">
                <input type="hidden" name="type" value="{{ $type }}">

                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="bx bx-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Search by LR No, Vehicle, Driver, or Document..." value="{{ $search }}">
                    </div>
                </div>

                <div class="col-md-2">
                    <select name="date_sort" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="latest" {{ request('date_sort', 'latest') == 'latest' ? 'selected' : '' }}>Latest to Oldest</option>
                        <option value="oldest" {{ request('date_sort') == 'oldest' ? 'selected' : '' }}>Oldest to Latest</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <div class="d-flex gap-1">
                        <a href="{{ route('admin.notifications.index', ['type' => $type, 'status' => 'all', 'search' => $search, 'date_sort' => request('date_sort')]) }}" class="btn btn-sm {{ $status === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">
                            All
                        </a>
                        <a href="{{ route('admin.notifications.index', ['type' => $type, 'status' => 'unread', 'search' => $search, 'date_sort' => request('date_sort')]) }}" class="btn btn-sm {{ $status === 'unread' ? 'btn-danger' : 'btn-outline-secondary' }}">
                            Unread ({{ $counts['unread'] ?? 0 }})
                        </a>
                        <a href="{{ route('admin.notifications.index', ['type' => $type, 'status' => 'read', 'search' => $search, 'date_sort' => request('date_sort')]) }}" class="btn btn-sm {{ $status === 'read' ? 'btn-success' : 'btn-outline-secondary' }}">
                            Read
                        </a>
                    </div>
                </div>

                <div class="col-md-3 text-md-end">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bx bx-filter-alt me-1"></i> Apply Filter
                    </button>
                    @if($search || $type !== 'all' || $status !== 'all' || request('date_sort') === 'oldest')
                    <a href="{{ route('admin.notifications.index') }}" class="btn btn-sm btn-outline-secondary ms-1">
                        Reset
                    </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Alert Items List -->
        <div class="card-body p-0">
            @if($notifications->isEmpty())
            <div class="text-center py-5">
                <div class="avatar avatar-xl bg-label-success rounded-circle mx-auto mb-3">
                    <i class="bx bx-check-double fs-1"></i>
                </div>
                <h5 class="fw-bold text-dark">No Notifications Found</h5>
                <p class="text-muted mb-0">All document expiry dates and E-Way bills are currently compliant.</p>
            </div>
            @else
            <div class="list-group list-group-flush">
                @foreach($notifications as $notif)
                @php
                    $isUnread = is_null($notif->read_at);
                    $data = json_decode($notif->data, true) ?: [];
                    
                    $iconClass = 'bx bx-file';
                    $badgeClass = 'bg-label-warning text-warning';
                    $typeLabel = 'Document';

                    if ($notif->notification_type === 'eway_bill_expiry') {
                        $iconClass = 'bx bx-receipt';
                        $badgeClass = 'bg-label-danger text-danger';
                        $typeLabel = 'E-Way Bill';
                    } elseif ($notif->notification_type === 'vehicle_document_expiry') {
                        $iconClass = 'bx bx-car';
                        $badgeClass = 'bg-label-info text-info';
                        $typeLabel = 'Vehicle Document';
                    } elseif ($notif->notification_type === 'driver_document_expiry') {
                        $iconClass = 'bx bx-id-card';
                        $badgeClass = 'bg-label-success text-success';
                        $typeLabel = 'Driver License';
                    }

                    $daysLeft = isset($data['days_left']) ? (int)$data['days_left'] : null;
                    $urgencyBadge = 'bg-warning text-dark';
                    $urgencyText = 'Expiring Soon';

                    if ($daysLeft !== null) {
                        if ($daysLeft < 0) {
                            $urgencyBadge = 'bg-danger text-white';
                            $urgencyText = 'Expired (' . abs($daysLeft) . 'd ago)';
                        } elseif ($daysLeft === 0) {
                            $urgencyBadge = 'bg-danger text-white';
                            $urgencyText = 'Expiring Today';
                        } elseif ($daysLeft === 1) {
                            $urgencyBadge = 'bg-warning text-dark';
                            $urgencyText = 'Expiring Tomorrow';
                        } else {
                            $urgencyBadge = 'bg-info text-white';
                            $urgencyText = "In {$daysLeft} days";
                        }
                    }
                @endphp
                <div class="list-group-item p-3 border-bottom {{ $isUnread ? 'bg-light bg-opacity-50' : '' }}">
                    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                        <div class="d-flex align-items-start gap-3">
                            <div class="avatar avatar-md flex-shrink-0">
                                <span class="avatar-initial rounded-circle {{ $badgeClass }} fs-4">
                                    <i class="{{ $iconClass }}"></i>
                                </span>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                    <span class="badge {{ $badgeClass }} fw-bold">{{ $typeLabel }}</span>
                                    <span class="badge {{ $urgencyBadge }} fw-bold">{{ $urgencyText }}</span>
                                    @if($isUnread)
                                    <span class="badge bg-danger rounded-pill">Unread</span>
                                    @endif
                                    <small class="text-muted ms-1"><i class="bx bx-time-five me-1"></i>{{ \Carbon\Carbon::parse($notif->created_at)->diffForHumans() }}</small>
                                </div>
                                <h6 class="fw-bold mb-1 text-dark">{{ $notif->title }}</h6>
                                
                                <div class="text-muted small mt-1" style="line-height: 1.5;">
                                    @if($notif->notification_type === 'eway_bill_expiry')
                                        <strong>Bilty No:</strong> <span class="text-dark">{{ $data['lr_no'] ?? '-' }}</span> |
                                        <strong>Document Type:</strong> <span class="text-dark">E-Way Bill</span> |
                                        <strong>E-Way Bill No:</strong> <span class="text-primary fw-bold">{{ $data['eway_bill_no'] ?? '-' }}</span> |
                                        <strong>Expiry Date:</strong> <span class="text-danger fw-bold">{{ $data['expiry_date'] ?? '-' }}</span>
                                        @if(!empty($data['vehicle_number']))
                                        | <strong>Vehicle:</strong> <span class="text-dark">{{ $data['vehicle_number'] }}</span>
                                        @endif
                                    @elseif($notif->notification_type === 'vehicle_document_expiry')
                                        <strong>Vehicle No:</strong> <span class="text-dark fw-bold">{{ $data['vehicle_number'] ?? '-' }}</span> |
                                        <strong>Document Type:</strong> <span class="text-dark">{{ $data['document_type'] ?? '-' }}</span> |
                                        <strong>Expiry Date:</strong> <span class="text-danger fw-bold">{{ $data['expiry_date'] ?? '-' }}</span>
                                    @elseif($notif->notification_type === 'driver_document_expiry')
                                        <strong>Driver Name:</strong> <span class="text-dark fw-bold">{{ $data['driver_name'] ?? '-' }}</span> |
                                        <strong>Document Type:</strong> <span class="text-dark">Driving License</span> |
                                        <strong>License No:</strong> <span class="text-dark">{{ $data['license_number'] ?? '-' }}</span> |
                                        <strong>Expiry Date:</strong> <span class="text-danger fw-bold">{{ $data['expiry_date'] ?? '-' }}</span>
                                    @else
                                        <strong>Document No:</strong> <span class="text-dark fw-bold">{{ $data['document_number'] ?? '-' }}</span> |
                                        <strong>Document Name:</strong> <span class="text-dark">{{ $data['document_name'] ?? '-' }}</span> |
                                        <strong>Expiry Date:</strong> <span class="text-danger fw-bold">{{ $data['expiry_date'] ?? '-' }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 align-self-end align-self-md-center flex-shrink-0">
                            @if(!empty($notif->url))
                            <a href="{{ $notif->url }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                <i class="bx bx-link-external me-1"></i> View Record
                            </a>
                            @endif

                            @if($isUnread)
                            <form method="POST" action="{{ route('admin.notifications.read', $notif->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-light border text-muted" title="Mark as read">
                                    <i class="bx bx-check"></i> Mark Read
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="p-3 d-flex justify-content-between align-items-center">
                <span class="text-muted small">Showing {{ $notifications->firstItem() ?? 0 }} to {{ $notifications->lastItem() ?? 0 }} of {{ $notifications->total() }} alerts</span>
                {{ $notifications->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
