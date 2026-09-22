@extends('admin.layouts.app')

@section('content')
@php
    $user = auth()->user();
    $isSuperAdmin = $user->isSuperAdmin();
    $isCompanyAdmin = $user->isCompanyAdmin();

    $currentCompanyId = session('current_company_id');
    $companies = $isSuperAdmin
        ? \App\Models\Company::where('status','active')->orderBy('name')->get()
        : $user->accessibleCompanies()->where('status','active')->orderBy('name')->get();
    if ($user->company_id) {
        $ownCompany = \App\Models\Company::find($user->company_id);
        if ($ownCompany && $ownCompany->status === 'active' && !$companies->contains('id',$ownCompany->id)) {
            $companies->prepend($ownCompany);
        }
    }
@endphp

<div class="container-fluid flex-grow-1 container-p-y">

    {{-- Welcome Card --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div>
                            <h5 class="mb-1" style="color: #062E39;">Welcome back, {{ $user->first_name }}!</h5>
                            <p class="mb-0 text-muted" style="font-size:0.9rem;">
                                @if($isSuperAdmin)
                                    Full system access. Manage companies, branches, and users from here.
                                @elseif($isCompanyAdmin)
                                    Managing <strong>{{ $user->company->name ?? 'N/A' }}</strong>
                                @else
                                    Branch: <strong>{{ $user->branch->name ?? 'N/A' }}</strong>
                                @endif
                            </p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('admin.profile') }}" class="btn btn-outline-primary btn-sm">
                                <i class="bx bx-user me-1"></i>Profile
                            </a>
                            @if(!$isSuperAdmin && !$isCompanyAdmin)
                            <a href="{{ route('admin.leaves.create') }}" class="btn btn-warning btn-sm text-white">
                                <i class="bx bx-exit me-1"></i>Apply Leave
                            </a>
                            @endif
                            @canany(['create trips', 'create bulties', 'import trip data'])
                            <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#importTripModal">
                                <i class="bx bx-import me-1"></i>Import
                            </button>
                            <a href="{{ route('admin.transport.trips.download-template') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bx bx-download me-1"></i>Template
                            </a>
                            @endcanany
                            @can('view bulties')
                            <a href="{{ route('admin.transport.bulties.index') }}" class="btn btn-primary btn-sm">
                                <i class="bx bx-receipt me-1"></i>Bilties
                            </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Company & Year Switcher --}}
    <div class="row mb-4 g-3">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3" style="padding:0.75rem 1rem;">
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded" style="background:rgba(6,46,57,0.1);color:#062E39;">
                            <i class="bx bx-building"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1">
                        <small class="text-muted fw-semibold d-block mb-1">Company</small>
                        <form method="POST" action="{{ route('admin.switch-company') }}">
                            @csrf
                            <select name="company_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                @if($isSuperAdmin)
                                    <option value="all" {{ $currentCompanyId === 'all' ? 'selected' : '' }}>All Companies</option>
                                @endif
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ $currentCompanyId == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3" style="padding:0.75rem 1rem;">
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded" style="background:rgba(253,85,35,0.1);color:#FD5523;">
                            <i class="bx bx-calendar"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1">
                        <small class="text-muted fw-semibold d-block mb-1">Financial Year</small>
                        <form method="POST" action="{{ route('admin.switch-year') }}">
                            @csrf
                            <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="all" {{ session('current_year') === 'all' ? 'selected' : '' }}>All Years</option>
                                @for($y = 2025; $y <= date('Y'); $y++)
                                    <option value="{{ $y }}" {{ (session('current_year') ?: date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3" style="padding:0.75rem 1rem;">
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded" style="background:rgba(16,185,129,0.1);color:#10b981;">
                            <i class="bx bx-check-circle"></i>
                        </span>
                    </div>
                    <div>
                        <small class="text-muted fw-semibold d-block mb-1">Status</small>
                        <span style="font-size:0.85rem;font-weight:600;color:#10b981;">All Systems Operational</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($isSuperAdmin && isset($kpi['expiring_documents']) && $kpi['expiring_documents']->count() > 0)
    @php
        $expiredCount = $kpi['expiring_documents']->filter(fn($d) => $d['days_left'] <= 0)->count();
        $expiring1Day = $kpi['expiring_documents']->filter(fn($d) => $d['days_left'] == 1)->count();
        $expiring5Days = $kpi['expiring_documents']->filter(fn($d) => $d['days_left'] >= 1 && $d['days_left'] <= 5)->count();
    @endphp
    <div class="mb-3">
        <h5 class="fw-bold mb-0"><i class="bx bx-file me-1"></i>Vehicle Expiring Document Count</h5>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border border-danger h-100">
                <div class="card-body text-center">
                    <h3 class="mb-1 text-danger fw-bold">{{ $expiredCount }}</h3>
                    <span class="text-muted small">Expired</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border border-warning h-100">
                <div class="card-body text-center">
                    <h3 class="mb-1 text-warning fw-bold">{{ $expiring1Day }}</h3>
                    <span class="text-muted small">Expiring in 1 Day</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border border-info h-100">
                <div class="card-body text-center">
                    <h3 class="mb-1 text-info fw-bold">{{ $expiring5Days }}</h3>
                    <span class="text-muted small">Expiring in 5 Days</span>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(isset($kpi['monthly_pnl']))
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0"><i class="bx bx-trending-up me-1"></i>Profit & Loss Trend</h5>
                    <a href="{{ route('admin.reports.profit-loss') }}" class="btn btn-sm btn-outline-primary">View Full Report</a>
                </div>
                <div class="card-body">
                    <div id="profitLossChart"></div>
                </div>
            </div>
        </div>
    </div>
    @endif

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
                    <div class="mt-3 p-2 rounded bg-light border">
                        <div class="small fw-bold text-secondary mb-1"><i class="bx bx-check-double text-success me-1"></i> Supported Details Include:</div>
                        <div class="small text-muted" style="font-size: 0.78rem; line-height: 1.4;">
                            LR Details &bull; Consignor/Consignee &bull; Vehicle & Driver &bull; Items/Freight &bull; GRN Details (Posting Date, Mat Doc, Gate Entry No, Supplier, Supplier No, Challan No, Gate Out Date, PO Item, Transporter Code/Name, Material Name, Challan Qty, Final Wgt) &bull; FastTag &bull; Fuel &bull; AdBlue &bull; Advance
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
@if(isset($kpi['monthly_pnl']))
document.addEventListener('DOMContentLoaded', function() {
    const pnlData = @json($kpi['monthly_pnl']);
    const pnlOptions = {
        series: [
            { name: 'Income', type: 'bar', data: pnlData.income },
            { name: 'Expenses', type: 'bar', data: pnlData.expense },
            { name: 'Profit', type: 'line', data: pnlData.income.map((inc, i) => inc - pnlData.expense[i]) }
        ],
        chart: { height: 350, type: 'line', stacked: false, toolbar: { show: true } },
        colors: ['#10b981', '#ef4444', '#062E39'],
        stroke: { width: [0, 0, 3], curve: 'smooth' },
        plotOptions: { bar: { columnWidth: '40%', endingShape: 'rounded' } },
        fill: { opacity: [0.85, 0.85, 1], gradient: { inverseColors: false, shade: 'light', type: 'vertical', opacityFrom: 0.85, opacityTo: 0.55 } },
        labels: pnlData.months,
        markers: { size: 0 },
        yaxis: {
            labels: { formatter: v => '₹ ' + Math.round(v).toLocaleString('en-IN') }
        },
        tooltip: {
            shared: true,
            intersect: false,
            y: { formatter: v => '₹ ' + Math.round(v).toLocaleString('en-IN') }
        },
        legend: { position: 'top', horizontalAlign: 'center' }
    };

    const pnlChart = new ApexCharts(document.querySelector('#profitLossChart'), pnlOptions);
    pnlChart.render();
});
@endif
</script>
@endsection
