@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <!-- Header & Filter Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-header d-flex justify-content-between align-items-center bg-transparent border-bottom pb-3">
                    <div>
                        <h4 class="fw-bold mb-0 text-primary">Package Used Summary Report</h4>
                        <small class="text-muted">Track package consumption according to appointments from package taken date to expiry date</small>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <form method="GET" action="{{ route('admin.reports.package-usage') }}" class="row g-3 align-items-end">
                        <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                            <label class="form-label text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600;">Date From</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from', $dateFrom) }}">
                        </div>
                        <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                            <label class="form-label text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600;">Date To</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to', $dateTo) }}">
                        </div>
                        <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                            <label class="form-label text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600;">Customer</label>
                            <select name="customer_id" class="form-select">
                                <option value="">All Customers</option>
                                @foreach($customers as $cust)
                                    <option value="{{ $cust->id }}" {{ request('customer_id') == $cust->id ? 'selected' : '' }}>{{ $cust->name }} ({{ $cust->mobile }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                            <label class="form-label text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600;">Store</label>
                            <select name="store_id" id="pkgReportStore" class="form-select" {{ (auth()->user()->store_id && !($canViewAllStores ?? (auth()->user()->hasRole('Admin') || auth()->user()->can('view stores')))) ? 'disabled' : '' }}>
                                <option value="">All Stores</option>
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}" {{ (string)($selectedStoreId ?? request('store_id')) === (string)$store->id ? 'selected' : '' }}>{{ $store->store_name ?? $store->name }}</option>
                                @endforeach
                            </select>
                            @if(auth()->user()->store_id && !($canViewAllStores ?? (auth()->user()->hasRole('Admin') || auth()->user()->can('view stores'))))
                                <input type="hidden" name="store_id" value="{{ auth()->user()->store_id }}">
                            @endif
                        </div>
                        <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                            <label class="form-label text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600;">Branch</label>
                            <select name="branch_id" id="pkgReportBranch" class="form-select" {{ (auth()->user()->branch_id && !($canViewAllBranches ?? (auth()->user()->hasRole('Admin') || auth()->user()->can('view branches')))) ? 'disabled' : '' }}>
                                <option value="">All Branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" data-store-id="{{ $branch->store_id }}" {{ (string)($selectedBranchId ?? request('branch_id')) === (string)$branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                            @if(auth()->user()->branch_id && !($canViewAllBranches ?? (auth()->user()->hasRole('Admin') || auth()->user()->can('view branches'))))
                                <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
                            @endif
                        </div>
                        <div class="col-12 col-md-4 col-xl-2">
                            <label class="form-label text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600;">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Fully Utilized" {{ request('status') == 'Fully Utilized' ? 'selected' : '' }}>Fully Utilized</option>
                                <option value="Expired" {{ request('status') == 'Expired' ? 'selected' : '' }}>Expired</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4 col-xl-12 d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-primary px-4" title="Filter"><i class="bx bx-filter-alt me-1"></i>Filter</button>
                            <a href="{{ route('admin.reports.package-usage') }}" class="btn btn-outline-secondary px-4" title="Reset"><i class="bx bx-reset me-1"></i>Reset</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card h-100 shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="d-block text-muted mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px; text-transform: uppercase;">Total Packages</span>
                            <h2 class="mb-0 text-primary fw-bold">{{ number_format($totalPackages) }}</h2>
                        </div>
                        <div class="stat-card-icon bg-label-primary rounded-circle" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                            <i class="bx bx-package fs-3 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="d-block text-muted mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px; text-transform: uppercase;">Services Allocated</span>
                            <h2 class="mb-0 text-info fw-bold">{{ number_format($totalAllocatedQty) }}</h2>
                        </div>
                        <div class="stat-card-icon bg-label-info rounded-circle" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                            <i class="bx bx-list-check fs-3 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="d-block text-muted mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px; text-transform: uppercase;">Services Used</span>
                            <h2 class="mb-0 text-success fw-bold">{{ number_format($totalUsedQty) }}</h2>
                        </div>
                        <div class="stat-card-icon bg-label-success rounded-circle" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                            <i class="bx bx-check-double fs-3 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="d-block text-muted mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px; text-transform: uppercase;">Services Remaining</span>
                            <h2 class="mb-0 text-warning fw-bold">{{ number_format($totalRemainingQty) }}</h2>
                        </div>
                        <div class="stat-card-icon bg-label-warning rounded-circle" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                            <i class="bx bx-time-five fs-3 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Package Usage Data Table -->
    <div class="card shadow-sm border-0" style="border-radius: 12px;">
        <div class="card-body">
            <div class="table-responsive text-nowrap">
                <table class="table table-hover align-middle" id="packageUsageTable">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 100px;">Actions</th>
                            <th>Package ID</th>
                            <th>Customer</th>
                            <th>Taken Date</th>
                            <th>End Date</th>
                            <th>Services Included</th>
                            <th class="text-center">Allocated Qty</th>
                            <th class="text-center">Used Qty</th>
                            <th class="text-center">Remaining Qty</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($packages as $package)
                        @php
                            $u = $package->usage_data;
                            $allocated = $u['total_allocated_qty'] ?? $package->qty;
                            $used = $u['total_used_qty'] ?? 0;
                            $remaining = $u['total_remaining_qty'] ?? max(0, $allocated - $used);
                            $st = $package->calculated_status ?? 'Active';
                        @endphp
                        <tr>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-primary view-usage-details" data-id="{{ $package->id }}">
                                    <i class="bx bx-show me-1"></i> Details
                                </button>
                            </td>
                            <td class="fw-bold text-primary">#{{ $package->id }}</td>
                            <td>
                                <span class="fw-semibold text-dark">{{ $package->customer->name ?? 'N/A' }}</span>
                                @if(isset($package->customer->mobile))
                                    <br><small class="text-muted">{{ $package->customer->mobile }}</small>
                                @endif
                            </td>
                            <td>
                                <i class="bx bx-calendar text-muted me-1"></i>
                                {{ $package->start_date ? \Carbon\Carbon::parse($package->start_date)->format('d-m-Y') : '-' }}
                            </td>
                            <td>
                                <i class="bx bx-calendar-event text-muted me-1"></i>
                                {{ $package->end_date ? \Carbon\Carbon::parse($package->end_date)->format('d-m-Y') : '-' }}
                            </td>
                            <td>
                                @if(isset($u['services_breakdown']) && count($u['services_breakdown']) > 0)
                                    @foreach($u['services_breakdown'] as $sb)
                                        <span class="badge bg-label-primary me-1 mb-1">
                                            {{ $sb['service_name'] }}: <strong>{{ $sb['used_qty'] }}/{{ $sb['allocated_qty'] }}</strong>
                                        </span>
                                    @endforeach
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center fw-bold">{{ $allocated }}</td>
                            <td class="text-center fw-bold text-success">{{ $used }}</td>
                            <td class="text-center fw-bold {{ $remaining > 0 ? 'text-warning' : 'text-muted' }}">{{ $remaining }}</td>
                            <td class="text-center">
                                @if($st === 'Active')
                                    <span class="badge bg-label-success"><i class="bx bx-check-circle me-1"></i>Active</span>
                                @elseif($st === 'Fully Utilized')
                                    <span class="badge bg-label-secondary"><i class="bx bx-task me-1"></i>Fully Used</span>
                                @else
                                    <span class="badge bg-label-danger"><i class="bx bx-x-circle me-1"></i>Expired</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Package Usage Details Modal -->
<div class="modal fade" id="packageUsageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <div>
                    <h5 class="modal-title fw-bold" id="modalCustomerName">Package Usage Details</h5>
                    <small class="text-muted" id="modalPackageDates"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Summary Chips -->
                <div class="row g-2 mb-4" id="modalSummaryCards">
                    <!-- Populated by JS -->
                </div>

                <!-- Per Service Breakdown -->
                <h6 class="fw-bold mb-2 text-dark"><i class="bx bx-list-check me-1 text-primary"></i> Services Breakdown</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Service Name</th>
                                <th class="text-center">Allocated</th>
                                <th class="text-center">Used</th>
                                <th class="text-center">Remaining</th>
                                <th style="width: 30%;">Usage Progress</th>
                            </tr>
                        </thead>
                        <tbody id="modalServicesBreakdownBody">
                            <!-- Populated by JS -->
                        </tbody>
                    </table>
                </div>

                <!-- Appointment Log -->
                <h6 class="fw-bold mb-2 text-dark"><i class="bx bx-calendar-check me-1 text-success"></i> Customer Appointments (Package Date Range)</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Apt #</th>
                                <th>Date & Time</th>
                                <th>Service</th>
                                <th>Staff</th>
                                <th>Status</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody id="modalAppointmentsBody">
                            <!-- Populated by JS -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('#packageUsageTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 25
        });

        $(document).on('click', '.view-usage-details', function() {
            const packageId = $(this).data('id');
            const modal = $('#packageUsageModal');

            // Show loading spinner / reset content
            $('#modalCustomerName').text('Loading Details...');
            $('#modalPackageDates').text('');
            $('#modalSummaryCards').empty();
            $('#modalServicesBreakdownBody').html('<tr><td colspan="5" class="text-center text-muted py-3"><div class="spinner-border spinner-border-sm text-primary me-2"></div> Fetching data...</td></tr>');
            $('#modalAppointmentsBody').html('<tr><td colspan="6" class="text-center text-muted py-3">Fetching data...</td></tr>');

            modal.modal('show');

            $.ajax({
                url: `{{ url('admin/packages') }}/${packageId}/usage-details`,
                method: 'GET',
                success: function(res) {
                    if (res.success) {
                        const p = res.package;
                        const u = res.usage;

                        $('#modalCustomerName').text(`Package #${p.id} - ${p.customer_name}`);
                        $('#modalPackageDates').html(`<strong>Taken Date:</strong> ${p.start_date} | <strong>End Date:</strong> ${p.end_date} | <strong>Amount:</strong> ₹${p.amount}`);

                        // Render summary chips
                        let statusBadge = '<span class="badge bg-success">Active</span>';
                        if (u.status === 'Fully Utilized') {
                            statusBadge = '<span class="badge bg-secondary">Fully Utilized</span>';
                        } else if (u.status === 'Expired') {
                            statusBadge = '<span class="badge bg-danger">Expired</span>';
                        }

                        $('#modalSummaryCards').html(`
                            <div class="col-md-3">
                                <div class="p-2 border rounded text-center bg-light">
                                    <small class="text-muted d-block">Allocated Qty</small>
                                    <span class="fw-bold text-dark fs-5">${u.total_allocated_qty}</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-2 border rounded text-center bg-light">
                                    <small class="text-muted d-block">Used Qty</small>
                                    <span class="fw-bold text-success fs-5">${u.total_used_qty}</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-2 border rounded text-center bg-light">
                                    <small class="text-muted d-block">Remaining Qty</small>
                                    <span class="fw-bold text-warning fs-5">${u.total_remaining_qty}</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-2 border rounded text-center bg-light d-flex flex-column justify-content-center align-items-center" style="height: 100%;">
                                    <small class="text-muted d-block mb-1">Status</small>
                                    ${statusBadge}
                                </div>
                            </div>
                        `);

                        // Render Services Breakdown
                        let sbHtml = '';
                        if (u.services_breakdown && u.services_breakdown.length > 0) {
                            u.services_breakdown.forEach(function(sb) {
                                let pct = sb.allocated_qty > 0 ? Math.min(100, Math.round((sb.used_qty / sb.allocated_qty) * 100)) : 0;
                                let barClass = pct >= 100 ? 'bg-success' : 'bg-primary';

                                sbHtml += `<tr>
                                    <td class="fw-semibold text-dark">${sb.service_name}</td>
                                    <td class="text-center fw-bold">${sb.allocated_qty}</td>
                                    <td class="text-center fw-bold text-success">${sb.used_qty}</td>
                                    <td class="text-center fw-bold ${sb.remaining_qty > 0 ? 'text-warning' : 'text-muted'}">${sb.remaining_qty}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress w-100 me-2" style="height: 8px;">
                                                <div class="progress-bar ${barClass}" role="progressbar" style="width: ${pct}%"></div>
                                            </div>
                                            <small class="fw-semibold text-muted" style="min-width: 35px;">${pct}%</small>
                                        </div>
                                    </td>
                                </tr>`;
                            });
                        } else {
                            sbHtml = '<tr><td colspan="5" class="text-center text-muted">No services included in this package.</td></tr>';
                        }
                        $('#modalServicesBreakdownBody').html(sbHtml);

                        // Render Appointments
                        let aptHtml = '';
                        if (u.appointments && u.appointments.length > 0) {
                            u.appointments.forEach(function(apt) {
                                let stBadge = `<span class="badge bg-label-info">${apt.status}</span>`;
                                if (apt.status.toLowerCase() === 'completed') {
                                    stBadge = `<span class="badge bg-label-success">${apt.status}</span>`;
                                } else if (apt.status.toLowerCase() === 'cancelled') {
                                    stBadge = `<span class="badge bg-label-danger">${apt.status}</span>`;
                                }

                                aptHtml += `<tr>
                                    <td class="fw-bold text-primary">${apt.appointment_number}</td>
                                    <td>${apt.appointment_date}</td>
                                    <td><span class="badge bg-label-primary">${apt.service_name}</span></td>
                                    <td>${apt.staff_name}</td>
                                    <td>${stBadge}</td>
                                    <td class="fw-semibold">₹${apt.final_amount}</td>
                                </tr>`;
                            });
                        } else {
                            aptHtml = '<tr><td colspan="6" class="text-center text-muted py-3">No appointments found for this customer in the package validity period.</td></tr>';
                        }
                        $('#modalAppointmentsBody').html(aptHtml);
                    } else {
                        showErrorToast('Failed to load package details.');
                    }
                },
                error: function() {
                    showErrorToast('An error occurred while fetching package usage details.');
                }
            });
        });

        if (window.setupDependentBranchSelect) {
            window.setupDependentBranchSelect('#pkgReportStore', '#pkgReportBranch');
        }
    });
</script>
@endsection
