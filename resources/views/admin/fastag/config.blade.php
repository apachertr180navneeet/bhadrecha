@extends('admin.layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light">FASTag /</span> API Configuration
            </h4>
            <p class="text-muted mb-0 small">BlackBuck FASTag API connection parameters, authentication health check, and diagnostics.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" id="btnTestConn" class="btn btn-primary d-inline-flex align-items-center gap-2 shadow-sm">
                <i class="bx bx-broadcast fs-5"></i>
                <span id="testConnText">Test API Connection</span>
            </button>
            <a href="{{ route('admin.fastag.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                <i class="bx bx-arrow-back"></i> Transactions
            </a>
        </div>
    </div>

    <!-- Live Test Connection Alert Box (Dynamic) -->
    <div id="testConnResultBox" class="d-none mb-4"></div>

    <!-- Health Check Status Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded {{ $validation['is_valid'] ? 'bg-label-success' : 'bg-label-warning' }}">
                                <i class="bx {{ $validation['is_valid'] ? 'bx-check-shield' : 'bx-alarm-exclamation' }} fs-4"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Config Status</small>
                            <h6 class="mb-0">{{ $validation['is_valid'] ? 'Configured' : 'Incomplete' }}</h6>
                        </div>
                    </div>
                    <small class="text-muted">
                        {{ $validation['is_valid'] ? 'All required credentials set in .env' : count($validation['missing']) . ' required settings missing' }}
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="bx bx-key fs-4"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Auth Scheme</small>
                            <h6 class="mb-0 text-uppercase">{{ $validation['auth_type'] ?? config('blackbuck.auth_type', 'Token') }}</h6>
                        </div>
                    </div>
                    <small class="text-muted"><code>BLACKBUCK_AUTH_TYPE</code></small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="bx bx-link-external fs-4"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Transaction Endpoint</small>
                            <span class="small font-monospace text-primary text-truncate d-inline-block" style="max-width: 150px;" title="{{ $validation['transaction_endpoint'] }}">
                                {{ $validation['transaction_endpoint'] }}
                            </span>
                        </div>
                    </div>
                    <small class="text-muted">Configured endpoint path</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-dark">
                                <i class="bx bx-time fs-4"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Last Sync Event</small>
                            <h6 class="mb-0">{{ $lastSync ? $lastSync->created_at->diffForHumans() : 'No runs yet' }}</h6>
                        </div>
                    </div>
                    <small class="text-muted">{{ $lastSync ? 'Status: ' . $lastSync->sync_status : 'Pending initial run' }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Parameters & Pending Specifications -->
    <div class="row g-4 mb-4">
        <!-- Configuration Parameters Table -->
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                        <i class="bx bx-slider-alt text-primary fs-4"></i>
                        <span>Active Parameters (.env)</span>
                    </h5>
                    <span class="badge bg-label-secondary">Read-only</span>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Setting / Variable</th>
                                <th>Value / Status</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>BLACKBUCK_BASE_URL</code></td>
                                <td><span class="font-monospace small">{{ config('blackbuck.base_url') }}</span></td>
                                <td><small class="text-muted">Root API host</small></td>
                            </tr>
                            <tr>
                                <td><code>BLACKBUCK_TRANSACTION_ENDPOINT</code></td>
                                <td><span class="font-monospace small">{{ config('blackbuck.transaction_endpoint') }}</span></td>
                                <td><small class="text-muted">Transactions path</small></td>
                            </tr>
                            <tr>
                                <td><code>BLACKBUCK_AUTH_TYPE</code></td>
                                <td><span class="badge bg-label-info text-uppercase">{{ config('blackbuck.auth_type', 'Token') }}</span></td>
                                <td><small class="text-muted">Header: <code>Authorization: Token &lt;token&gt;</code></small></td>
                            </tr>
                            <tr>
                                <td><code>BLACKBUCK_TOKEN</code></td>
                                <td>
                                    @if(config('blackbuck.token') && config('blackbuck.token') !== 'YOUR_TEST_TOKEN')
                                        <span class="badge bg-label-success">CONFIGURED ({{ Str::mask(config('blackbuck.token'), '*', 4, -4) }})</span>
                                    @else
                                        <span class="badge bg-label-danger">NOT CONFIGURED / PLACEHOLDER</span>
                                    @endif
                                </td>
                                <td><small class="text-muted">API Auth Token</small></td>
                            </tr>
                            <tr>
                                <td><code>BLACKBUCK_PHONE_NUMBER</code></td>
                                <td>
                                    <span class="badge bg-label-primary font-monospace">{{ config('blackbuck.phone_number', '9829894925') }}</span>
                                </td>
                                <td><small class="text-muted">BlackBuck Account Phone</small></td>
                            </tr>
                            <tr>
                                <td><code>BLACKBUCK_TIMEOUT</code></td>
                                <td><span class="font-monospace small">{{ config('blackbuck.timeout', 60) }}s</span></td>
                                <td><small class="text-muted">HTTP Client Timeout</small></td>
                            </tr>
                            <tr>
                                <td><code>BLACKBUCK_RETRY_COUNT</code></td>
                                <td><span class="font-monospace small">{{ config('blackbuck.retry_count', 3) }}</span></td>
                                <td><small class="text-muted">Automatic Retry Attempts</small></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Specifications & Dependencies Checklist -->
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                        <i class="bx bx-list-check text-success fs-4"></i>
                        <span>Integration Checklist & Dependencies</span>
                    </h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">
                        To enable live production communication with BlackBuck FASTag servers, ensure the following specifications from BlackBuck have been received and populated:
                    </p>

                    <div class="list-group list-group-flush mb-3">
                        <div class="list-group-item px-0 py-2 d-flex align-items-start gap-2">
                            <i class="bx {{ config('blackbuck.base_url') ? 'bx-check-circle text-success' : 'bx-circle text-muted' }} fs-5 mt-1"></i>
                            <div>
                                <strong class="small d-block">1. BlackBuck API Base URL & Gateway IP</strong>
                                <small class="text-muted">Production endpoint (e.g., <code>https://api.blackbuck.com</code>) or staging sandbox.</small>
                            </div>
                        </div>

                        <div class="list-group-item px-0 py-2 d-flex align-items-start gap-2">
                            <i class="bx {{ config('blackbuck.api_key') || config('blackbuck.username') ? 'bx-check-circle text-success' : 'bx-circle text-warning' }} fs-5 mt-1"></i>
                            <div>
                                <strong class="small d-block">2. API Credentials (Key / Secret / User)</strong>
                                <small class="text-muted">Merchant credentials issued by your BlackBuck relationship manager.</small>
                            </div>
                        </div>

                        <div class="list-group-item px-0 py-2 d-flex align-items-start gap-2">
                            <i class="bx bx-check-circle text-success fs-5 mt-1"></i>
                            <div>
                                <strong class="small d-block">3. Automated Daily Scheduler</strong>
                                <small class="text-muted">Registered in <code>Kernel.php</code> to run daily at 02:00 AM with overlap protection.</small>
                            </div>
                        </div>

                        <div class="list-group-item px-0 py-2 d-flex align-items-start gap-2">
                            <i class="bx bx-check-circle text-success fs-5 mt-1"></i>
                            <div>
                                <strong class="small d-block">4. Server IP Whitelisting</strong>
                                <small class="text-muted">If BlackBuck enforces IP restrictions, provide your production web server IP to BlackBuck support.</small>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-light border small text-muted mb-0">
                        <i class="bx bx-lock-alt me-1"></i> Credentials are never exposed in JavaScript, logs, or API responses. Update them inside your server <code>.env</code> file.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var btnTestConn = document.getElementById('btnTestConn');
    var testConnText = document.getElementById('testConnText');
    var testConnResultBox = document.getElementById('testConnResultBox');

    if (btnTestConn && testConnResultBox) {
        btnTestConn.addEventListener('click', function () {
            btnTestConn.disabled = true;
            btnTestConn.querySelector('i').className = 'bx bx-loader-alt bx-spin fs-5';
            testConnText.textContent = 'Testing BlackBuck API Connection...';
            testConnResultBox.className = 'd-none';

            fetch("{{ route('admin.fastag.test-connection') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json'
                }
            })
            .then(function (res) { return res.json().then(function (d) { return { ok: res.ok, status: res.status, data: d }; }); })
            .then(function (result) {
                btnTestConn.disabled = false;
                btnTestConn.querySelector('i').className = 'bx bx-broadcast fs-5';
                testConnText.textContent = 'Test API Connection';

                testConnResultBox.classList.remove('d-none');
                if (result.ok && result.data.success) {
                    testConnResultBox.innerHTML = `
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bx bx-check-circle me-1"></i> <strong>Connection Succeeded!</strong> ${result.data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                } else {
                    var errorMsg = result.data.message || 'API connection returned an error.';
                    testConnResultBox.innerHTML = `
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <i class="bx bx-alarm-exclamation me-1"></i> <strong>Connection Test Notice:</strong> ${errorMsg}
                            <div class="small text-muted mt-1">Status code: ${result.status} | Verify your BlackBuck credentials and endpoint in <code>.env</code>.</div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                }
            })
            .catch(function (err) {
                btnTestConn.disabled = false;
                btnTestConn.querySelector('i').className = 'bx bx-broadcast fs-5';
                testConnText.textContent = 'Test API Connection';

                testConnResultBox.classList.remove('d-none');
                testConnResultBox.innerHTML = `
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bx bx-error-circle me-1"></i> <strong>Network Error:</strong> ${err.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
            });
        });
    }
});
</script>
@endsection
