@extends('admin.layouts.app')

@section('style')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .select2-container--bootstrap-5 .select2-selection { min-height: 38px; }
</style>
@endsection

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-0">Appointments</h4>
            <small class="text-muted">Manage all customer appointments</small>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('admin.appointments.calendar') }}" class="btn btn-outline-primary me-2">
                <i class="bx bx-calendar me-1"></i> Calendar View
            </a>
            @can('create appointments')
            <a href="{{ route('admin.appointments.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Add Appointment
            </a>
            @endcan
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form id="filterForm" method="GET" action="{{ route('admin.appointments.index') }}" class="row g-3 align-items-end">
                <div class="col-12 col-sm-6 col-md-4 col-xl-3">
                    <label class="form-label">Date Range</label>
                    <input type="text" class="form-control" id="dateRange" name="date_range" value="{{ request('date_range') }}" placeholder="Select date range">
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status" id="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="checked_in" {{ request('status') === 'checked_in' ? 'selected' : '' }}>Checked In</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="no_show" {{ request('status') === 'no_show' ? 'selected' : '' }}>No Show</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                    <label class="form-label">Client</label>
                    <select class="form-select" name="customer_id" id="customerFilter">
                        <option value="">All Clients</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ (string)request('customer_id') === (string)$customer->id ? 'selected' : '' }}>{{ $customer->full_name ?? $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                    <label class="form-label">Store</label>
                    <select class="form-select" name="store_id" id="storeFilter" {{ (!$canViewAllStores && $selectedStoreId) ? 'disabled' : '' }}>
                        <option value="">All Stores</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}" {{ (string)($selectedStoreId ?? request('store_id')) === (string)$store->id ? 'selected' : '' }}>{{ $store->store_name ?? $store->name }}</option>
                        @endforeach
                    </select>
                    @if(!$canViewAllStores && $selectedStoreId)
                        <input type="hidden" name="store_id" value="{{ $selectedStoreId }}">
                    @endif
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                    <label class="form-label">Branch</label>
                    <select class="form-select" name="branch_id" id="branchFilter" {{ (!$canViewAllBranches && $selectedBranchId) ? 'disabled' : '' }}>
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" data-store-id="{{ $branch->store_id }}" {{ (string)($selectedBranchId ?? request('branch_id')) === (string)$branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    @if(!$canViewAllBranches && $selectedBranchId)
                        <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">
                    @endif
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                    <label class="form-label">Stylist</label>
                    <select class="form-select" name="staff_id" id="staffFilter">
                        <option value="">All Stylists</option>
                        @foreach($staffs as $staff)
                            <option value="{{ $staff->id }}" data-store-id="{{ $staff->store_id }}" data-branch-id="{{ $staff->branch_id }}" {{ (string)request('staff_id') === (string)$staff->id ? 'selected' : '' }}>{{ $staff->full_name ?? $staff->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-xl-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1" id="applyFilter"><i class="bx bx-filter me-1"></i> Filter</button>
                    <button type="button" class="btn btn-outline-secondary flex-grow-1" id="resetFilter">Reset</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive text-nowrap">
<table class="table table-hover" id="appointmentsTable">
                    <thead>
                        <tr>
                            <th>S. No.</th>
                            @if(auth()->user()->can('view appointments') || auth()->user()->can('edit appointments') || auth()->user()->can('delete appointments'))
                            <th class="text-nowrap" style="width: 80px;">Actions</th>
                            @endif
                            <th>Appt No.</th>
                            <th>Client</th>
                            <th>Stylist</th>
                            <th>Service</th>
                            <th>Date / Time</th>
                            <th>Status</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($appointments as $index => $appointment)
                        <tr>
                            <td class="fw-semibold text-muted">{{ method_exists($appointments, 'firstItem') ? ($appointments->firstItem() + $index) : ($index + 1) }}</td>
                            @if(auth()->user()->can('view appointments') || auth()->user()->can('edit appointments') || auth()->user()->can('delete appointments'))
                            <td>
                                <div class="dropdown">
                                    <button class="btn p-0 dropdown-toggle hide-arrow" type="button" data-bs-toggle="dropdown" data-bs-boundary="window">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        @can('view appointments')
                                        <li><a class="dropdown-item view-appointment" href="javascript:void(0);" data-id="{{ $appointment->id }}"><i class="bx bx-show me-1"></i> View</a></li>
                                        @endcan
                                        @can('edit appointments')
                                        <li><a class="dropdown-item" href="{{ route('admin.appointments.edit', $appointment->id) }}"><i class="bx bx-edit-alt me-1"></i> Edit</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item status-update text-warning" href="javascript:void(0);" data-id="{{ $appointment->id }}" data-status="pending"><i class="bx bx-time-five me-1"></i> Mark Pending</a></li>
                                        <li><a class="dropdown-item status-update" href="javascript:void(0);" data-id="{{ $appointment->id }}" data-status="confirmed"><i class="bx bx-check-circle me-1"></i> Confirm</a></li>
                                        <li><a class="dropdown-item status-update" href="javascript:void(0);" data-id="{{ $appointment->id }}" data-status="checked_in"><i class="bx bx-log-in me-1"></i> Check In</a></li>
                                        <li><a class="dropdown-item status-update" href="javascript:void(0);" data-id="{{ $appointment->id }}" data-status="in_progress"><i class="bx bx-timer me-1"></i> In Progress</a></li>
                                        <li><a class="dropdown-item status-update" href="javascript:void(0);" data-id="{{ $appointment->id }}" data-status="completed"><i class="bx bx-check me-1"></i> Complete</a></li>
                                        <li><a class="dropdown-item status-update text-dark" href="javascript:void(0);" data-id="{{ $appointment->id }}" data-status="no_show"><i class="bx bx-user-x me-1"></i> Mark No Show</a></li>
                                        <li><a class="dropdown-item status-update text-danger" href="javascript:void(0);" data-id="{{ $appointment->id }}" data-status="cancelled"><i class="bx bx-x-circle me-1"></i> Cancel</a></li>
                                        @endcan
                                        @can('delete appointments')
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger delete-appointment" href="javascript:void(0);" data-id="{{ $appointment->id }}"><i class="bx bx-trash me-1"></i> Delete</a></li>
                                        @endcan
                                    </ul>
                                </div>
                            </td>
                            @endif
                            <td>
                                <span class="fw-bold text-primary font-monospace">{{ $appointment->appointment_number ?? '#' . $appointment->id }}</span>
                            </td>
                            <td>
                                <div>{{ $appointment->customer->full_name ?? $appointment->customer->name ?? 'N/A' }}</div>
                                @if($appointment->customer && $appointment->customer->mobile)
                                    <small class="text-muted"><i class="bx bx-phone me-1"></i>{{ $appointment->customer->mobile }}</small>
                                @endif
                            </td>
                            <td>
                                @if($appointment->appointmentServices->count() > 0)
                                    @foreach($appointment->appointmentServices as $item)
                                        @php
                                             $members = $item->staffMembers();
                                             $names = $members->count() > 0 ? $members->pluck('full_name')->implode(', ') : ($item->staff->full_name ?? $item->staff->name ?? 'Unassigned');
                                        @endphp
                                        <div><small class="fw-semibold" title="{{ $names }}"><i class="bx bx-user me-1"></i>{{ $names }}</small></div>
                                    @endforeach
                                 @else
                                    {{ $appointment->staff->full_name ?? $appointment->staff->name ?? 'N/A' }}
                                @endif
                            </td>
                            <td>
                                @if($appointment->appointmentServices->count() > 0)
                                    @foreach($appointment->appointmentServices as $item)
                                        <div>
                                            <span class="badge bg-label-secondary me-1">{{ $item->service->service_name ?? 'N/A' }}</span>
                                            <small class="text-muted">₹{{ number_format($item->price, 2) }}</small>
                                        </div>
                                    @endforeach
                                @else
                                    {{ $appointment->service->service_name ?? 'N/A' }}
                                @endif
                            </td>
                            <td>
                                <div>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d M Y, h:i A') }}</div>
                                @if($appointment->check_in_time)
                                    <small class="text-muted d-block" title="Check-in Time"><i class="bx bx-log-in me-1"></i>In: {{ \Carbon\Carbon::parse($appointment->check_in_time)->format('h:i A') }}</small>
                                @endif
                                @if($appointment->completed_time)
                                    <small class="text-muted d-block" title="Check-out Time"><i class="bx bx-log-out me-1"></i>Out: {{ \Carbon\Carbon::parse($appointment->completed_time)->format('h:i A') }}</small>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusClasses = [
                                        'pending' => 'bg-label-warning',
                                        'confirmed' => 'bg-label-info',
                                        'checked_in' => 'bg-label-primary',
                                        'in_progress' => 'bg-label-secondary',
                                        'completed' => 'bg-label-success',
                                        'cancelled' => 'bg-label-danger',
                                        'no_show' => 'bg-label-dark',
                                    ];
                                    $class = $statusClasses[$appointment->status] ?? 'bg-label-secondary';
                                @endphp
                                <span class="badge {{ $class }}">{{ ucfirst(str_replace('_', ' ', $appointment->status)) }}</span>
                            </td>
                            <td>
                                <div>Final: ₹{{ number_format($appointment->final_amount, 2) }}</div>
                                <small class="text-muted d-block">Paid: ₹{{ number_format($appointment->paid_amount ?? 0, 2) }}</small>
                                @php
                                    $adj = $adjustedBalances[$appointment->id] ?? null;
                                    $rawCredit = $adj ? $adj['raw_credit'] : ($appointment->credit_amount ?? 0);
                                    $rawDebit = $adj ? $adj['raw_debit'] : ($appointment->debit_amount ?? 0);
                                    $adjCredit = $adj ? $adj['adjusted_credit'] : $rawCredit;
                                    $adjDebit = $adj ? $adj['adjusted_debit'] : $rawDebit;
                                    $creditUsed = $adj ? $adj['credit_used'] : 0;
                                    $creditCovered = $adj ? $adj['credit_covered'] : 0;
                                @endphp
                                @if($rawDebit > 0)
                                    @if($adjDebit == 0 && $creditCovered > 0)
                                        <span class="badge bg-label-info fs-tiny me-1" title="Due of ₹{{ number_format($rawDebit, 2) }} adjusted from Customer Credit"><i class="bx bx-check-circle me-1"></i>Due: ₹0.00 (Adjusted ₹{{ number_format($creditCovered, 2) }} Credit)</span>
                                    @elseif($adjDebit > 0 && $creditCovered > 0)
                                        <span class="badge bg-label-danger fs-tiny me-1" title="₹{{ number_format($creditCovered, 2) }} adjusted from Credit"><i class="bx bx-minus-circle me-1"></i>Due: ₹{{ number_format($adjDebit, 2) }} (Adj ₹{{ number_format($creditCovered, 2) }})</span>
                                    @else
                                        <span class="badge bg-label-danger fs-tiny me-1"><i class="bx bx-minus-circle me-1"></i>Due: ₹{{ number_format($rawDebit, 2) }}</span>
                                    @endif
                                @elseif($rawCredit > 0)
                                    @if($creditUsed > 0 && $adjCredit == 0)
                                        <span class="badge bg-label-success fs-tiny me-1" title="₹{{ number_format($rawCredit, 2) }} Credit adjusted towards other appointments"><i class="bx bx-check-circle me-1"></i>Credit: ₹{{ number_format($rawCredit, 2) }} (Adjusted)</span>
                                    @elseif($creditUsed > 0 && $adjCredit > 0)
                                        <span class="badge bg-label-success fs-tiny me-1" title="₹{{ number_format($creditUsed, 2) }} Credit adjusted"><i class="bx bx-plus-circle me-1"></i>Credit: ₹{{ number_format($adjCredit, 2) }} (Used ₹{{ number_format($creditUsed, 2) }})</span>
                                    @else
                                        <span class="badge bg-label-success fs-tiny me-1"><i class="bx bx-plus-circle me-1"></i>Credit: ₹{{ number_format($rawCredit, 2) }}</span>
                                    @endif
                                @endif
                                @if($appointment->payment_type)
                                    @if(strtolower($appointment->payment_type) === 'split')
                                        <small class="text-muted d-block">Via: <span class="badge bg-label-info">SPLIT</span></small>
                                        <small class="text-success d-block" style="font-size: 0.72rem;"><i class="bx bx-money me-1"></i>Cash: ₹{{ number_format($appointment->cash_amount ?? 0, 2) }}</small>
                                        <small class="text-primary d-block" style="font-size: 0.72rem;"><i class="bx bx-qr-scan me-1"></i>UPI: ₹{{ number_format($appointment->upi_amount ?? 0, 2) }}</small>
                                        @if($appointment->upi_reference)
                                            <small class="text-muted d-block" style="font-size: 0.72rem;">Ref: {{ $appointment->upi_reference }}</small>
                                        @endif
                                    @else
                                        <small class="text-muted d-block">Via: <span class="text-uppercase">{{ $appointment->payment_type }}</span></small>
                                        @if(strtolower($appointment->payment_type) === 'upi' && $appointment->upi_reference)
                                            <small class="text-muted d-block" style="font-size: 0.72rem;">Ref: {{ $appointment->upi_reference }}</small>
                                        @endif
                                    @endif
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

<!-- View Appointment Modal -->
<div class="modal fade" id="viewAppointmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="viewApptTitle">Appointment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewApptBody">
                <div class="text-center py-4"><div class="spinner-border text-primary"></div></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    $(document).ready(function() {
        const table = $('#appointmentsTable').DataTable({
            order: [[0, 'asc']]
        });

        const dateVal = $('#dateRange').val();
        $('#dateRange').flatpickr({
            mode: 'range',
            dateFormat: 'Y-m-d',
            defaultDate: dateVal ? dateVal.split(' to ') : null,
            onClose: function(selectedDates, dateStr) {
                if (dateStr) {
                    applyFilters();
                }
            }
        });

        function applyFilters() {
            const dateRange = $('#dateRange').val();
            const status = $('#statusFilter').val();
            const customer = $('#customerFilter').val();
            const staff = $('#staffFilter').val();
            const store = $('#storeFilter').val();
            const branch = $('#branchFilter').val();

            const params = {};
            if (dateRange) params.date_range = dateRange;
            if (status) params.status = status;
            if (customer) params.customer_id = customer;
            if (staff) params.staff_id = staff;
            if (store) params.store_id = store;
            if (branch) params.branch_id = branch;

            window.location.href = '{{ route("admin.appointments.index") }}?' + $.param(params);
        }

        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            applyFilters();
        });

        $('#applyFilter').on('click', function(e) {
            e.preventDefault();
            applyFilters();
        });

        $('#resetFilter').on('click', function() {
            window.location.href = '{{ route("admin.appointments.index") }}';
        });

        $(document).on('click', '.status-update', function(e) {
            e.preventDefault();
            const btn = $(this);
            const id = btn.data('id');
            const status = btn.data('status');
            
            if (!id || !status) {
                showErrorToast('Invalid status update request.');
                return;
            }
            
            let swalConfig = {
                title: 'Update Status?',
                text: 'Change status to ' + status.replace(/_/g, ' ') + '?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#7367f0',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, update!'
            };

            if (status === 'completed') {
                swalConfig.title = 'Complete Appointment';
                swalConfig.text = undefined;
                swalConfig.html = `
                    <div class="mb-3 text-start mt-3">
                        <label class="form-label fw-semibold">Payment Mode</label>
                        <select id="swal-payment-type" class="form-select">
                            <option value="cash">Cash</option>
                            <option value="upi">UPI</option>
                            <option value="card">Card</option>
                            <option value="split">Split Payment (Cash + UPI)</option>
                        </select>
                    </div>
                    <div class="mb-3 text-start" id="swal-paid-amount-container">
                        <label class="form-label fw-semibold">Paid Amount (₹)</label>
                        <input type="number" id="swal-paid-amount" class="form-control" step="0.01" min="0" placeholder="Enter Paid Amount">
                    </div>
                    <div class="mb-3 text-start d-none" id="swal-split-container">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label fw-semibold text-success"><i class="bx bx-money me-1"></i>Cash Amount (₹)</label>
                                <input type="number" id="swal-cash-amount" class="form-control" step="0.01" min="0" placeholder="0.00">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold text-primary"><i class="bx bx-qr-scan me-1"></i>UPI Amount (₹)</label>
                                <input type="number" id="swal-upi-amount" class="form-control" step="0.01" min="0" placeholder="0.00">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3 text-start d-none" id="swal-upi-container">
                        <label class="form-label fw-semibold">UPI Reference / Transaction ID</label>
                        <input type="text" id="swal-upi-ref" class="form-control" placeholder="Enter UPI Reference">
                    </div>
                `;
                swalConfig.didOpen = () => {
                    const paymentTypeSelect = document.getElementById('swal-payment-type');
                    const upiContainer = document.getElementById('swal-upi-container');
                    const splitContainer = document.getElementById('swal-split-container');
                    const paidAmtContainer = document.getElementById('swal-paid-amount-container');

                    paymentTypeSelect.addEventListener('change', (e) => {
                        const val = e.target.value;
                        if (val === 'split') {
                            paidAmtContainer.classList.add('d-none');
                            splitContainer.classList.remove('d-none');
                            upiContainer.classList.remove('d-none');
                        } else if (val === 'upi') {
                            paidAmtContainer.classList.remove('d-none');
                            splitContainer.classList.add('d-none');
                            upiContainer.classList.remove('d-none');
                        } else {
                            paidAmtContainer.classList.remove('d-none');
                            splitContainer.classList.add('d-none');
                            upiContainer.classList.add('d-none');
                        }
                    });
                };
                swalConfig.preConfirm = () => {
                    const payment_type = document.getElementById('swal-payment-type').value;
                    const upi_reference = document.getElementById('swal-upi-ref').value;
                    const paid_amount = document.getElementById('swal-paid-amount').value;
                    const cash_amount = document.getElementById('swal-cash-amount').value;
                    const upi_amount = document.getElementById('swal-upi-amount').value;

                    if (payment_type === 'split') {
                        if (!cash_amount && !upi_amount) {
                            Swal.showValidationMessage('Please enter Cash or UPI amount for split payment');
                            return false;
                        }
                        if (parseFloat(upi_amount) > 0 && !upi_reference.trim()) {
                            Swal.showValidationMessage('Please enter UPI reference for UPI payment portion');
                            return false;
                        }
                        return { payment_type, cash_amount, upi_amount, upi_reference };
                    }

                    if (payment_type === 'upi' && !upi_reference.trim()) {
                        Swal.showValidationMessage('Please enter UPI reference');
                        return false;
                    }
                    return { payment_type, upi_reference, paid_amount };
                };
            }

            Swal.fire(swalConfig).then((result) => {
                if (result.isConfirmed) {
                    let ajaxData = { _token: '{{ csrf_token() }}', status: status };
                    if (status === 'completed' && result.value) {
                        ajaxData.payment_type = result.value.payment_type;
                        ajaxData.upi_reference = result.value.upi_reference;
                        if (result.value.payment_type === 'split') {
                            ajaxData.cash_amount = result.value.cash_amount;
                            ajaxData.upi_amount = result.value.upi_amount;
                        } else if (result.value.paid_amount !== '' && result.value.paid_amount !== null) {
                            ajaxData.paid_amount = result.value.paid_amount;
                        }
                    }
                    const url = '{{ route("admin.appointments.update-status", ":id") }}'.replace(':id', id).replace('%3Aid', id);
                    $.ajax({
                        url: url,
                        method: 'POST',
                        data: ajaxData,
                        success: function(res) {
                            if (res.success) {
                                showSuccessToast(res.message || 'Status updated successfully.');
                                if (res.whatsapp_url) {
                                    window.open(res.whatsapp_url, '_blank');
                                }
                                setTimeout(() => location.reload(), 800);
                            } else {
                                showErrorToast(res.message || 'Failed to update status.');
                            }
                        },
                        error: function(xhr) {
                            const msg = (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error)) 
                                ? (xhr.responseJSON.message || xhr.responseJSON.error) 
                                : 'Failed to update status.';
                            showErrorToast(msg);
                        }
                    });
                }
            });
        });

        $(document).on('click', '.view-appointment', function() {
            const id = $(this).data('id');
            const modal = $('#viewAppointmentModal');
            $('#viewApptBody').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>');
            modal.modal('show');

            $.ajax({
                url: '{{ route("admin.appointments.show", ":id") }}'.replace(':id', id),
                method: 'GET',
                success: function(res) {
                    if (res.success) {
                        const d = res.data;
                        $('#viewApptTitle').text('Appointment Details ' + d.appointment_number);
                        
                        let servicesHtml = '';
                        if (d.services && d.services.length > 0) {
                            d.services.forEach((s, idx) => {
                                servicesHtml += `
                                    <tr>
                                        <td>${idx + 1}</td>
                                        <td><span class="fw-semibold">${s.service_name}</span></td>
                                        <td>${s.staff_name}</td>
                                        <td>${s.duration} min</td>
                                        <td>₹${s.price}</td>
                                    </tr>
                                `;
                            });
                        } else {
                            servicesHtml = `<tr><td colspan="5" class="text-center text-muted">No services found</td></tr>`;
                        }

                        let balanceBadgesHtml = '';
                        if (d.adjusted_balance) {
                            const ab = d.adjusted_balance;
                            if (ab.raw_debit > 0) {
                                if (ab.adjusted_debit == 0 && ab.credit_covered > 0) {
                                    balanceBadgesHtml += `<div class="col-md-6 mb-2"><div class="alert alert-info py-2 mb-0 text-center fw-bold"><i class="bx bx-check-circle me-1"></i>Due: ₹0.00 (Adjusted ₹${parseFloat(ab.credit_covered).toFixed(2)} Credit)</div></div>`;
                                } else if (ab.adjusted_debit > 0 && ab.credit_covered > 0) {
                                    balanceBadgesHtml += `<div class="col-md-6 mb-2"><div class="alert alert-danger py-2 mb-0 text-center fw-bold"><i class="bx bx-minus-circle me-1"></i>Net Due: ₹${parseFloat(ab.adjusted_debit).toFixed(2)} (Adjusted ₹${parseFloat(ab.credit_covered).toFixed(2)} Credit)</div></div>`;
                                } else {
                                    balanceBadgesHtml += `<div class="col-md-6 mb-2"><div class="alert alert-danger py-2 mb-0 text-center fw-bold"><i class="bx bx-minus-circle me-1"></i>Debit (Outstanding Due): ₹${parseFloat(ab.raw_debit).toFixed(2)}</div></div>`;
                                }
                            }
                            if (ab.raw_credit > 0) {
                                if (ab.credit_used > 0 && ab.adjusted_credit == 0) {
                                    balanceBadgesHtml += `<div class="col-md-6 mb-2"><div class="alert alert-success py-2 mb-0 text-center fw-bold"><i class="bx bx-check-circle me-1"></i>Credit: ₹${parseFloat(ab.raw_credit).toFixed(2)} (Fully Adjusted)</div></div>`;
                                } else if (ab.credit_used > 0 && ab.adjusted_credit > 0) {
                                    balanceBadgesHtml += `<div class="col-md-6 mb-2"><div class="alert alert-success py-2 mb-0 text-center fw-bold"><i class="bx bx-plus-circle me-1"></i>Net Credit: ₹${parseFloat(ab.adjusted_credit).toFixed(2)} (Used ₹${parseFloat(ab.credit_used).toFixed(2)})</div></div>`;
                                } else {
                                    balanceBadgesHtml += `<div class="col-md-6 mb-2"><div class="alert alert-success py-2 mb-0 text-center fw-bold"><i class="bx bx-plus-circle me-1"></i>Credit (Advance): ₹${parseFloat(ab.raw_credit).toFixed(2)}</div></div>`;
                                }
                            }
                        }

                        const bodyHtml = `
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-1">Client Name</h6>
                                    <p class="fw-bold fs-6 mb-0">${d.customer ? d.customer.name : 'N/A'}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-1">Date & Time</h6>
                                    <p class="fw-bold fs-6 mb-0">${d.date_time}</p>
                                </div>
                            </div>

                            <div class="card shadow-none border mb-3">
                                <div class="card-header bg-light py-2">
                                    <h6 class="mb-0 fw-bold"><i class="bx bx-cut me-1"></i> Booked Services & Stylists</h6>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Service</th>
                                                <th>Stylist</th>
                                                <th>Duration</th>
                                                <th>Price</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${servicesHtml}
                                        </tbody>
                                    </table>
                                             <div class="row mb-3">
                                <div class="col-md-3">
                                    <div class="p-2 border rounded text-center">
                                        <small class="text-muted">Total Amount</small>
                                        <div class="fw-bold">₹${parseFloat(d.total_amount || 0).toFixed(2)}</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-2 border rounded text-center">
                                        <small class="text-muted">Discount</small>
                                        <div class="fw-bold text-danger">₹${parseFloat(d.discount || 0).toFixed(2)}</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-2 border rounded text-center bg-light">
                                        <small class="text-muted">Final Amount</small>
                                        <div class="fw-bold text-primary">₹${parseFloat(d.final_amount || d.amount || 0).toFixed(2)}</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-2 border rounded text-center bg-light">
                                        <small class="text-muted">Paid Amount</small>
                                        <div class="fw-bold text-success fs-6">₹${parseFloat(d.paid_amount || 0).toFixed(2)}</div>
                                    </div>
                                </div>
                            </div>

                            ${d.payment_type ? `
                                <div class="card shadow-none border bg-light mb-3">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-bold text-muted"><i class="bx bx-credit-card me-1"></i> Payment Mode</span>
                                            <span class="badge ${d.payment_type === 'split' ? 'bg-label-info' : 'bg-label-primary'} text-uppercase fs-7">${d.payment_type === 'split' ? 'SPLIT PAYMENT' : d.payment_type}</span>
                                        </div>
                                        ${d.payment_type === 'split' ? `
                                            <div class="row g-2 mt-1">
                                                <div class="col-6">
                                                    <div class="p-2 bg-white rounded border text-center">
                                                        <small class="text-muted d-block"><i class="bx bx-money me-1 text-success"></i>Cash Paid</small>
                                                        <strong class="text-success fs-6">₹${parseFloat(d.cash_amount || 0).toFixed(2)}</strong>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="p-2 bg-white rounded border text-center">
                                                        <small class="text-muted d-block"><i class="bx bx-qr-scan me-1 text-primary"></i>UPI Paid</small>
                                                        <strong class="text-primary fs-6">₹${parseFloat(d.upi_amount || 0).toFixed(2)}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                        ` : ''}
                                        ${d.upi_reference ? `
                                            <small class="text-muted d-block mt-2"><i class="bx bx-receipt me-1"></i>UPI Transaction Ref: <code class="text-primary">${d.upi_reference}</code></small>
                                        ` : ''}
                                    </div>
                                </div>
                            ` : ''}                            </div>
                            </div>

                            ${(d.credit_amount > 0 || d.debit_amount > 0 || d.customer_balance || balanceBadgesHtml) ? `
                                <div class="row mb-3">
                                    ${balanceBadgesHtml}
                                    ${d.customer_balance ? `
                                        <div class="col-md-6 mb-2">
                                            <div class="alert alert-info py-2 mb-0 text-center fw-bold">
                                                <i class="bx bx-wallet me-1"></i>Customer ${d.customer_balance.text}
                                            </div>
                                        </div>
                                    ` : ''}
                                </div>
                            ` : ''}

                            ${d.notes ? `
                                <div class="mb-2">
                                    <h6 class="text-muted mb-1">Notes</h6>
                                    <p class="mb-0 bg-light p-2 rounded">${d.notes}</p>
                                </div>
                            ` : ''}
                        `;
                        $('#viewApptBody').html(bodyHtml);
                    }
                },
                error: function() {
                    $('#viewApptBody').html('<div class="alert alert-danger mb-0">Failed to load appointment details.</div>');
                }
            });
        });

        $(document).on('click', '.delete-appointment', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: 'This appointment will be permanently deleted.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("admin.appointments.destroy", ":id") }}'.replace(':id', id),
                        method: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(res) {
                            if (res.success) {
                                showSuccessToast(res.message || 'Appointment deleted successfully.');
                                setTimeout(() => location.reload(), 1000);
                            }
                        },
                        error: function() {
                            showErrorToast('Failed to delete appointment.');
                        }
                    });
                }
            });
        });
    });
</script>
@endsection
