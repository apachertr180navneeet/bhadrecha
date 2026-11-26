@extends('admin.layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light">System /</span> Database Backup
            </h4>
            <p class="text-muted mb-0 small">Create, download, and manage complete database and project backups.</p>
        </div>
        <div>
            <a href="{{ route('admin.database-backup.download') }}" id="btnQuickBackup" class="btn btn-primary d-inline-flex align-items-center gap-2 shadow-sm">
                <i class="bx bx-download fs-5"></i>
                <span id="quickBackupText">Download SQL Backup</span>
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

    @if(!$isConnected)
        <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
            <i class="bx bx-alarm-exclamation fs-3 me-3"></i>
            <div>
                <h6 class="alert-heading mb-1 fw-bold">Database Connection Warning</h6>
                <p class="mb-0 small">
                    Unable to connect to MySQL database <code>{{ $databaseName }}</code> on <code>{{ $dbHost }}:{{ $dbPort }}</code>.
                    @if($connectionError)
                        <br><span class="text-muted">Details: {{ $connectionError }}</span>
                    @endif
                </p>
            </div>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <!-- DB Status Card -->
        <div class="col-md-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="bx bx-data fs-4"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Database</small>
                            <span class="fw-semibold text-truncate d-inline-block" style="max-width: 150px;" title="{{ $databaseName }}">
                                {{ $databaseName }}
                            </span>
                        </div>
                    </div>
                    <div class="mt-2">
                        @if($isConnected)
                            <span class="badge bg-label-success">
                                <i class="bx bx-check-circle me-1"></i> Connected
                            </span>
                        @else
                            <span class="badge bg-label-danger">
                                <i class="bx bx-x-circle me-1"></i> Disconnected
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Tables -->
        <div class="col-md-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="bx bx-table fs-4"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Total Tables</small>
                            <h5 class="card-title mb-0">{{ count($tables) }}</h5>
                        </div>
                    </div>
                    <small class="text-muted">Host: <code>{{ $dbHost }}:{{ $dbPort }}</code></small>
                </div>
            </div>
        </div>

        <!-- Total Records -->
        <div class="col-md-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="bx bx-layer fs-4"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Est. Total Rows</small>
                            <h5 class="card-title mb-0">{{ number_format($totalRows) }}</h5>
                        </div>
                    </div>
                    <small class="text-muted">Across all schema tables</small>
                </div>
            </div>
        </div>

        <!-- Estimated Size -->
        <div class="col-md-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="bx bx-hdd fs-4"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Estimated DB Size</small>
                            <h5 class="card-title mb-0">{{ $totalSizeMb }} MB</h5>
                        </div>
                    </div>
                    <small class="text-muted">Data & index length</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Download Card & Guidelines -->
    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                        <i class="bx bx-cloud-download text-primary fs-4"></i>
                        <span>Generate & Download SQL Dump</span>
                    </h5>
                    <span class="badge bg-label-primary">Live Dump</span>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        Export the full database structure and records into a single standard <code>.sql</code> file. 
                        The export automatically includes:
                    </p>
                    <ul class="list-unstyled mb-4">
                        <li class="d-flex align-items-center mb-2">
                            <i class="bx bx-check text-success me-2 fs-5"></i>
                            <span><code>DROP TABLE IF EXISTS</code> & full <code>CREATE TABLE</code> schemas</span>
                        </li>
                        <li class="d-flex align-items-center mb-2">
                            <i class="bx bx-check text-success me-2 fs-5"></i>
                            <span>Batched <code>INSERT</code> statements with sanitized column values</span>
                        </li>
                        <li class="d-flex align-items-center mb-2">
                            <i class="bx bx-check text-success me-2 fs-5"></i>
                            <span>Disabled foreign key checks during import to prevent constraint failures</span>
                        </li>
                        <li class="d-flex align-items-center mb-2">
                            <i class="bx bx-check text-success me-2 fs-5"></i>
                            <span>Single transactional wrapper (<code>START TRANSACTION / COMMIT</code>)</span>
                        </li>
                    </ul>

                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <a href="{{ route('admin.database-backup.download') }}" id="btnMainDownload" class="btn btn-primary px-4 py-2 d-inline-flex align-items-center gap-2">
                            <i class="bx bx-download fs-5"></i>
                            <span id="mainDownloadText">Download .SQL Backup Now</span>
                        </a>
                        <span class="text-muted small">
                            Format: <code>{{ $databaseName }}_backup_YYYY-MM-DD_HH-mm-ss.sql</code>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                        <i class="bx bx-info-circle text-info fs-4"></i>
                        <span>Restore Instructions</span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="fw-semibold mb-1 small text-uppercase text-muted">Option A: Via phpMyAdmin / GUI</h6>
                        <ol class="ps-3 mb-0 small text-muted">
                            <li>Open phpMyAdmin or your MySQL GUI tool.</li>
                            <li>Select database <code>{{ $databaseName }}</code>.</li>
                            <li>Navigate to the <strong>Import</strong> tab.</li>
                            <li>Choose the downloaded <code>.sql</code> file and click <strong>Go</strong>.</li>
                        </ol>
                    </div>

                    <div class="border-top pt-3">
                        <h6 class="fw-semibold mb-1 small text-uppercase text-muted">Option B: Via MySQL CLI</h6>
                        <pre class="bg-light p-2 rounded small text-dark mb-0"><code>mysql -u {{ $dbUsername }} -p {{ $databaseName }} &lt; backup.sql</code></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stored Project & Server Backups -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                    <i class="bx bx-archive text-warning fs-4"></i>
                    <span>Stored Project & Database Archives</span>
                </h5>
                <small class="text-muted">Files present in <code>/backups</code> directory on the server</small>
            </div>
            <span class="badge bg-label-secondary">{{ count($storedBackups) }} Available</span>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>File Name</th>
                        <th>Type</th>
                        <th>Size</th>
                        <th>Created Date</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($storedBackups as $backup)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="bx {{ $backup['extension'] === 'zip' ? 'bxs-file-archive text-warning' : 'bxs-data text-primary' }} fs-3 me-2"></i>
                                    <span class="fw-semibold font-monospace small">{{ $backup['name'] }}</span>
                                </div>
                            </td>
                            <td>
                                @if($backup['extension'] === 'zip')
                                    <span class="badge bg-label-warning text-uppercase">Zip Archive</span>
                                @elseif($backup['extension'] === 'sql')
                                    <span class="badge bg-label-primary text-uppercase">SQL Dump</span>
                                @else
                                    <span class="badge bg-label-secondary text-uppercase">{{ $backup['extension'] }}</span>
                                @endif
                            </td>
                            <td>{{ $backup['size'] }} MB</td>
                            <td>{{ date('M d, Y h:i A', $backup['created_at']) }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.database-backup.download-file', $backup['name']) }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                                    <i class="bx bx-download"></i> Download
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="bx bx-folder-open fs-2 d-block mb-1 opacity-50"></i>
                                No stored archive files found in <code>/backups</code>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($isConnected && count($tables) > 0)
        <!-- Tables List Accordion / Card -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                        <i class="bx bx-list-ul text-secondary fs-4"></i>
                        <span>Database Tables Detail</span>
                    </h5>
                    <small class="text-muted">Total of {{ count($tables) }} tables in <code>{{ $databaseName }}</code></small>
                </div>
                <div>
                    <input type="text" id="tableFilterInput" class="form-control form-control-sm" placeholder="Filter tables..." style="max-width: 200px;">
                </div>
            </div>
            <div class="table-responsive text-nowrap" style="max-height: 420px; overflow-y: auto;">
                <table class="table table-sm table-hover mb-0" id="tablesListTable">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th>#</th>
                            <th>Table Name</th>
                            <th>Engine</th>
                            <th>Collation</th>
                            <th class="text-end">Rows</th>
                            <th class="text-end">Size (MB)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tables as $index => $table)
                            <tr>
                                <td><small class="text-muted">{{ $index + 1 }}</small></td>
                                <td>
                                    <code class="text-primary fw-semibold">{{ $table['name'] }}</code>
                                </td>
                                <td><span class="badge bg-label-secondary small">{{ $table['engine'] }}</span></td>
                                <td><small class="text-muted">{{ $table['collation'] }}</small></td>
                                <td class="text-end font-monospace">{{ number_format($table['rows']) }}</td>
                                <td class="text-end font-monospace">{{ $table['size_mb'] }} MB</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Handling download button spinner
    function setupDownloadSpinner(btnId, textId, originalText) {
        var btn = document.getElementById(btnId);
        var txt = document.getElementById(textId);
        if (!btn || !txt) return;

        btn.addEventListener('click', function () {
            var icon = btn.querySelector('i');
            if (icon) {
                icon.className = 'bx bx-loader-alt bx-spin fs-5';
            }
            txt.textContent = 'Generating & Downloading...';

            setTimeout(function () {
                if (icon) {
                    icon.className = 'bx bx-download fs-5';
                }
                txt.textContent = originalText;
            }, 6000);
        });
    }

    setupDownloadSpinner('btnQuickBackup', 'quickBackupText', 'Download SQL Backup');
    setupDownloadSpinner('btnMainDownload', 'mainDownloadText', 'Download .SQL Backup Now');

    // Table filter search
    var filterInput = document.getElementById('tableFilterInput');
    var tablesTable = document.getElementById('tablesListTable');
    if (filterInput && tablesTable) {
        filterInput.addEventListener('input', function () {
            var val = this.value.trim().toLowerCase();
            var rows = tablesTable.querySelectorAll('tbody tr');
            rows.forEach(function (row) {
                var tableName = row.querySelector('code') ? row.querySelector('code').textContent.toLowerCase() : '';
                if (val === '' || tableName.includes(val)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});
</script>
@endsection
