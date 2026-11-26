@extends('admin.layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light">FASTag /</span> Fetch Transactions
            </h4>
            <p class="text-muted mb-0 small">Directly pull toll deduction history from BlackBuck API for one or more trucks.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.fastag.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                <i class="bx bx-receipt"></i> View Transactions
            </a>
            <a href="{{ route('admin.fastag.config') }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-1">
                <i class="bx bx-cog"></i> API Config
            </a>
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

    <!-- Dynamic AJAX Notification Alert Box -->
    <div id="fetchAlertBox" class="d-none mb-4"></div>

    <div class="row g-4">
        <!-- Fetch Form Card -->
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                        <i class="bx bx-cloud-download text-primary fs-4"></i>
                        <span>Fetch Parameters (BlackBuck API)</span>
                    </h5>
                    <span class="badge bg-label-info">POST Request</span>
                </div>
                <div class="card-body">
                    <form id="fetchForm" method="POST" action="{{ route('admin.fastag.sync') }}">
                        @csrf

                        <!-- Account info -->
                        <div class="mb-3 p-3 bg-light rounded d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted d-block">BlackBuck Account Phone</small>
                                <span class="fw-bold font-monospace text-dark">{{ config('blackbuck.phone_number', '9829894925') }}</span>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block">Filter Mode</small>
                                <span class="badge bg-label-primary">SELECTIVE</span>
                            </div>
                        </div>

                        <!-- Truck Multi-Select -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0">Select Trucks / Vehicles <span class="text-danger">*</span></label>
                                <div class="d-flex gap-2">
                                    <button type="button" id="btnSelectAllTrucks" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size: 0.75rem;">Select All</button>
                                    <button type="button" id="btnClearTrucks" class="btn btn-xs btn-outline-secondary py-0 px-2" style="font-size: 0.75rem;">Clear</button>
                                </div>
                            </div>
                            <select name="truck_numbers[]" id="truckNumbersSelect" class="form-select" multiple size="8" required>
                                @foreach($vehicles as $veh)
                                    <option value="{{ $veh->vehicle_number }}">
                                        {{ $veh->vehicle_number }} &nbsp;—&nbsp; {{ $veh->make_model ?: ($veh->vehicle_type ?: 'Truck') }} {{ $veh->owner_name ? '('.$veh->owner_name.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Hold <kbd>Ctrl</kbd> (or <kbd>Cmd</kbd> on Mac) to select multiple trucks.</div>
                        </div>

                        <!-- Date and Time Filters -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">From Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="from_time" id="fromTimeInput" class="form-control" value="{{ \Carbon\Carbon::now()->subDays(3)->startOfDay()->format('Y-m-d\TH:i') }}" required>
                                <div class="form-text">Converted automatically to milliseconds Unix timestamp.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">To Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="to_time" id="toTimeInput" class="form-control" value="{{ \Carbon\Carbon::now()->endOfDay()->format('Y-m-d\TH:i') }}" required>
                                <div class="form-text">Must be after or equal to From Time.</div>
                            </div>
                        </div>

                        <!-- Submit Button with Spinner -->
                        <div class="d-flex align-items-center gap-3">
                            <button type="submit" id="btnFetchSubmit" class="btn btn-primary px-4 d-inline-flex align-items-center gap-2 shadow-sm">
                                <i class="bx bx-download fs-5"></i>
                                <span id="fetchBtnText">Fetch Transactions</span>
                            </button>
                            <div id="fetchSpinner" class="d-none d-flex align-items-center gap-2 text-primary small">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                                <span>Querying BlackBuck API... please wait</span>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Side: API Spec & Recent Runs -->
        <div class="col-lg-5">
            <!-- Specification Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                        <i class="bx bx-code-alt text-info fs-4"></i>
                        <span>API Request Payload Preview</span>
                    </h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-2">The system dispatches the following JSON body using <code>Authorization: Token ...</code>:</p>
                    <pre class="bg-light p-3 rounded small text-dark mb-3 font-monospace" style="font-size: 0.78rem;"><code>{
  "phone_number": {{ config('blackbuck.phone_number', 9829894925) }},
  "from_time": <span id="previewFromMs">1794444318000</span>,
  "to_time": <span id="previewToMs">1794465918000</span>,
  "truck_filter": "SELECTIVE",
  "truck_numbers": <span id="previewTrucks">["RJ19GJ3506"]</span>
}</code></pre>

                    <div class="alert alert-light border small text-muted mb-0">
                        <i class="bx bx-shield-check text-success me-1"></i>
                        <span>Transactions are deduplicated using BlackBuck unique IDs via <code>updateOrCreate</code>. Existing transactions will not be duplicated.</span>
                    </div>
                </div>
            </div>

            <!-- Recent Syncs Card -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">Recent Fetch Attempts</h6>
                    <a href="{{ route('admin.fastag.sync-logs') }}" class="small">View All</a>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Target</th>
                                <th>Status</th>
                                <th class="text-end">Saved</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentSyncs as $sync)
                                <tr>
                                    <td>
                                        <span class="small font-monospace fw-semibold">{{ Str::limit($sync->vehicle_number, 14) }}</span>
                                    </td>
                                    <td>
                                        @if($sync->sync_status === 'SUCCESS')
                                            <span class="badge bg-label-success" style="font-size: 0.7rem;">OK</span>
                                        @elseif($sync->sync_status === 'FAILED')
                                            <span class="badge bg-label-danger" style="font-size: 0.7rem;" title="{{ $sync->error_message }}">Failed</span>
                                        @else
                                            <span class="badge bg-label-warning" style="font-size: 0.7rem;">{{ $sync->sync_status }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end font-monospace small">
                                        {{ $sync->total_records_saved }}
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $sync->created_at->diffForHumans() }}</small>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted small">No fetch attempts recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var truckSelect = document.getElementById('truckNumbersSelect');
    var btnSelectAll = document.getElementById('btnSelectAllTrucks');
    var btnClear = document.getElementById('btnClearTrucks');
    var fromInput = document.getElementById('fromTimeInput');
    var toInput = document.getElementById('toTimeInput');
    var previewFromMs = document.getElementById('previewFromMs');
    var previewToMs = document.getElementById('previewToMs');
    var previewTrucks = document.getElementById('previewTrucks');

    var fetchForm = document.getElementById('fetchForm');
    var btnSubmit = document.getElementById('btnFetchSubmit');
    var fetchBtnText = document.getElementById('fetchBtnText');
    var fetchSpinner = document.getElementById('fetchSpinner');
    var alertBox = document.getElementById('fetchAlertBox');

    function updatePreview() {
        if (fromInput.value) {
            previewFromMs.textContent = new Date(fromInput.value).getTime();
        }
        if (toInput.value) {
            previewToMs.textContent = new Date(toInput.value).getTime();
        }

        var selected = Array.from(truckSelect.selectedOptions).map(function (o) { return o.value; });
        previewTrucks.textContent = JSON.stringify(selected.length > 0 ? selected.slice(0, 3) : ["SELECT TRUCKS..."]);
    }

    if (btnSelectAll && truckSelect) {
        btnSelectAll.addEventListener('click', function () {
            Array.from(truckSelect.options).forEach(function (opt) { opt.selected = true; });
            updatePreview();
        });
    }

    if (btnClear && truckSelect) {
        btnClear.addEventListener('click', function () {
            Array.from(truckSelect.options).forEach(function (opt) { opt.selected = false; });
            updatePreview();
        });
    }

    if (fromInput) fromInput.addEventListener('change', updatePreview);
    if (toInput) toInput.addEventListener('change', updatePreview);
    if (truckSelect) truckSelect.addEventListener('change', updatePreview);

    updatePreview();

    // AJAX Submission with loading indicator & notifications
    if (fetchForm) {
        fetchForm.addEventListener('submit', function (e) {
            e.preventDefault();

            var selectedTrucks = Array.from(truckSelect.selectedOptions).map(function (o) { return o.value; });
            if (selectedTrucks.length === 0) {
                alertBox.className = 'alert alert-warning';
                alertBox.innerHTML = '<i class="bx bx-error me-1"></i> Please select at least one truck.';
                return;
            }

            btnSubmit.disabled = true;
            btnSubmit.querySelector('i').className = 'bx bx-loader-alt bx-spin fs-5';
            fetchBtnText.textContent = 'Fetching from BlackBuck...';
            fetchSpinner.classList.remove('d-none');
            alertBox.className = 'd-none';

            var formData = {
                truck_numbers: selectedTrucks,
                from_time: fromInput.value,
                to_time: toInput.value
            };

            fetch(fetchForm.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(function (res) { return res.json().then(function (d) { return { ok: res.ok, status: res.status, data: d }; }); })
            .then(function (result) {
                btnSubmit.disabled = false;
                btnSubmit.querySelector('i').className = 'bx bx-download fs-5';
                fetchBtnText.textContent = 'Fetch Transactions';
                fetchSpinner.classList.add('d-none');

                alertBox.classList.remove('d-none');

                if (result.ok && result.data.success) {
                    alertBox.className = 'alert alert-success alert-dismissible fade show';
                    alertBox.innerHTML = `
                        <div class="d-flex align-items-center">
                            <i class="bx bx-check-circle fs-3 me-2"></i>
                            <div>
                                <h6 class="alert-heading mb-1 fw-bold">Transactions Fetched Successfully!</h6>
                                <p class="mb-0 small">
                                    Received: <strong>${result.data.records_received || 0}</strong> records | 
                                    Saved to Database: <strong>${result.data.records_saved || 0}</strong> records.
                                    <a href="{{ route('admin.fastag.index') }}" class="alert-link ms-2">View Transactions &rarr;</a>
                                </p>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    `;
                } else {
                    var errorMsg = result.data.error || (result.data.errors ? Object.values(result.data.errors).flat().join(', ') : 'API request failed');
                    alertBox.className = 'alert alert-danger alert-dismissible fade show';
                    alertBox.innerHTML = `
                        <div class="d-flex align-items-center">
                            <i class="bx bx-error-circle fs-3 me-2"></i>
                            <div>
                                <h6 class="alert-heading mb-1 fw-bold">Fetch Failed</h6>
                                <p class="mb-0 small">${errorMsg}</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    `;
                }
            })
            .catch(function (err) {
                btnSubmit.disabled = false;
                btnSubmit.querySelector('i').className = 'bx bx-download fs-5';
                fetchBtnText.textContent = 'Fetch Transactions';
                fetchSpinner.classList.add('d-none');

                alertBox.classList.remove('d-none');
                alertBox.className = 'alert alert-danger alert-dismissible fade show';
                alertBox.innerHTML = `
                    <i class="bx bx-error-circle me-1"></i> Network error: ${err.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
            });
        });
    }
});
</script>
@endsection
