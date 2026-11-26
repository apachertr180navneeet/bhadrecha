@extends('admin.layouts.app')

@section('style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .services-table th {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #566a7f;
        background-color: #f8f9fa;
        padding: 10px 12px;
        border-bottom: 1px solid #e9ecef;
    }
    .services-table td {
        padding: 10px 12px;
        vertical-align: top;
        border-bottom: 1px solid #f1f3f5;
    }
    .table-responsive.has-open-dropdown,
    .card-body.has-open-dropdown,
    .card.has-open-dropdown,
    .row.has-open-dropdown {
        position: relative !important;
        z-index: 9999 !important;
        overflow: visible !important;
    }
    .service-row.has-open-dropdown {
        position: relative !important;
        z-index: 9999 !important;
    }
    .custom-ajax-wrapper.has-open-dropdown {
        position: relative !important;
        z-index: 9999 !important;
    }
    .custom-ajax-wrapper { position: relative; }
    .custom-ajax-menu {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 99999 !important;
        background: #ffffff !important;
        border: 1px solid rgba(197, 160, 89, 0.4);
        border-radius: 12px;
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.25) !important;
        max-height: 230px;
        overflow-y: auto;
        display: none;
    }
    .custom-ajax-menu .dropdown-item {
        padding: 8px 14px;
        cursor: pointer;
        border-bottom: 1px solid #f8f9fa;
        white-space: normal;
        background: #ffffff !important;
    }
    .custom-ajax-menu .dropdown-item:hover {
        background-color: rgba(197, 160, 89, 0.15) !important;
        color: #9e7d3b;
    }
    .stylist-tag {
        background: rgba(197, 160, 89, 0.15);
        color: #5c4314;
        border: 1px solid rgba(197, 160, 89, 0.35);
        border-radius: 6px;
        padding: 2px 8px;
        font-size: 0.8125rem;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-weight: 500;
    }
    .stylist-tag .remove-tag {
        cursor: pointer;
        font-size: 0.95rem;
        color: #7a5c1e;
    }
    .stylist-tag .remove-tag:hover {
        color: #d9534f;
    }
