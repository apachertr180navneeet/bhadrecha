@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Staff Performance Report</h4>
            <small class="text-muted">Track completed services, sales share, and attendance per staff member</small>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-header border-bottom bg-transparent py-3">
                    <h5 class="card-title mb-0 fw-bold" style="color: #4b5563;"><i class="bx bx-filter-alt me-2 text-primary"></i>Filter Report</h5>
                </div>
                <div class="card-body mt-3">
                    <form method="GET" action="{{ route('admin.reports.staff') }}" class="row g-3 align-items-end">
                        <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                            <label class="form-label text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600;">Date From</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                            <label class="form-label text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600;">Date To</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-12 col-sm-6 col-md-4 col-xl-3">
                            <label class="form-label text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600;">Store</label>
                            <select name="store_id" id="staffReportStore" class="form-select" {{ (auth()->user()->store_id && !($canViewAllStores ?? (auth()->user()->hasRole('Admin') || auth()->user()->can('view stores')))) ? 'disabled' : '' }}>
                                <option value="">All Stores</option>
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}" {{ (string)($selectedStoreId ?? request('store_id')) === (string)$store->id ? 'selected' : '' }}>{{ $store->store_name ?? $store->name }}</option>
                                @endforeach
                            </select>
                            @if(auth()->user()->store_id && !($canViewAllStores ?? (auth()->user()->hasRole('Admin') || auth()->user()->can('view stores'))))
                                <input type="hidden" name="store_id" value="{{ auth()->user()->store_id }}">
                            @endif
                        </div>
                        <div class="col-12 col-sm-6 col-md-4 col-xl-3">
                            <label class="form-label text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600;">Branch</label>
                            <select name="branch_id" id="staffReportBranch" class="form-select" {{ (auth()->user()->branch_id && !($canViewAllBranches ?? (auth()->user()->hasRole('Admin') || auth()->user()->can('view branches')))) ? 'disabled' : '' }}>
                                <option value="">All Branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" data-store-id="{{ $branch->store_id }}" {{ (string)($selectedBranchId ?? request('branch_id')) === (string)$branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                            @if(auth()->user()->branch_id && !($canViewAllBranches ?? (auth()->user()->hasRole('Admin') || auth()->user()->can('view branches'))))
                                <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
                            @endif
                        </div>
                        <div class="col-12 col-sm-6 col-md-4 col-xl-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1"><i class="bx bx-filter-alt me-1"></i>Filter</button>
                            <a href="{{ route('admin.reports.staff') }}" class="btn btn-outline-secondary flex-grow-1"><i class="bx bx-reset me-1"></i>Reset</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px;">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-md me-3 bg-label-primary p-2 rounded">
                        <i class="bx bx-user fs-3 text-primary"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase d-block fw-semibold" style="font-size: 0.7rem;">Total Staff Members</small>
                        <h4 class="mb-0 fw-bold">{{ number_format($staffs->count()) }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px;">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-md me-3 bg-label-info p-2 rounded">
                        <i class="bx bx-cut fs-3 text-info"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase d-block fw-semibold" style="font-size: 0.7rem;">Services Delivered</small>
                        <h4 class="mb-0 fw-bold">{{ number_format($staffs->sum('completed_services_count')) }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px;">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-md me-3 bg-label-success p-2 rounded">
                        <i class="bx bx-rupee fs-3 text-success"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase d-block fw-semibold" style="font-size: 0.7rem;">Total Sales Share</small>
                        <h4 class="mb-0 fw-bold text-success">₹{{ number_format($staffs->sum('total_sales_amount'), 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px;">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-md me-3 bg-label-warning p-2 rounded">
                        <i class="bx bx-wallet fs-3 text-warning"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase d-block fw-semibold" style="font-size: 0.7rem;">Total Advances</small>
                        <h4 class="mb-0 fw-bold text-dark">₹{{ number_format($totalAdvanceAll ?? $staffs->sum('total_advance_amount'), 2) }}</h4>
                        <div class="mt-1 d-flex gap-1 flex-wrap" style="font-size: 0.68rem;">
                            <span class="badge bg-label-warning py-0 px-1">Pending: ₹{{ number_format($totalPendingAdvanceAll ?? $staffs->sum('pending_advance_amount'), 0) }}</span>
                            <span class="badge bg-label-success py-0 px-1">Deducted: ₹{{ number_format($totalDeductedAdvanceAll ?? $staffs->sum('deducted_advance_amount'), 0) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Staff Performance Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-header border-bottom bg-transparent py-3">
                    <h5 class="card-title mb-0 fw-bold" style="color: #4b5563;"><i class="bx bx-id-card me-2 text-primary"></i>Staff Sales & Performance</h5>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb; padding-left: 1.5rem;">Staff Name</th>
                                <th class="text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb;">Store</th>
                                <th class="text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb;">Position</th>
                                <th class="text-uppercase text-muted text-center" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb;">Completed Services</th>
                                <th class="text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb;">Sales Amount Share (₹)</th>
                                <th class="text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb;">Employee Advances (₹)</th>
                                <th class="text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb; padding-right: 1.5rem;">Attendance %</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($staffs as $staff)
                                <tr>
                                    <td style="padding-left: 1.5rem;">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-3">
                                                <span class="avatar-initial rounded-circle bg-label-primary text-primary fw-bold">{{ strtoupper(substr($staff->full_name, 0, 1)) }}</span>
                                            </div>
                                            <div>
                                                <span class="fw-semibold text-body d-block">{{ $staff->full_name }}</span>
                                                @if($staff->phone)
                                                    <small class="text-muted"><i class="bx bx-phone me-1"></i>{{ $staff->phone }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-secondary">{{ $staff->store->store_name ?? $staff->store->name ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-info px-3 py-2 rounded-pill">{{ $staff->position ?? 'N/A' }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-label-primary px-3 py-2 rounded-pill fs-6">{{ $staff->completed_services_count ?? 0 }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-success fs-6">₹{{ number_format($staff->total_sales_amount ?? 0, 2) }}</span>
                                    </td>
                                    <td>
                                        <div>
                                            <span class="fw-bold fs-6 {{ ($staff->total_advance_amount ?? 0) > 0 ? 'text-dark' : 'text-muted' }}">
                                                ₹{{ number_format($staff->total_advance_amount ?? 0, 2) }}
                                            </span>
                                            @if(($staff->total_advance_amount ?? 0) > 0)
                                                <div class="d-flex gap-1 mt-1 flex-wrap">
                                                    @if(($staff->pending_advance_amount ?? 0) > 0)
                                                        <span class="badge bg-label-warning py-0 px-1" style="font-size: 0.68rem;" title="Pending to deduct">
                                                            Pending: ₹{{ number_format($staff->pending_advance_amount, 2) }}
                                                        </span>
                                                    @endif
                                                    @if(($staff->deducted_advance_amount ?? 0) > 0)
                                                        <span class="badge bg-label-success py-0 px-1" style="font-size: 0.68rem;" title="Deducted in payroll">
                                                            Deducted: ₹{{ number_format($staff->deducted_advance_amount, 2) }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td style="padding-right: 1.5rem;">
                                        <div class="d-flex flex-column">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="small fw-semibold">{{ number_format($staff->attendance_percentage ?? 0, 1) }}%</span>
                                            </div>
                                            <div class="progress w-100" style="height: 6px; border-radius: 4px;">
                                                <div class="progress-bar {{ ($staff->attendance_percentage ?? 0) >= 80 ? 'bg-success' : (($staff->attendance_percentage ?? 0) >= 50 ? 'bg-warning' : 'bg-danger') }}" role="progressbar" style="width: {{ $staff->attendance_percentage ?? 0 }}%;" aria-valuenow="{{ $staff->attendance_percentage ?? 0 }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="bx bx-id-card fs-1 text-light mb-3"></i>
                                        <p class="mb-0">No staff performance records found.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        if (window.setupDependentBranchSelect) {
            window.setupDependentBranchSelect('#staffReportStore', '#staffReportBranch');
        }
    });
</script>
@endsection
