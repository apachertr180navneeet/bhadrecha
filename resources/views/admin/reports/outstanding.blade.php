@extends('admin.layouts.app')

@section('style')
<style>
    .due-card {
        border-radius: 12px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .due-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08) !important;
    }
    .nav-tabs .nav-link {
        font-weight: 600;
        font-size: 0.85rem;
        color: #697a8d;
        border-radius: 8px 8px 0 0;
        padding: 0.65rem 1.25rem;
    }
    .nav-tabs .nav-link.active {
        color: #566a7f;
        background-color: #fff;
        border-color: #d9dee3 #d9dee3 #fff;
    }
    .table th, .table td {
        vertical-align: middle;
    }
    
    @media print {
        body { background: #fff !important; font-size: 11px !important; }
        .layout-menu, .layout-navbar, .content-footer, .no-print, .btn, .nav-tabs, .filter-card { display: none !important; }
        .tab-content > .tab-pane { display: block !important; opacity: 1 !important; visibility: visible !important; margin-bottom: 2rem; }
        .layout-page { padding: 0 !important; margin: 0 !important; }
        .content-wrapper { padding: 0 !important; }
        .container-fluid { padding: 0 !important; max-width: 100% !important; }
        .card { border: none !important; box-shadow: none !important; }
        .table-responsive { overflow: visible !important; }
    }
</style>
@endsection

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <!-- Header with breadcrumb and actions -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bx bx-error-circle text-danger me-2"></i>Outstanding & Due Report</h4>
            <small class="text-muted">Track unpaid customer balances across appointments and packages</small>
        </div>
        <div class="d-flex align-items-center gap-2 no-print">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                <i class="bx bx-printer me-1"></i> Print
            </button>
            <button type="button" class="btn btn-sm btn-outline-success" id="exportOutstandingCsvBtn">
                <i class="bx bx-download me-1"></i> Export CSV
            </button>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm border-0 mb-4 filter-card no-print" style="border-radius: 12px;">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.reports.outstanding') }}" class="row g-2 align-items-end" id="outstandingFilterForm">
                <div class="col-12 col-sm-6 col-md-2">
                    <label class="form-label small text-muted fw-semibold">Date From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-12 col-sm-6 col-md-2">
                    <label class="form-label small text-muted fw-semibold">Date To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>

                @if(isset($stores) && count($stores) > 0 && ($canViewAllStores || !auth()->user()->store_id))
                <div class="col-12 col-sm-6 col-md-2">
                    <label class="form-label small text-muted fw-semibold">Store</label>
                    <select name="store_id" id="outstandingStoreSelect" class="form-select form-select-sm">
                        <option value="">All Stores</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}" {{ (string)$selectedStoreId === (string)$store->id ? 'selected' : '' }}>
                                {{ $store->store_name ?? $store->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                @if(isset($branches) && count($branches) > 0)
                <div class="col-12 col-sm-6 col-md-2">
                    <label class="form-label small text-muted fw-semibold">Branch</label>
                    <select name="branch_id" id="outstandingBranchSelect" class="form-select form-select-sm">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" data-store-id="{{ $branch->store_id }}" data-store="{{ $branch->store_id }}" {{ (string)$selectedBranchId === (string)$branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="col-12 col-sm-6 col-md-2">
                    <label class="form-label small text-muted fw-semibold">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Customer, phone, #..." value="{{ request('search') }}">
                </div>

                <div class="col-12 col-sm-6 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary flex-grow-1"><i class="bx bx-filter-alt me-1"></i>Filter</button>
                    <a href="{{ route('admin.reports.outstanding') }}" class="btn btn-sm btn-outline-secondary"><i class="bx bx-reset"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="row g-3 mb-4">
        <!-- Grand Total Outstanding -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card shadow-sm border-0 due-card h-100" style="border-left: 4px solid #dc3545 !important;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.72rem;">Total Outstanding Due</small>
                        <span class="avatar-initial rounded bg-label-danger p-1"><i class="bx bx-error-alt fs-5"></i></span>
                    </div>
                    <h3 class="mb-0 fw-bold text-danger">₹{{ number_format($grandTotalOutstanding, 2) }}</h3>
                    <small class="text-muted" style="font-size: 0.75rem;">Combined Service + Package Dues</small>
                </div>
            </div>
        </div>

        <!-- Appointment Dues -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card shadow-sm border-0 due-card h-100" style="border-left: 4px solid #fd7e14 !important;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.72rem;">Appointment Dues</small>
                        <span class="avatar-initial rounded bg-label-warning p-1"><i class="bx bx-calendar-event fs-5"></i></span>
                    </div>
                    <h3 class="mb-0 fw-bold text-warning">₹{{ number_format($totalAppointmentDue, 2) }}</h3>
                    <small class="text-muted" style="font-size: 0.75rem;">{{ $outstandingAppointments->count() }} Unsettled Appointments</small>
                </div>
            </div>
        </div>

        <!-- Package Dues -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card shadow-sm border-0 due-card h-100" style="border-left: 4px solid #6f42c1 !important;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.72rem;">Package Dues</small>
                        <span class="avatar-initial rounded bg-label-primary p-1" style="background-color:#e0cffc !important; color:#3d0a91;"><i class="bx bx-package fs-5"></i></span>
                    </div>
                    <h3 class="mb-0 fw-bold" style="color: #6f42c1;">₹{{ number_format($totalPackageDue, 2) }}</h3>
                    <small class="text-muted" style="font-size: 0.75rem;">{{ $outstandingPackages->count() }} Packages with Balance</small>
                </div>
            </div>
        </div>

        <!-- Customers with Dues -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card shadow-sm border-0 due-card h-100" style="border-left: 4px solid #0dcaf0 !important;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.72rem;">Customers With Dues</small>
                        <span class="avatar-initial rounded bg-label-info p-1"><i class="bx bx-user-voice fs-5"></i></span>
                    </div>
                    <h3 class="mb-0 fw-bold text-info">{{ $totalCustomersDueCount }}</h3>
                    <small class="text-muted" style="font-size: 0.75rem;">Unique Customer Accounts</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Tabs Card -->
    <div class="card shadow-sm border-0" style="border-radius: 12px;">
        <div class="card-header border-bottom bg-transparent pb-0">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#customerTab" type="button" role="tab">
                        <i class="bx bx-user me-1"></i> Customer Summary
                        <span class="badge bg-danger rounded-pill ms-1">{{ count($customerSummaries) }}</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#appointmentTab" type="button" role="tab">
                        <i class="bx bx-calendar-check me-1"></i> Appointment Dues
                        <span class="badge bg-warning rounded-pill ms-1">{{ count($outstandingAppointments) }}</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#packageTab" type="button" role="tab">
                        <i class="bx bx-gift me-1"></i> Package Dues
                        <span class="badge rounded-pill ms-1" style="background-color:#6f42c1;">{{ count($outstandingPackages) }}</span>
                    </button>
                </li>
            </ul>
        </div>

        <div class="tab-content p-0">
            <!-- TAB 1: Customer-Wise Summary -->
            <div class="tab-pane fade show active" id="customerTab" role="tabpanel">
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover align-middle mb-0" id="customerDueTable">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 40px;">#</th>
                                <th class="text-center no-print" style="width: 80px;">Action</th>
                                <th>Customer Name</th>
                                <th>Contact</th>
                                <th>Store / Branch</th>
                                <th class="text-end">Appt Due (₹)</th>
                                <th class="text-end">Package Due (₹)</th>
                                <th class="text-end fw-bold text-danger">Total Due (₹)</th>
                                <th class="text-center">Pending Items</th>
                                <th class="text-center">Last Transaction</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customerSummaries as $idx => $cs)
                                <tr>
                                    <td class="text-center text-muted small">{{ $idx + 1 }}</td>
                                    <td class="text-center no-print">
                                        @if(isset($cs['customer']) && $cs['customer'])
                                            <a href="{{ route('admin.customers.show', $cs['customer']->id) }}" class="btn btn-xs btn-outline-primary" title="View Customer Details">
                                                <i class="bx bx-user me-1"></i> Profile
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2">
                                                <span class="avatar-initial rounded-circle bg-label-danger text-danger fw-bold">
                                                    {{ strtoupper(substr($cs['customer_name'] ?? 'C', 0, 1)) }}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="fw-semibold text-dark d-block">{{ $cs['customer_name'] }}</span>
                                                @if(!empty($cs['customer_email']))
                                                    <small class="text-muted">{{ $cs['customer_email'] }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if(!empty($cs['customer_phone']) && $cs['customer_phone'] !== 'N/A')
                                            <a href="tel:{{ $cs['customer_phone'] }}" class="text-body fw-semibold">
                                                <i class="bx bx-phone me-1 text-muted"></i>{{ $cs['customer_phone'] }}
                                            </a>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-label-secondary">{{ $cs['store_name'] }}</span>
                                        @if(!empty($cs['branch_name']) && $cs['branch_name'] !== 'N/A')
                                            <small class="text-muted d-block">{{ $cs['branch_name'] }}</small>
                                        @endif
                                    </td>
                                    <td class="text-end fw-semibold text-warning">
                                        ₹{{ number_format($cs['appointment_due'], 2) }}
                                    </td>
                                    <td class="text-end fw-semibold" style="color: #6f42c1;">
                                        ₹{{ number_format($cs['package_due'], 2) }}
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold text-danger fs-6">₹{{ number_format($cs['total_due'], 2) }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($cs['appointment_count'] > 0)
                                            <span class="badge bg-label-warning me-1">{{ $cs['appointment_count'] }} Appts</span>
                                        @endif
                                        @if($cs['package_count'] > 0)
                                            <span class="badge bg-label-primary">{{ $cs['package_count'] }} Pkgs</span>
                                        @endif
                                    </td>
                                    <td class="text-center small text-muted">
                                        {{ $cs['last_date'] !== 'N/A' ? \Carbon\Carbon::parse($cs['last_date'])->format('d M Y') : 'N/A' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-5 text-muted">
                                        <i class="bx bx-check-circle fs-1 text-success mb-2"></i>
                                        <p class="mb-0 fw-semibold">Great! No outstanding customer dues found for the selected filter.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 2: Appointment Dues (Service-Wise) -->
            <div class="tab-pane fade" id="appointmentTab" role="tabpanel">
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover align-middle mb-0" id="appointmentDueTable">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 40px;">#</th>
                                <th class="text-center no-print" style="width: 80px;">Action</th>
                                <th>Appointment #</th>
                                <th>Date & Time</th>
                                <th>Customer</th>
                                <th>Service / Items</th>
                                <th>Store / Branch</th>
                                <th class="text-end">Total (₹)</th>
                                <th class="text-end">Paid (₹)</th>
                                <th class="text-end fw-bold text-danger">Due Amount (₹)</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($outstandingAppointments as $idx => $apt)
                                @php
                                    $aptDue = max(0, (float)$apt->final_amount - (float)$apt->paid_amount);
                                @endphp
                                <tr>
                                    <td class="text-center text-muted small">{{ $idx + 1 }}</td>
                                    <td class="text-center no-print">
                                        <a href="{{ route('admin.appointments.index', ['date' => $apt->appointment_date ? \Carbon\Carbon::parse($apt->appointment_date)->format('Y-m-d') : null]) }}" class="btn btn-xs btn-outline-primary" title="View Appointment">
                                            <i class="bx bx-edit-alt me-1"></i> Settle
                                        </a>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-primary">{{ $apt->appointment_number ?? ('#' . $apt->id) }}</span>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold">{{ $apt->appointment_date ? \Carbon\Carbon::parse($apt->appointment_date)->format('d M Y') : 'N/A' }}</div>
                                        <small class="text-muted">{{ $apt->appointment_date ? \Carbon\Carbon::parse($apt->appointment_date)->format('h:i A') : '' }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $apt->customer->name ?? 'Walk-in' }}</div>
                                        @if($apt->customer && $apt->customer->phone)
                                            <small class="text-muted"><i class="bx bx-phone me-1"></i>{{ $apt->customer->phone }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($apt->appointmentServices && $apt->appointmentServices->count() > 0)
                                            <span class="badge bg-label-info">{{ $apt->appointmentServices->first()->service->service_name ?? 'Service' }}</span>
                                            @if($apt->appointmentServices->count() > 1)
                                                <small class="text-muted">+{{ $apt->appointmentServices->count() - 1 }} more</small>
                                            @endif
                                        @else
                                            <span class="badge bg-label-secondary">{{ $apt->service->service_name ?? 'Service' }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-label-secondary">{{ $apt->store->store_name ?? $apt->store->name ?? 'N/A' }}</span>
                                    </td>
                                    <td class="text-end fw-semibold">₹{{ number_format($apt->final_amount ?? 0, 2) }}</td>
                                    <td class="text-end text-success fw-semibold">₹{{ number_format($apt->paid_amount ?? 0, 2) }}</td>
                                    <td class="text-end fw-bold text-danger fs-6">₹{{ number_format($aptDue, 2) }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-label-{{ $apt->payment_status === 'partial' ? 'warning' : 'danger' }} text-uppercase" style="font-size: 0.7rem;">
                                            {{ $apt->payment_status ?? 'Pending' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center py-5 text-muted">
                                        <i class="bx bx-check-circle fs-1 text-success mb-2"></i>
                                        <p class="mb-0 fw-semibold">No outstanding appointment balances found.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 3: Package Dues -->
            <div class="tab-pane fade" id="packageTab" role="tabpanel">
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover align-middle mb-0" id="packageDueTable">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 40px;">#</th>
                                <th class="text-center no-print" style="width: 80px;">Action</th>
                                <th>Customer</th>
                                <th>Package Details</th>
                                <th>Purchase Date</th>
                                <th>Validity / Expiry</th>
                                <th class="text-end">Total Package (₹)</th>
                                <th class="text-end text-success">Advance Paid (₹)</th>
                                <th class="text-end fw-bold text-danger">Remaining Due (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($outstandingPackages as $idx => $pkg)
                                <tr>
                                    <td class="text-center text-muted small">{{ $idx + 1 }}</td>
                                    <td class="text-center no-print">
                                        <button type="button" class="btn btn-xs btn-outline-success open-add-payment-btn" 
                                                data-id="{{ $pkg->id }}" 
                                                data-remaining="{{ $pkg->remaining }}"
                                                data-customer="{{ $pkg->customer->name ?? 'Customer' }}">
                                            <i class="bx bx-plus me-1"></i> Collect
                                        </button>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $pkg->customer->name ?? 'N/A' }}</div>
                                        @if($pkg->customer && $pkg->customer->phone)
                                            <small class="text-muted"><i class="bx bx-phone me-1"></i>{{ $pkg->customer->phone }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-label-primary">{{ count($pkg->services_with_qty ?? []) }} Services</span>
                                        <small class="text-muted ms-1">({{ $pkg->duration->title ?? ($pkg->qty . ' visits') }})</small>
                                    </td>
                                    <td>
                                        {{ $pkg->created_at ? $pkg->created_at->format('d M Y') : 'N/A' }}
                                    </td>
                                    <td>
                                        {{ $pkg->end_date ? \Carbon\Carbon::parse($pkg->end_date)->format('d M Y') : 'Ongoing' }}
                                    </td>
                                    <td class="text-end fw-semibold">₹{{ number_format($pkg->amount, 2) }}</td>
                                    <td class="text-end text-success fw-semibold">₹{{ number_format($pkg->advance ?? 0, 2) }}</td>
                                    <td class="text-end fw-bold text-danger fs-6">₹{{ number_format($pkg->remaining, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <i class="bx bx-check-circle fs-1 text-success mb-2"></i>
                                        <p class="mb-0 fw-semibold">No outstanding package payments found.</p>
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

<!-- Modal for Collecting Package Payment -->
<div class="modal fade" id="addPackagePaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Collect Package Due</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addPackagePaymentForm" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="modal_package_id" name="package_id">
                    <div class="mb-2">
                        <label class="form-label small text-muted">Customer</label>
                        <div class="fw-bold text-dark" id="modal_customer_name"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Remaining Balance</label>
                        <div class="fs-5 fw-bold text-danger" id="modal_remaining_display"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Amount to Pay (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="1" name="amount" id="modal_pay_amount" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Payment Mode <span class="text-danger">*</span></label>
                        <select name="payment_mode" class="form-select" required>
                            <option value="cash">Cash</option>
                            <option value="upi">UPI / QR Code</option>
                            <option value="card">Card</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Remarks (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-success" id="savePaymentBtn">Submit Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    // Dependent store-to-branch dropdowns
    if (window.setupDependentBranchSelect) {
        window.setupDependentBranchSelect('#outstandingStoreSelect', '#outstandingBranchSelect');
    }

    // Open Package Add Payment Modal
    $('.open-add-payment-btn').on('click', function() {
        let pkgId = $(this).data('id');
        let remaining = parseFloat($(this).data('remaining')) || 0;
        let customer = $(this).data('customer');

        $('#modal_package_id').val(pkgId);
        $('#modal_customer_name').text(customer);
        $('#modal_remaining_display').text('₹' + remaining.toFixed(2));
        $('#modal_pay_amount').val(remaining).attr('max', remaining);
        $('#addPackagePaymentForm').attr('action', '/admin/packages/' + pkgId + '/add-payment');

        $('#addPackagePaymentModal').modal('show');
    });

    // Handle AJAX Package payment submit if route supports it
    $('#addPackagePaymentForm').on('submit', function(e) {
        let form = $(this);
        let submitBtn = $('#savePaymentBtn');
        submitBtn.prop('disabled', true).text('Saving...');
    });

    // Export active tab table to CSV
    $('#exportOutstandingCsvBtn').on('click', function() {
        let activePane = document.querySelector('.tab-pane.active');
        if (!activePane) return;
        
        let table = activePane.querySelector('table');
        if (!table) return;

        let rows = table.querySelectorAll('tr');
        let csv = [];

        for (let i = 0; i < rows.length; i++) {
            let row = [], cols = rows[i].querySelectorAll('td, th');
            for (let j = 0; j < cols.length; j++) {
                if (cols[j].classList.contains('no-print')) continue;
                let text = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, ' ').replace(/\s+/g, ' ').trim();
                text = text.replace(/"/g, '""');
                row.push('"' + text + '"');
            }
            csv.push(row.join(','));
        }

        let csvString = csv.join('\n');
        let tabName = activePane.id || 'outstanding';
        let filename = 'Outstanding_Report_' + tabName + '_' + new Date().toISOString().slice(0, 10) + '.csv';
        let blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
        let link = document.createElement('a');
        let url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });
});
</script>
@endsection
