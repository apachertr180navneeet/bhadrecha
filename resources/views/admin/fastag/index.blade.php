@extends('admin.layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header with Action -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light">Transport /</span> FASTag Transactions
            </h4>
            <p class="text-muted mb-0 small">BlackBuck FASTag toll deductions, transaction history, and live synchronization.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#syncModal">
                <i class="bx bx-sync fs-5"></i>
                <span>Sync Transactions</span>
            </button>
            <a href="{{ route('admin.fastag.reports') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                <i class="bx bx-bar-chart"></i> Reports
            </a>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bx bx-alarm-exclamation me-1"></i> {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Dashboard Summary Cards -->
    <div class="row g-4 mb-4">
        <!-- Total Transactions -->
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="bx bx-receipt fs-4"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Total Transactions</small>
                            <h5 class="card-title mb-0">{{ number_format($totalTransactions) }}</h5>
                        </div>
                    </div>
                    <small class="text-muted">Recorded toll events</small>
                </div>
            </div>
        </div>

        <!-- Total Toll Expenses -->
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-danger">
                                <i class="bx bx-rupee fs-4"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Total Toll Expenses</small>
                            <h5 class="card-title mb-0">₹{{ number_format($totalTollExpenses, 2) }}</h5>
                        </div>
                    </div>
                    <small class="text-muted">Total debit amount</small>
                </div>
            </div>
        </div>

        <!-- Today's Toll Expenses -->
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="bx bx-time fs-4"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Today's Toll</small>
                            <h5 class="card-title mb-0">₹{{ number_format($todayTollExpenses, 2) }}</h5>
                        </div>
                    </div>
                    <small class="text-muted">Debited today</small>
                </div>
            </div>
        </div>

        <!-- Registered Vehicles / Last Sync -->
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="bx bx-check-shield fs-4"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">FASTag Active Trucks</small>
                            <h5 class="card-title mb-0">{{ $fastagVehiclesCount }} <small class="text-muted fs-6">/ {{ $registeredVehiclesCount }}</small></h5>
                        </div>
                    </div>
                    <small class="text-muted text-truncate d-block" title="{{ $lastSync ? $lastSync->completed_at?->format('M d, Y h:i A') : 'No sync recorded' }}">
                        Last Sync: {{ $lastSync ? $lastSync->completed_at?->diffForHumans() : 'Never' }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-header pb-2 d-flex justify-content-between align-items-center">
            <h6 class="card-title mb-0 d-flex align-items-center gap-2">
                <i class="bx bx-filter text-primary fs-5"></i>
                <span>Filter Transactions</span>
            </h6>
            @if(request()->anyFilled(['vehicle_number', 'from_date', 'to_date', 'status', 'plaza', 'type']))
                <a href="{{ route('admin.fastag.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bx bx-reset"></i> Clear Filters
                </a>
            @endif
        </div>
        <div class="card-body pt-2">
            <form method="GET" action="{{ route('admin.fastag.index') }}" class="row g-3">
                <div class="col-md-3 col-sm-6">
                    <label class="form-label small">Vehicle Number</label>
                    <input type="text" name="vehicle_number" value="{{ request('vehicle_number') }}" class="form-control form-control-sm text-uppercase" placeholder="e.g. RJ09GA1234">
                </div>

                <div class="col-md-2 col-sm-6">
                    <label class="form-label small">From Date</label>
                    <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control form-control-sm">
                </div>

                <div class="col-md-2 col-sm-6">
                    <label class="form-label small">To Date</label>
                    <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control form-control-sm">
                </div>

                <div class="col-md-2 col-sm-6">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="SUCCESS" {{ request('status') === 'SUCCESS' ? 'selected' : '' }}>SUCCESS</option>
                        <option value="FAILED" {{ request('status') === 'FAILED' ? 'selected' : '' }}>FAILED</option>
                        <option value="PENDING" {{ request('status') === 'PENDING' ? 'selected' : '' }}>PENDING</option>
                        <option value="DISPUTED" {{ request('status') === 'DISPUTED' ? 'selected' : '' }}>DISPUTED</option>
                    </select>
                </div>

                <div class="col-md-2 col-sm-6">
                    <label class="form-label small">Type</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="DEBIT" {{ request('type') === 'DEBIT' ? 'selected' : '' }}>DEBIT</option>
                        <option value="CREDIT" {{ request('type') === 'CREDIT' ? 'selected' : '' }}>CREDIT</option>
                        <option value="REFUND" {{ request('type') === 'REFUND' ? 'selected' : '' }}>REFUND</option>
                    </select>
                </div>

                <div class="col-md-3 col-sm-6">
                    <label class="form-label small">Toll Plaza</label>
                    <input type="text" name="plaza" value="{{ request('plaza') }}" class="form-control form-control-sm" placeholder="Search toll plaza...">
                </div>

                <div class="col-md-3 col-sm-6 d-flex align-items-end">
                    <button type="submit" class="btn btn-sm btn-primary w-100 d-flex align-items-center justify-content-center gap-1">
                        <i class="bx bx-search"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">FASTag Toll Transactions</h5>
            <small class="text-muted">Showing {{ $transactions->firstItem() ?? 0 }} to {{ $transactions->lastItem() ?? 0 }} of {{ $transactions->total() }}</small>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Transaction ID</th>
                        <th>Vehicle Number</th>
                        <th>Toll Plaza</th>
                        <th class="text-end">Amount</th>
                        <th>Date & Time</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $txn)
                        <tr>
                            <td>
                                <span class="font-monospace fw-semibold small text-primary" title="{{ $txn->transaction_id }}">
                                    {{ Str::limit($txn->transaction_id, 16) }}
                                </span>
                                @if($txn->tag_id)
                                    <br><small class="text-muted font-monospace" style="font-size: 0.72rem;">Tag: {{ $txn->tag_id }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-label-dark font-monospace fw-bold">{{ $txn->vehicle_number }}</span>
                                @if($txn->vehicle)
                                    <br><small class="text-muted" style="font-size: 0.72rem;">{{ $txn->vehicle->make_model ?: $txn->vehicle->vehicle_type }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="fw-semibold">{{ $txn->toll_plaza_name ?: 'N/A' }}</span>
                                @if($txn->lane_code)
                                    <br><small class="text-muted" style="font-size: 0.72rem;">Lane: {{ $txn->lane_code }}</small>
                                @endif
                            </td>
                            <td class="text-end">
                                <span class="fw-bold font-monospace {{ $txn->transaction_type === 'REFUND' ? 'text-success' : 'text-danger' }}">
                                    {{ $txn->transaction_type === 'REFUND' ? '+' : '-' }}₹{{ number_format($txn->amount, 2) }}
                                </span>
                            </td>
                            <td>
                                <span class="text-nowrap">{{ $txn->transaction_date->format('M d, Y') }}</span>
                                <br><small class="text-muted font-monospace">{{ $txn->transaction_date->format('h:i:s A') }}</small>
                            </td>
                            <td>
                                @if($txn->transaction_type === 'DEBIT')
                                    <span class="badge bg-label-danger">DEBIT</span>
                                @elseif($txn->transaction_type === 'REFUND')
                                    <span class="badge bg-label-success">REFUND</span>
                                @else
                                    <span class="badge bg-label-info">{{ $txn->transaction_type }}</span>
                                @endif
                            </td>
                            <td>
                                @if($txn->transaction_status === 'SUCCESS')
                                    <span class="badge bg-label-success"><i class="bx bx-check-circle me-1"></i>SUCCESS</span>
                                @elseif($txn->transaction_status === 'FAILED')
                                    <span class="badge bg-label-danger"><i class="bx bx-x-circle me-1"></i>FAILED</span>
                                @elseif($txn->transaction_status === 'PENDING')
                                    <span class="badge bg-label-warning"><i class="bx bx-time me-1"></i>PENDING</span>
                                @else
                                    <span class="badge bg-label-secondary">{{ $txn->transaction_status }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-icon btn-outline-primary btn-view-txn" data-id="{{ $txn->id }}" title="View Full Payload & Details">
                                    <i class="bx bx-show"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bx bx-receipt fs-1 d-block mb-2 opacity-50"></i>
                                <h6>No FASTag transactions found</h6>
                                <p class="small text-muted mb-3">Sync transactions from BlackBuck API or adjust your search filters.</p>
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#syncModal">
                                    <i class="bx bx-sync me-1"></i> Sync Transactions Now
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
            <div class="card-footer d-flex justify-content-end py-2">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Sync Transactions Modal -->
<div class="modal fade" id="syncModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="syncForm" method="POST" action="{{ route('admin.fastag.sync') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title d-flex align-items-center gap-2">
                        <i class="bx bx-sync text-primary fs-4"></i>
                        <span>Sync FASTag Transactions</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">
                        Fetch real-time FASTag toll deductions from BlackBuck API and update the local database.
                    </p>

                    <!-- Scope selection -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Synchronization Scope</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="sync_scope" id="scopeSingle" value="single" checked>
                                <label class="form-check-label" for="scopeSingle">Single Truck</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="sync_scope" id="scopeAll" value="all">
                                <label class="form-check-label" for="scopeAll">All Registered Trucks</label>
                            </div>
                        </div>
                    </div>

                    <!-- Single vehicle picker -->
                    <div class="mb-3" id="vehiclePickerGroup">
                        <label class="form-label fw-semibold">Select Vehicle <span class="text-danger">*</span></label>
                        <select name="vehicle_number" id="vehicleSelect" class="form-select select2">
                            <option value="">-- Choose Truck / Vehicle --</option>
                            @foreach($vehicles as $veh)
                                <option value="{{ $veh->vehicle_number }}">{{ $veh->vehicle_number }} ({{ $veh->make_model ?: $veh->vehicle_type }})</option>
                            @endforeach
                        </select>
                        <div class="form-text">Or enter manually below if not registered yet.</div>
                        <input type="text" id="manualVehicleInput" class="form-control form-control-sm mt-1 text-uppercase" placeholder="Or enter manual vehicle number...">
                    </div>

                    <!-- Date range -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">From Date</label>
                            <input type="date" name="from_date" class="form-control" value="{{ \Carbon\Carbon::now()->subDays(3)->format('Y-m-d') }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">To Date</label>
                            <input type="date" name="to_date" class="form-control" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                        </div>
                    </div>

                    <div class="alert alert-light border small text-muted mb-0">
                        <i class="bx bx-info-circle me-1"></i> Transactions are deduplicated automatically based on the BlackBuck Transaction ID.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="btnSubmitSync" class="btn btn-primary d-inline-flex align-items-center gap-2">
                        <i class="bx bx-sync"></i>
                        <span id="syncBtnText">Start Synchronization</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Transaction Details Modal -->
<div class="modal fade" id="txnDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center gap-2">
                    <i class="bx bx-detail text-primary fs-4"></i>
                    <span>FASTag Transaction Details</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="txnModalBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var scopeSingle = document.getElementById('scopeSingle');
    var scopeAll = document.getElementById('scopeAll');
    var vehiclePickerGroup = document.getElementById('vehiclePickerGroup');
    var syncForm = document.getElementById('syncForm');
    var vehicleSelect = document.getElementById('vehicleSelect');
    var manualVehicleInput = document.getElementById('manualVehicleInput');
    var btnSubmitSync = document.getElementById('btnSubmitSync');
    var syncBtnText = document.getElementById('syncBtnText');

    function toggleScope() {
        if (scopeAll.checked) {
            vehiclePickerGroup.style.display = 'none';
            syncForm.action = "{{ route('admin.fastag.sync-all') }}";
            vehicleSelect.removeAttribute('required');
        } else {
            vehiclePickerGroup.style.display = 'block';
            syncForm.action = "{{ route('admin.fastag.sync') }}";
            vehicleSelect.setAttribute('required', 'required');
        }
    }

    if (scopeSingle && scopeAll) {
        scopeSingle.addEventListener('change', toggleScope);
        scopeAll.addEventListener('change', toggleScope);
    }

    if (manualVehicleInput) {
        manualVehicleInput.addEventListener('input', function () {
            if (this.value.trim() !== '') {
                vehicleSelect.value = '';
                var opt = document.createElement('option');
                opt.value = this.value.trim().toUpperCase();
                opt.text = this.value.trim().toUpperCase() + ' (Manual Entry)';
                opt.selected = true;
                vehicleSelect.appendChild(opt);
            }
        });
    }

    if (syncForm) {
        syncForm.addEventListener('submit', function () {
            btnSubmitSync.disabled = true;
            btnSubmitSync.querySelector('i').className = 'bx bx-loader-alt bx-spin';
            syncBtnText.textContent = 'Synchronizing with BlackBuck...';
        });
    }

    // Modal details viewer
    var detailButtons = document.querySelectorAll('.btn-view-txn');
    var txnModal = new bootstrap.Modal(document.getElementById('txnDetailModal'));
    var txnModalBody = document.getElementById('txnModalBody');

    detailButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.getAttribute('data-id');
            txnModalBody.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>';
            txnModal.show();

            fetch("{{ url('admin/fastag') }}/" + id, {
                headers: { 'Accept': 'application/json' }
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success && data.data) {
                    var t = data.data;
                    var rawJson = JSON.stringify(t.raw_response, null, 2);
                    txnModalBody.innerHTML = `
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="text-muted small d-block">Transaction ID</label>
                                <strong class="font-monospace">${t.transaction_id || 'N/A'}</strong>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small d-block">Vehicle Number</label>
                                <span class="badge bg-label-dark font-monospace fw-bold fs-6">${t.vehicle_number || 'N/A'}</span>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small d-block">Toll Plaza Name</label>
                                <strong>${t.toll_plaza_name || 'N/A'}</strong>
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted small d-block">Lane / Plaza Code</label>
                                <span>${t.lane_code || '-'} / ${t.plaza_code || '-'}</span>
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted small d-block">Tag ID</label>
                                <span class="font-monospace">${t.tag_id || 'N/A'}</span>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small d-block">Amount</label>
                                <h5 class="text-danger font-monospace mb-0">₹${parseFloat(t.amount).toFixed(2)}</h5>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small d-block">Transaction Time</label>
                                <span>${new Date(t.transaction_date).toLocaleString()}</span>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small d-block">Status / Type</label>
                                <span class="badge bg-label-success">${t.transaction_status}</span>
                                <span class="badge bg-label-primary">${t.transaction_type}</span>
                            </div>
                        </div>
                        <h6 class="border-top pt-3 mb-2 font-monospace small">Raw Provider JSON Payload</h6>
                        <pre class="bg-light p-3 rounded small text-dark" style="max-height: 280px; overflow-y: auto;"><code>${rawJson || 'No raw payload'}</code></pre>
                    `;
                } else {
                    txnModalBody.innerHTML = '<div class="alert alert-danger">Failed to load transaction details.</div>';
                }
            })
            .catch(function (err) {
                txnModalBody.innerHTML = '<div class="alert alert-danger">Error: ' + err.message + '</div>';
            });
        });
    });
});
</script>
@endsection