</style>
@endsection

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">
                Edit Appointment 
                <span class="badge bg-label-primary ms-2 fs-6">{{ $appointment->appointment_number ?? '#' . $appointment->id }}</span>
            </h4>
            <small class="text-muted">Update appointment details, services & stylists</small>
        </div>
        <a href="{{ route('admin.appointments.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Back to List
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.appointments.update', $appointment->id) }}" method="POST" id="editAppointmentForm">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-6 col-12 mb-md-0 mb-3">
                        <label class="form-label">Store <span class="text-danger">*</span></label>
                        <select class="form-select @error('store_id') is-invalid @enderror" name="store_id" id="store_id" required {{ (!$canViewAllStores && $selectedStoreId) ? 'disabled' : '' }}>
                            <option value="">Select Store</option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}" {{ (string)old('store_id', $appointment->store_id ?? $selectedStoreId) === (string)$store->id ? 'selected' : '' }}>{{ $store->store_name ?? $store->name }}</option>
                            @endforeach
                        </select>
                        @if(!$canViewAllStores && $selectedStoreId)
                            <input type="hidden" name="store_id" value="{{ $selectedStoreId }}">
                        @endif
                        @error('store_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 col-12">
                        <label class="form-label">Branch <span class="text-danger">*</span></label>
                        <select class="form-select @error('branch_id') is-invalid @enderror" name="branch_id" id="branch_id" required {{ (!$canViewAllBranches && $selectedBranchId) ? 'disabled' : '' }}>
                            <option value="">Select Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" data-store="{{ $branch->store_id }}" {{ (string)old('branch_id', $appointment->branch_id ?? $selectedBranchId) === (string)$branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        @if(!$canViewAllBranches && $selectedBranchId)
                            <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">
                        @endif
                        @error('branch_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4 col-12 mb-md-0 mb-3">
                        <label class="form-label">Client <span class="text-danger">*</span></label>
                        <div class="custom-ajax-wrapper" id="clientPickerWrapper">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bx bx-user text-muted"></i></span>
                                <input type="text" class="form-control @error('customer_id') is-invalid @enderror" id="client_search_input" placeholder="Type name or phone to search client..." value="{{ old('customer_id', $appointment->customer_id) ? ($appointment->customer->name ?? '') : '' }}" autocomplete="off" required>
                                <button type="button" class="btn btn-outline-secondary" id="clear_client_btn" style="display: {{ old('customer_id', $appointment->customer_id) ? 'inline-block' : 'none' }}"><i class="bx bx-x"></i></button>
                            </div>
                            <input type="hidden" name="customer_id" id="customer_id" value="{{ old('customer_id', $appointment->customer_id) }}" required>
                            <div class="custom-ajax-menu" id="client_results_menu"></div>
                        </div>
                        <div id="customerBalanceBadgeContainer" class="mt-2"></div>
                        @error('customer_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 col-12 mb-md-0 mb-3">
                        <label class="form-label">Date & Time <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('appointment_date') is-invalid @enderror" id="appointment_date_time" name="appointment_date" value="{{ old('appointment_date', \Carbon\Carbon::parse($appointment->appointment_date)->format('Y-m-d h:i A')) }}" placeholder="Select date & time" required>
                        @error('appointment_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 col-12">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" name="status" id="status" required>
                            <option value="pending" {{ old('status', $appointment->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="confirmed" {{ old('status', $appointment->status) == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="checked_in" {{ old('status', $appointment->status) == 'checked_in' ? 'selected' : '' }}>Checked In</option>
                            <option value="in_progress" {{ old('status', $appointment->status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ old('status', $appointment->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ old('status', $appointment->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            <option value="no_show" {{ old('status', $appointment->status) == 'no_show' ? 'selected' : '' }}>No Show</option>
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="card mb-4 border shadow-none">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                        <h6 class="mb-0 fw-bold"><i class="bx bx-cut me-1"></i> Services & Stylists</h6>
                        <button type="button" class="btn btn-sm btn-primary" id="addServiceRowBtn">
                            <i class="bx bx-plus me-1"></i> Add More Service
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="min-height: 180px;">
                            <table class="table table-borderless services-table mb-0">
                                <thead>
                                    <tr>
                                        <th style="min-width: 50px; width: 6%; text-align: center;">Action</th>
                                        <th style="min-width: 200px; width: 32%;">Service <span class="text-danger">*</span></th>
                                        <th style="min-width: 220px; width: 36%;">Stylist(s)</th>
                                        <th style="min-width: 110px; width: 14%;">Price (₹)</th>
                                        <th style="min-width: 100px; width: 12%;">Duration (min)</th>
                                    </tr>
                                </thead>
                                <tbody id="servicesContainer">
                                    @php
                                        $existingServices = $appointment->appointmentServices;
                                    @endphp
                                    @if($existingServices && $existingServices->count() > 0)
                                        @foreach($existingServices as $idx => $item)
                                            <tr class="service-row" data-index="{{ $idx }}">
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn" {{ $existingServices->count() > 1 ? '' : 'disabled' }} title="Remove Service">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </td>
                                                <td>
                                                    <div class="custom-ajax-wrapper service-picker-wrapper">
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text bg-white py-1 px-2"><i class="bx bx-cut text-muted"></i></span>
                                                            <input type="text" class="form-control service-search-input" placeholder="Search service..." value="{{ $item->service->service_name ?? '' }}" autocomplete="off" required>
                                                            <button type="button" class="btn btn-outline-secondary btn-sm clear-service-btn" style="display: {{ $item->service_id ? 'inline-block' : 'none' }};"><i class="bx bx-x"></i></button>
                                                        </div>
                                                        <input type="hidden" class="item-service" name="items[{{ $idx }}][service_id]" value="{{ $item->service_id }}" data-price="{{ $item->service->price ?? 0 }}" data-duration="{{ $item->service->duration ?? 0 }}" required>
                                                        <div class="custom-ajax-menu service-results-menu"></div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @php
                                                        $selectedStaffIds = $item->staff_ids ?? ($item->staff_id ? [$item->staff_id] : []);
                                                        $selectedStaffObjs = $staffs->whereIn('id', $selectedStaffIds);
                                                    @endphp
                                                    <div class="custom-ajax-wrapper stylist-picker-wrapper">
                                                        <div class="form-control form-control-sm d-flex flex-wrap align-items-center gap-1 stylist-box" style="min-height: 38px; cursor: pointer;">
                                                            <div class="selected-tags d-flex flex-wrap gap-1">
                                                                @foreach($selectedStaffObjs as $st)
                                                                    <span class="stylist-tag" data-id="{{ $st->id }}">
                                                                        {{ $st->full_name ?? $st->name }} <i class="bx bx-x remove-tag" data-id="{{ $st->id }}"></i>
                                                                    </span>
                                                                @endforeach
                                                            </div>
                                                            <input type="text" class="border-0 flex-grow-1 stylist-search-input bg-transparent shadow-none" placeholder="Search Stylist(s)..." style="outline: none; min-width: 80px; font-size: 0.85rem;" autocomplete="off">
                                                        </div>
                                                        <div class="hidden-staff-inputs">
                                                            @foreach($selectedStaffIds as $stId)
                                                                <input type="hidden" name="items[{{ $idx }}][staff_ids][]" value="{{ $stId }}">
                                                            @endforeach
                                                        </div>
                                                        <div class="custom-ajax-menu stylist-results-menu"></div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" min="0" class="form-control item-price" name="items[{{ $idx }}][price]" value="{{ number_format($item->price, 2, '.', '') }}" placeholder="0.00">
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control item-duration" name="items[{{ $idx }}][duration]" value="{{ $item->duration }}" placeholder="0" readonly>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr class="service-row" data-index="0">
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn" disabled title="At least one service is required">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </td>
                                            <td>
                                                <div class="custom-ajax-wrapper service-picker-wrapper">
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text bg-white py-1 px-2"><i class="bx bx-cut text-muted"></i></span>
                                                        <input type="text" class="form-control service-search-input" placeholder="Search service..." value="{{ $appointment->service->service_name ?? '' }}" autocomplete="off" required>
                                                        <button type="button" class="btn btn-outline-secondary btn-sm clear-service-btn" style="display: {{ $appointment->service_id ? 'inline-block' : 'none' }};"><i class="bx bx-x"></i></button>
                                                    </div>
                                                    <input type="hidden" class="item-service" name="items[0][service_id]" value="{{ $appointment->service_id }}" data-price="{{ $appointment->service->price ?? 0 }}" data-duration="{{ $appointment->service->duration ?? 0 }}" required>
                                                    <div class="custom-ajax-menu service-results-menu"></div>
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $selectedStaffIds = $appointment->staff_id ? [$appointment->staff_id] : [];
                                                    $selectedStaffObjs = $staffs->whereIn('id', $selectedStaffIds);
                                                @endphp
                                                <div class="custom-ajax-wrapper stylist-picker-wrapper">
                                                    <div class="form-control form-control-sm d-flex flex-wrap align-items-center gap-1 stylist-box" style="min-height: 38px; cursor: pointer;">
                                                        <div class="selected-tags d-flex flex-wrap gap-1">
                                                            @foreach($selectedStaffObjs as $st)
                                                                <span class="stylist-tag" data-id="{{ $st->id }}">
                                                                    {{ $st->full_name ?? $st->name }} <i class="bx bx-x remove-tag" data-id="{{ $st->id }}"></i>
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                        <input type="text" class="border-0 flex-grow-1 stylist-search-input bg-transparent shadow-none" placeholder="Search Stylist(s)..." style="outline: none; min-width: 80px; font-size: 0.85rem;" autocomplete="off">
                                                    </div>
                                                    <div class="hidden-staff-inputs">
                                                        @foreach($selectedStaffIds as $stId)
                                                            <input type="hidden" name="items[0][staff_ids][]" value="{{ $stId }}">
                                                        @endforeach
                                                    </div>
                                                    <div class="custom-ajax-menu stylist-results-menu"></div>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" min="0" class="form-control item-price" name="items[0][price]" value="{{ number_format($appointment->total_amount, 2, '.', '') }}" placeholder="0.00">
                                            </td>
                                            <td>
                                                <input type="number" class="form-control item-duration" name="items[0][duration]" value="{{ $appointment->service->duration ?? '' }}" placeholder="0" readonly>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-xl-3 col-md-6 col-12 mb-3">
                        <label class="form-label text-uppercase fw-bold small text-secondary">TOTAL AMOUNT (₹)</label>
                        <input type="number" step="0.01" min="0" class="form-control" name="total_amount" id="total_amount" value="{{ number_format($appointment->total_amount, 2, '.', '') }}" placeholder="0.00">
                    </div>
                    <div class="col-xl-3 col-md-6 col-12 mb-3">
                        <label class="form-label text-uppercase fw-bold small text-secondary">DISCOUNT (₹)</label>
                        <input type="number" step="0.01" min="0" class="form-control" name="discount" id="discount" value="{{ number_format($appointment->discount, 2, '.', '') }}" placeholder="0.00">
                    </div>
                    <div class="col-xl-3 col-md-6 col-12 mb-3">
                        <label class="form-label text-uppercase fw-bold small text-secondary">FINAL AMOUNT (₹)</label>
                        <input type="number" step="0.01" min="0" class="form-control" name="final_amount" id="final_amount" value="{{ number_format($appointment->final_amount, 2, '.', '') }}" placeholder="0.00">
                    </div>
                    <div class="col-xl-3 col-md-6 col-12 mb-3" id="paidAmountContainer">
                        <label class="form-label text-uppercase fw-bold small text-secondary">PAID AMOUNT (₹)</label>
                        <input type="number" step="0.01" min="0" class="form-control" name="paid_amount" id="paid_amount" value="{{ number_format($appointment->paid_amount ?? 0, 2, '.', '') }}" placeholder="0.00">
                    </div>
                </div>

                <div class="row mb-3 p-3 bg-light rounded border mx-0 align-items-center" id="paymentDetailsBox">
                    <div class="col-xl-4 col-md-6 col-12 mb-xl-0 mb-3">
                        <label class="form-label text-uppercase fw-bold small text-secondary">Payment Mode</label>
                        <select class="form-select" name="payment_type" id="edit_payment_type">
                            <option value="">Select Payment Mode</option>
                            <option value="cash" {{ old('payment_type', $appointment->payment_type) == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="upi" {{ old('payment_type', $appointment->payment_type) == 'upi' ? 'selected' : '' }}>UPI</option>
                            <option value="card" {{ old('payment_type', $appointment->payment_type) == 'card' ? 'selected' : '' }}>Card</option>
                            <option value="split" {{ old('payment_type', $appointment->payment_type) == 'split' ? 'selected' : '' }}>Split Payment (Cash + UPI)</option>
                        </select>
                    </div>
                    <div class="col-xl-4 col-md-6 col-12 mb-xl-0 mb-3 d-none" id="splitCashContainer">
                        <label class="form-label fw-semibold"><i class="bx bx-money me-1 text-success"></i>Cash Amount (₹)</label>
                        <input type="number" step="0.01" min="0" class="form-control" name="cash_amount" id="cash_amount" value="{{ number_format($appointment->cash_amount ?? 0, 2, '.', '') }}" placeholder="0.00">
                    </div>
                    <div class="col-xl-4 col-md-6 col-12 mb-xl-0 mb-3 d-none" id="splitUpiContainer">
                        <label class="form-label fw-semibold"><i class="bx bx-qr-scan me-1 text-primary"></i>UPI Amount (₹)</label>
                        <input type="number" step="0.01" min="0" class="form-control" name="upi_amount" id="upi_amount" value="{{ number_format($appointment->upi_amount ?? 0, 2, '.', '') }}" placeholder="0.00">
                    </div>
                    <div class="col-xl-4 col-md-6 col-12 mb-xl-0 mb-3 d-none" id="upiRefContainer">
                        <label class="form-label fw-semibold"><i class="bx bx-receipt me-1 text-info"></i>UPI Reference / Transaction ID</label>
                        <input type="text" class="form-control" name="upi_reference" id="upi_reference" value="{{ old('upi_reference', $appointment->upi_reference) }}" placeholder="e.g. 1234567890">
                    </div>
                    <div class="col-12 mt-2">
                        <div id="apptBalanceDifferenceHint" class="small fw-bold"></div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label text-uppercase fw-bold small text-secondary">NOTES</label>
                    <textarea class="form-control" name="notes" rows="3" placeholder="Optional notes...">{{ old('notes', $appointment->notes) }}</textarea>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.appointments.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">Update Appointment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    $(document).ready(function() {
        let itemIndex = {{ max(1, count($appointment->appointmentServices ?? [])) }};
        let customerPackageServices = {};

        function showCustomMenu(menu) {
            if (!menu || typeof menu.show !== 'function') return;
            $('.custom-ajax-menu').not(menu).hide();
            $('.has-open-dropdown').removeClass('has-open-dropdown');
            
            menu.show();
            menu.parents('.custom-ajax-wrapper, .service-row, .table-responsive, .card-body, .card, .row').addClass('has-open-dropdown');
        }

        function hideCustomMenu() {
            $('.custom-ajax-menu').hide();
            $('.has-open-dropdown').removeClass('has-open-dropdown');
        }

        if (window.setupDependentBranchSelect) {
            window.setupDependentBranchSelect('#store_id', '#branch_id');
        }

        $('#appointment_date_time').flatpickr({
            enableTime: true,
            dateFormat: 'Y-m-d h:i K',
            time_24hr: false
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('.custom-ajax-wrapper').length) {
                hideCustomMenu();
            }
        });

        let clientSearchTimer;
        $('#client_search_input').on('focus input', function() {
            clearTimeout(clientSearchTimer);
            const q = $(this).val();
            const menu = $('#client_results_menu');
            clientSearchTimer = setTimeout(function() {
                $.ajax({
                    url: '{{ route("admin.appointments.search-customers") }}',
                    method: 'GET',
                    data: { q: q },
                    success: function(data) {
                        menu.empty();
                        if (data.length === 0) menu.append('<div class="p-3 text-muted text-center small">No customers found</div>');
                        else {
                            data.forEach(function(c) {
                                menu.append(`<div class="dropdown-item select-client-item" data-id="${c.id}" data-name="${c.name}"><div class="fw-bold">${c.name}</div><small class="text-muted"><i class="bx bx-phone me-1"></i>${c.phone || 'No phone'}</small></div>`);
                            });
                        }
                        showCustomMenu(menu);
                    }
                });
            }, 200);
        });

        $(document).on('click', '.select-client-item', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            $('#customer_id').val(id);
            $('#client_search_input').val(name);
            $('#clear_client_btn').show();
            hideCustomMenu();
            fetchCustomerPackages(id);
            fetchCustomerBalance(id);
        });

        $('#clear_client_btn').on('click', function() {
            $('#customer_id').val('');
            $('#client_search_input').val('');
            $(this).hide();
            fetchCustomerPackages('');
            fetchCustomerBalance('');
        });

        let serviceSearchTimer;
        $(document).on('focus input', '.service-search-input', function() {
            clearTimeout(serviceSearchTimer);
            const input = $(this);
            const wrapper = input.closest('.service-picker-wrapper');
            const q = input.val();
            const menu = wrapper.find('.service-results-menu');
            serviceSearchTimer = setTimeout(function() {
                $.ajax({
                    url: '{{ route("admin.appointments.search-services") }}',
                    method: 'GET',
                    data: { q: q },
                    success: function(data) {
                        menu.empty();
                        if (data.length === 0) menu.append('<div class="p-3 text-muted text-center small">No services found</div>');
                        else {
                            data.forEach(function(s) {
                                menu.append(`<div class="dropdown-item select-service-item" data-id="${s.id}" data-name="${s.name}" data-price="${s.price}" data-duration="${s.duration}"><div class="d-flex justify-content-between align-items-center"><span class="fw-semibold">${s.name}</span><span class="fw-bold text-primary small">₹${parseFloat(s.price).toFixed(2)}</span></div><small class="text-muted"><i class="bx bx-time me-1"></i>${s.duration} min</small></div>`);
                            });
                        }
                        showCustomMenu(menu);
                    }
                });
            }, 200);
        });

        $(document).on('click', '.select-service-item', function() {
            const wrapper = $(this).closest('.service-picker-wrapper');
            const row = wrapper.closest('.service-row');
            const id = $(this).data('id');
            const name = $(this).data('name');
            const price = parseFloat($(this).data('price')) || 0;
            const duration = parseInt($(this).data('duration')) || 0;
            wrapper.find('.item-service').val(id).data('price', price).data('duration', duration);
            wrapper.find('.service-search-input').val(name);
            wrapper.find('.clear-service-btn').show();
            hideCustomMenu();
            row.find('.item-duration').val(duration > 0 ? duration : '');
            row.data('manual-price', false);
            updateAllRowsPackageStatus();
        });

        $(document).on('click', '.clear-service-btn', function() {
            const wrapper = $(this).closest('.service-picker-wrapper');
            const row = wrapper.closest('.service-row');
            wrapper.find('.item-service').val('');
            wrapper.find('.service-search-input').val('');
            $(this).hide();
            row.find('.item-duration').val('');
            row.find('.item-price').val('');
            row.find('.package-badge').remove();
            calculateTotals();
        });

        let stylistSearchTimer;
        $(document).on('click focus input', '.stylist-box, .stylist-search-input', function(e) {
            e.stopPropagation();
            const wrapper = $(this).closest('.stylist-picker-wrapper');
            const input = wrapper.find('.stylist-search-input');
            const q = input.val();
            const menu = wrapper.find('.stylist-results-menu');
            const storeId = $('#store_id').val();
            const branchId = $('#branch_id').val();

            showCustomMenu(menu);

            clearTimeout(stylistSearchTimer);
            stylistSearchTimer = setTimeout(function() {
                $.ajax({
                    url: '{{ route("admin.appointments.search-staffs") }}',
                    method: 'GET',
                    data: { q: q, store_id: storeId, branch_id: branchId },
                    success: function(data) {
                        menu.empty();
                        if (data.length === 0) menu.append('<div class="p-3 text-muted text-center small">No stylists found</div>');
                        else {
                            data.forEach(function(st) {
                                const isChecked = wrapper.find(`.hidden-staff-inputs input[value="${st.id}"]`).length > 0;
                                menu.append(`
                                    <label class="dropdown-item d-flex align-items-center gap-2 py-2 px-3 mb-0 select-stylist-item cursor-pointer">
                                        <input class="form-check-input stylist-checkbox flex-shrink-0 m-0" type="checkbox" value="${st.id}" data-name="${st.name}" id="st_${wrapper.closest('.service-row').data('index')}_${st.id}" ${isChecked ? 'checked' : ''}>
                                        <span class="cursor-pointer text-dark fw-medium fs-6">${st.name}</span>
                                    </label>
                                `);
                            });
                        }
                        showCustomMenu(menu);
                    }
                });
            }, 200);
        });

        $(document).on('change', '.stylist-checkbox', function(e) {
            e.stopPropagation();
            const wrapper = $(this).closest('.stylist-picker-wrapper');
            const rowIndex = wrapper.closest('.service-row').data('index');
            const id = $(this).val();
            const name = $(this).data('name');
            const tagsContainer = wrapper.find('.selected-tags');
            const inputsContainer = wrapper.find('.hidden-staff-inputs');
            if ($(this).is(':checked')) {
                if (inputsContainer.find(`input[value="${id}"]`).length === 0) {
                    inputsContainer.append(`<input type="hidden" name="items[${rowIndex}][staff_ids][]" value="${id}">`);
                    tagsContainer.append(`<span class="stylist-tag" data-id="${id}">${name} <i class="bx bx-x remove-tag" data-id="${id}"></i></span>`);
                }
            } else {
                inputsContainer.find(`input[value="${id}"]`).remove();
                tagsContainer.find(`.stylist-tag[data-id="${id}"]`).remove();
            }
        });

        $(document).on('click', '.stylist-tag .remove-tag', function(e) {
            e.stopPropagation();
            const id = $(this).data('id');
            const wrapper = $(this).closest('.stylist-picker-wrapper');
            wrapper.find(`.hidden-staff-inputs input[value="${id}"]`).remove();
            wrapper.find(`.stylist-checkbox[value="${id}"]`).prop('checked', false);
            $(this).closest('.stylist-tag').remove();
        });

        function fetchCustomerPackages(customerId) {
            if (!customerId) { customerPackageServices = {}; updateAllRowsPackageStatus(); return; }
            $.ajax({
                url: '{{ route("admin.appointments.customer-packages") }}',
                method: 'GET',
                data: { customer_id: customerId, exclude_appointment_id: {{ $appointment->id }} },
                success: function(res) { customerPackageServices = res.success ? res.covered_services : {}; updateAllRowsPackageStatus(); }
            });
        }

        function updateAllRowsPackageStatus() {
            const usedInForm = {};
            $('#servicesContainer .service-row').each(function() {
                const row = $(this);
                const selectEl = row.find('.item-service');
                const serviceId = selectEl.val();
                row.find('.package-badge').remove();
                const defaultPrice = parseFloat(selectEl.data('price')) || 0;
                const duration = parseInt(selectEl.data('duration')) || 0;
                if (duration > 0 && !row.find('.item-duration').val()) row.find('.item-duration').val(duration);
                if (serviceId && customerPackageServices[serviceId]) {
                    const totalRemaining = parseInt(customerPackageServices[serviceId].remaining_qty) || 0;
                    const alreadyUsed = usedInForm[serviceId] || 0;
                    const remainingForThisRow = totalRemaining - alreadyUsed;
                    if (remainingForThisRow > 0) {
                        usedInForm[serviceId] = alreadyUsed + 1;
                        row.find('.item-price').val('0.00');
                        row.find('.service-picker-wrapper').after(`<small class="text-success fw-bold package-badge d-block mt-1"><i class="bx bx-gift me-1"></i>Covered by Package (${remainingForThisRow} left)</small>`);
                    } else if (!row.data('manual-price')) row.find('.item-price').val(defaultPrice > 0 ? defaultPrice.toFixed(2) : '0.00');
                } else if (serviceId && !row.data('manual-price')) row.find('.item-price').val(defaultPrice > 0 ? defaultPrice.toFixed(2) : '0.00');
            });
            calculateTotals();
        }

        function fetchCustomerBalance(customerId) {
            if (!customerId) { $('#customerBalanceBadgeContainer').empty(); return; }
            $.ajax({
                url: '{{ route("admin.appointments.customer-balance") }}',
                method: 'GET',
                data: { customer_id: customerId },
                success: function(res) {
                    if (res.success && res.balance) {
                        const b = res.balance;
                        let html = `<span class="badge ${b.badge_class} fs-6 px-3 py-2"><i class="bx ${b.type === 'credit' ? 'bx-wallet' : 'bx-error-circle'} me-1"></i> Customer ${b.text}</span>`;
                        if (b.type === 'credit' && b.credit_balance > 0) html += `<small class="text-success fw-semibold d-block mt-1">Credit Balance (₹${parseFloat(b.credit_balance).toFixed(2)}) will auto-adjust.</small>`;
                        $('#customerBalanceBadgeContainer').html(html);
                    }
                }
            });
        }

        $('#addServiceRowBtn').on('click', function() {
            const newRow = `
                <tr class="service-row" data-index="${itemIndex}">
                    <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-row-btn" title="Remove Service"><i class="bx bx-trash"></i></button></td>
                    <td>
                        <div class="custom-ajax-wrapper service-picker-wrapper">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white py-1 px-2"><i class="bx bx-cut text-muted"></i></span>
                                <input type="text" class="form-control service-search-input" placeholder="Search service..." autocomplete="off" required>
                                <button type="button" class="btn btn-outline-secondary btn-sm clear-service-btn" style="display: none;"><i class="bx bx-x"></i></button>
                            </div>
                            <input type="hidden" class="item-service" name="items[${itemIndex}][service_id]" required>
                            <div class="custom-ajax-menu service-results-menu"></div>
                        </div>
                    </td>
                    <td>
                        <div class="custom-ajax-wrapper stylist-picker-wrapper">
                            <div class="form-control form-control-sm d-flex flex-wrap align-items-center gap-1 stylist-box" style="min-height: 38px; cursor: pointer;">
                                <div class="selected-tags d-flex flex-wrap gap-1"></div>
                                <input type="text" class="border-0 flex-grow-1 stylist-search-input bg-transparent shadow-none" placeholder="Search Stylist(s)..." style="outline: none; min-width: 80px; font-size: 0.85rem;" autocomplete="off">
                            </div>
                            <div class="hidden-staff-inputs"></div>
                            <div class="custom-ajax-menu stylist-results-menu"></div>
                        </div>
                    </td>
                    <td><input type="number" step="0.01" min="0" class="form-control item-price" name="items[${itemIndex}][price]" placeholder="0.00"></td>
                    <td><input type="number" class="form-control item-duration" name="items[${itemIndex}][duration]" placeholder="0" readonly></td>
                </tr>
            `;
            $('#servicesContainer').append(newRow);
            itemIndex++;
            updateRemoveButtons();
        });

        $(document).on('click', '.remove-row-btn', function() {
            if ($(this).prop('disabled')) return;
            $(this).closest('.service-row').remove();
            updateRemoveButtons();
            calculateTotals();
        });

        function updateRemoveButtons() {
            const rows = $('#servicesContainer .service-row');
            $('.remove-row-btn').prop('disabled', rows.length <= 1);
        }

        $(document).on('input', '.item-price', function() { $(this).closest('.service-row').data('manual-price', true); calculateTotals(); });
        $('#discount, #paid_amount').on('input', function() { calculateFinalAmount(); calculateBalanceDifference(); });

        function calculateTotals() {
            let sum = 0;
            $('.item-price').each(function() { sum += parseFloat($(this).val()) || 0; });
            $('#total_amount').val(sum.toFixed(2));
            calculateFinalAmount();
        }

        function calculateFinalAmount() {
            const final = Math.max(0, (parseFloat($('#total_amount').val()) || 0) - (parseFloat($('#discount').val()) || 0));
            $('#final_amount').val(final.toFixed(2));
            calculateBalanceDifference();
        }

        function calculateBalanceDifference() {
            const diff = (parseFloat($('#paid_amount').val()) || 0) - (parseFloat($('#final_amount').val()) || 0);
            if ($('#paid_amount').val() === '') $('#apptBalanceDifferenceHint').empty();
            else $('#apptBalanceDifferenceHint').html(diff > 0 ? `<span class="text-success"><i class="bx bx-plus-circle me-1"></i>Credit: ₹${diff.toFixed(2)}</span>` : (diff < 0 ? `<span class="text-danger"><i class="bx bx-minus-circle me-1"></i>Due: ₹${Math.abs(diff).toFixed(2)}</span>` : `<span class="text-secondary"><i class="bx bx-check-circle me-1"></i>Settled</span>`));
        }

        function togglePaymentModeFields() {
            const pType = $('#edit_payment_type').val();
            $('#paidAmountContainer').toggleClass('d-none', pType === 'split');
            $('#splitCashContainer, #splitUpiContainer, #upiRefContainer').toggleClass('d-none', pType !== 'split');
            if (pType !== 'split') $('#upiRefContainer').toggleClass('d-none', pType !== 'upi');
        }

        $('#edit_payment_type').on('change', togglePaymentModeFields);
        $('#cash_amount, #upi_amount').on('input', function() {
            const total = (parseFloat($('#cash_amount').val()) || 0) + (parseFloat($('#upi_amount').val()) || 0);
            $('#paid_amount').val(total.toFixed(2));
            calculateBalanceDifference();
        });

        togglePaymentModeFields();
        const initCust = $('#customer_id').val();
        if (initCust) { fetchCustomerPackages(initCust); fetchCustomerBalance(initCust); }
        calculateBalanceDifference();
    });
</script>
@endsection
