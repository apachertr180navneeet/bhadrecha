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
            <h4 class="fw-bold mb-0">Add New Appointment</h4>
            <small class="text-muted">Create a new customer appointment with multiple services & stylists</small>
        </div>
        <a href="{{ route('admin.appointments.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Back to List
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.appointments.store') }}" method="POST" id="addAppointmentForm">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6 col-12 mb-md-0 mb-3">
                        <label class="form-label">Store <span class="text-danger">*</span></label>
                        <select class="form-select @error('store_id') is-invalid @enderror" name="store_id" id="store_id" required {{ (!$canViewAllStores && $selectedStoreId) ? 'disabled' : '' }}>
                            <option value="">Select Store</option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}" {{ (string)old('store_id', $selectedStoreId ?? auth()->user()->store_id) === (string)$store->id ? 'selected' : '' }}>{{ $store->store_name ?? $store->name }}</option>
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
                                <option value="{{ $branch->id }}" data-store="{{ $branch->store_id }}" {{ (string)old('branch_id', $selectedBranchId ?? auth()->user()->branch_id) === (string)$branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        @if(!$canViewAllBranches && $selectedBranchId)
                            <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">
                        @endif
                        @error('branch_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6 col-12 mb-md-0 mb-3">
                        <label class="form-label">Client <span class="text-danger">*</span></label>
                        <div class="custom-ajax-wrapper" id="clientPickerWrapper">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bx bx-user text-muted"></i></span>
                                <input type="text" class="form-control @error('customer_id') is-invalid @enderror" id="client_search_input" placeholder="Type name or phone to search client..." value="{{ old('customer_id') ? ($customers->firstWhere('id', old('customer_id'))->name ?? '') : '' }}" autocomplete="off" required>
                                <button type="button" class="btn btn-outline-secondary" id="clear_client_btn" style="display: {{ old('customer_id') ? 'inline-block' : 'none' }}"><i class="bx bx-x"></i></button>
                            </div>
                            <input type="hidden" name="customer_id" id="customer_id" value="{{ old('customer_id') }}" required>
                            <div class="custom-ajax-menu" id="client_results_menu"></div>
                        </div>
                        <div id="customerBalanceBadgeContainer" class="mt-2"></div>
                        @error('customer_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 col-12">
                        <label class="form-label">Date & Time <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('appointment_date') is-invalid @enderror" id="appointment_date_time" name="appointment_date" value="{{ old('appointment_date') }}" placeholder="Select date & time" required>
                        @error('appointment_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <!-- Multi-Services & Staff Table Section -->
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
                                        <th style="min-width: 200px; width: 32%;">Service <span class="text-danger">*</span></th>
                                        <th style="min-width: 220px; width: 36%;">Stylist(s)</th>
                                        <th style="min-width: 110px; width: 14%;">Price (₹)</th>
                                        <th style="min-width: 100px; width: 12%;">Duration (min)</th>
                                        <th style="min-width: 50px; width: 6%; text-align: center;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="servicesContainer">
                                    <!-- Default Service Row 0 -->
                                    <tr class="service-row" data-index="0">
                                        <td>
                                            <div class="custom-ajax-wrapper service-picker-wrapper">
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-white py-1 px-2"><i class="bx bx-cut text-muted"></i></span>
                                                    <input type="text" class="form-control service-search-input" placeholder="Search service..." autocomplete="off" required>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm clear-service-btn" style="display: none;"><i class="bx bx-x"></i></button>
                                                </div>
                                                <input type="hidden" class="item-service" name="items[0][service_id]" required>
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
                                        <td>
                                            <input type="number" step="0.01" min="0" class="form-control item-price" name="items[0][price]" placeholder="0.00">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control item-duration" name="items[0][duration]" placeholder="0" readonly>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn" disabled title="At least one service is required">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Amounts Horizontal Row (4 Columns Side-by-Side on Desktop, 2x2 on Tablet) -->
                <div class="row mb-3">
                    <div class="col-xl-3 col-md-6 col-12 mb-3">
                        <label class="form-label text-uppercase fw-bold small text-secondary">TOTAL AMOUNT (₹)</label>
                        <input type="number" step="0.01" min="0" class="form-control @error('total_amount') is-invalid @enderror" name="total_amount" id="total_amount" value="{{ old('total_amount') }}" placeholder="0.00">
                        @error('total_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-xl-3 col-md-6 col-12 mb-3">
                        <label class="form-label text-uppercase fw-bold small text-secondary">DISCOUNT (₹)</label>
                        <input type="number" step="0.01" min="0" class="form-control @error('discount') is-invalid @enderror" name="discount" id="discount" value="{{ old('discount', '0.00') }}" placeholder="0.00">
                        @error('discount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-xl-3 col-md-6 col-12 mb-3">
                        <label class="form-label text-uppercase fw-bold small text-secondary">FINAL AMOUNT (₹)</label>
                        <input type="number" step="0.01" min="0" class="form-control @error('final_amount') is-invalid @enderror" name="final_amount" id="final_amount" value="{{ old('final_amount') }}" placeholder="0.00">
                        @error('final_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-xl-3 col-md-6 col-12 mb-3" id="paidAmountContainer">
                        <label class="form-label text-uppercase fw-bold small text-secondary">PAID AMOUNT (₹)</label>
                        <input type="number" step="0.01" min="0" class="form-control @error('paid_amount') is-invalid @enderror" name="paid_amount" id="paid_amount" value="{{ old('paid_amount') }}" placeholder="0.00">
                        @error('paid_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <!-- Payment Details Container -->
                <div class="row mb-3 p-3 bg-light rounded border mx-0 align-items-center" id="paymentDetailsBox">
                    <div class="col-xl-4 col-md-6 col-12 mb-xl-0 mb-3">
                        <label class="form-label text-uppercase fw-bold small text-secondary">Payment Mode</label>
                        <select class="form-select @error('payment_type') is-invalid @enderror" name="payment_type" id="create_payment_type">
                            <option value="">Select Payment Mode</option>
                            <option value="cash" {{ old('payment_type') == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="upi" {{ old('payment_type') == 'upi' ? 'selected' : '' }}>UPI</option>
                            <option value="card" {{ old('payment_type') == 'card' ? 'selected' : '' }}>Card</option>
                            <option value="split" {{ old('payment_type') == 'split' ? 'selected' : '' }}>Split Payment (Cash + UPI)</option>
                        </select>
                        @error('payment_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Split Payment Inputs -->
                    <div class="col-xl-4 col-md-6 col-12 mb-xl-0 mb-3 d-none" id="splitCashContainer">
                        <label class="form-label fw-semibold"><i class="bx bx-money me-1 text-success"></i>Cash Amount (₹)</label>
                        <input type="number" step="0.01" min="0" class="form-control @error('cash_amount') is-invalid @enderror" name="cash_amount" id="cash_amount" value="{{ old('cash_amount') }}" placeholder="0.00">
                        @error('cash_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-xl-4 col-md-6 col-12 mb-xl-0 mb-3 d-none" id="splitUpiContainer">
                        <label class="form-label fw-semibold"><i class="bx bx-qr-scan me-1 text-primary"></i>UPI Amount (₹)</label>
                        <input type="number" step="0.01" min="0" class="form-control @error('upi_amount') is-invalid @enderror" name="upi_amount" id="upi_amount" value="{{ old('upi_amount') }}" placeholder="0.00">
                        @error('upi_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- UPI Reference Field (Shared for UPI & Split) -->
                    <div class="col-xl-4 col-md-6 col-12 mb-xl-0 mb-3 d-none" id="upiRefContainer">
                        <label class="form-label fw-semibold"><i class="bx bx-receipt me-1 text-info"></i>UPI Reference / Transaction ID</label>
                        <input type="text" class="form-control @error('upi_reference') is-invalid @enderror" name="upi_reference" id="upi_reference" value="{{ old('upi_reference') }}" placeholder="e.g. 1234567890">
                        @error('upi_reference') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 mt-2">
                        <div id="apptBalanceDifferenceHint" class="small fw-bold"></div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label text-uppercase fw-bold small text-secondary">NOTES</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror" name="notes" rows="3" placeholder="Optional notes...">{{ old('notes') }}</textarea>
                    @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.appointments.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-dark px-4">Save Appointment</button>
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
        let itemIndex = 1;
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

        // Hide all menus when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.custom-ajax-wrapper').length) {
                hideCustomMenu();
            }
        });

        // ------------------ CLIENT AJAX SEARCH ------------------
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
                        if (data.length === 0) {
                            menu.append('<div class="p-3 text-muted text-center small">No customers found</div>');
                        } else {
                            data.forEach(function(c) {
                                menu.append(`
                                    <div class="dropdown-item select-client-item" data-id="${c.id}" data-name="${c.name}">
                                        <div class="fw-bold">${c.name}</div>
                                        <small class="text-muted"><i class="bx bx-phone me-1"></i>${c.phone || 'No phone'}</small>
                                    </div>
                                `);
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

        // ------------------ SERVICE AJAX SEARCH ------------------
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
                        if (data.length === 0) {
                            menu.append('<div class="p-3 text-muted text-center small">No services found</div>');
                        } else {
                            data.forEach(function(s) {
                                menu.append(`
                                    <div class="dropdown-item select-service-item" data-id="${s.id}" data-name="${s.name}" data-price="${s.price}" data-duration="${s.duration}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-semibold">${s.name}</span>
                                            <span class="fw-bold text-primary small">₹${parseFloat(s.price).toFixed(2)}</span>
                                        </div>
                                        <small class="text-muted"><i class="bx bx-time me-1"></i>${s.duration} min</small>
                                    </div>
                                `);
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

            wrapper.find('.item-service').val(id);
            wrapper.find('.service-search-input').val(name);
            wrapper.find('.clear-service-btn').show();
            hideCustomMenu();

            row.find('.item-service').data('price', price).data('duration', duration);
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

        // ------------------ STYLIST MULTI-SELECT AJAX SEARCH ------------------
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
                        if (data.length === 0) {
                            menu.append('<div class="p-3 text-muted text-center small">No stylists found</div>');
                        } else {
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
                    tagsContainer.append(`
                        <span class="stylist-tag" data-id="${id}">
                            ${name} <i class="bx bx-x remove-tag" data-id="${id}"></i>
                        </span>
                    `);
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

        // ------------------ CUSTOMER PACKAGES & BALANCE ------------------
        function fetchCustomerPackages(customerId) {
            if (!customerId) {
                customerPackageServices = {};
                updateAllRowsPackageStatus();
                return;
            }

            $.ajax({
                url: '{{ route("admin.appointments.customer-packages") }}',
                method: 'GET',
                data: { customer_id: customerId },
                success: function(res) {
                    if (res.success && res.covered_services) {
                        customerPackageServices = res.covered_services;
                    } else {
                        customerPackageServices = {};
                    }
                    updateAllRowsPackageStatus();
                },
                error: function() {
                    customerPackageServices = {};
                    updateAllRowsPackageStatus();
                }
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
                row.find('.item-duration').val(duration > 0 ? duration : '');

                if (serviceId && customerPackageServices[serviceId]) {
                    const totalRemaining = parseInt(customerPackageServices[serviceId].remaining_qty) || 0;
                    const alreadyUsed = usedInForm[serviceId] || 0;
                    const remainingForThisRow = totalRemaining - alreadyUsed;

                    if (remainingForThisRow > 0) {
                        usedInForm[serviceId] = alreadyUsed + 1;
                        row.find('.item-price').val('0.00');
                        row.find('.service-picker-wrapper').after(`<small class="text-success fw-bold package-badge d-block mt-1"><i class="bx bx-gift me-1"></i>Covered by Package (${remainingForThisRow} left)</small>`);
                    } else {
                        if (!row.data('manual-price')) {
                            row.find('.item-price').val(defaultPrice > 0 ? defaultPrice.toFixed(2) : '0.00');
                        }
                    }
                } else if (serviceId) {
                    if (!row.data('manual-price')) {
                        row.find('.item-price').val(defaultPrice > 0 ? defaultPrice.toFixed(2) : '0.00');
                    }
                } else {
                    row.find('.item-price').val('');
                }
            });

            calculateTotals();
        }

        function fetchCustomerBalance(customerId) {
            if (!customerId) {
                $('#customerBalanceBadgeContainer').empty();
                return;
            }

            $.ajax({
                url: '{{ route("admin.appointments.customer-balance") }}',
                method: 'GET',
                data: { customer_id: customerId },
                success: function(res) {
                    if (res.success && res.balance) {
                        const b = res.balance;
                        const icon = b.type === 'credit' ? 'bx-wallet' : (b.type === 'debit' ? 'bx-error-circle' : 'bx-check-circle');
                        let html = `<span class="badge ${b.badge_class} fs-6 px-3 py-2"><i class="bx ${icon} me-1"></i> Customer ${b.text}</span>`;
                        if (b.type === 'credit' && b.credit_balance > 0) {
                            html += `<small class="text-success fw-semibold d-block mt-1"><i class="bx bx-info-circle me-1"></i>Customer Credit Balance (₹${parseFloat(b.credit_balance).toFixed(2)}) will automatically adjust against any due amount.</small>`;
                        }
                        $('#customerBalanceBadgeContainer').html(html);
                    } else {
                        $('#customerBalanceBadgeContainer').empty();
                    }
                },
                error: function() {
                    $('#customerBalanceBadgeContainer').empty();
                }
            });
        }

        // Add service row
        $('#addServiceRowBtn').on('click', function() {
            const newRow = `
                <tr class="service-row" data-index="${itemIndex}">
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
                    <td>
                        <input type="number" step="0.01" min="0" class="form-control item-price" name="items[${itemIndex}][price]" placeholder="0.00">
                    </td>
                    <td>
                        <input type="number" class="form-control item-duration" name="items[${itemIndex}][duration]" placeholder="0" readonly>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn" title="Remove Service">
                            <i class="bx bx-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#servicesContainer').append(newRow);
            itemIndex++;
            updateRemoveButtons();
            updateAllRowsPackageStatus();
        });

        // Remove service row
        $(document).on('click', '.remove-row-btn', function() {
            if ($(this).prop('disabled')) return;
            $(this).closest('.service-row').remove();
            updateRemoveButtons();
            updateAllRowsPackageStatus();
        });

        function updateRemoveButtons() {
            const rows = $('#servicesContainer .service-row');
            if (rows.length > 1) {
                $('.remove-row-btn').prop('disabled', false).attr('title', 'Remove Service');
            } else {
                $('.remove-row-btn').prop('disabled', true).attr('title', 'At least one service is required');
            }
        }

        // On item price input change
        $(document).on('input', '.item-price', function() {
            const row = $(this).closest('.service-row');
            row.data('manual-price', true);
            calculateTotals();
        });

        $('#discount, #paid_amount').on('input', function() {
            calculateFinalAmount();
            calculateBalanceDifference();
        });

        function calculateTotals() {
            let sumTotal = 0;
            $('.item-price').each(function() {
                const val = parseFloat($(this).val()) || 0;
                sumTotal += val;
            });

            $('#total_amount').val(sumTotal.toFixed(2));
            calculateFinalAmount();
        }

        function calculateFinalAmount() {
            const total = parseFloat($('#total_amount').val()) || 0;
            const discount = parseFloat($('#discount').val()) || 0;
            const finalAmt = Math.max(0, total - discount);
            $('#final_amount').val(finalAmt.toFixed(2));
            calculateBalanceDifference();
        }

        function calculateBalanceDifference() {
            const finalAmt = parseFloat($('#final_amount').val()) || 0;
            const paidVal = $('#paid_amount').val();
            if (paidVal === '' || paidVal === null) {
                $('#apptBalanceDifferenceHint').empty();
                return;
            }
            const paidAmt = parseFloat(paidVal) || 0;
            const diff = paidAmt - finalAmt;
            if (diff > 0) {
                $('#apptBalanceDifferenceHint').html(`<span class="text-success"><i class="bx bx-plus-circle me-1"></i>Credit (Advance Generated): ₹${diff.toFixed(2)}</span>`);
            } else if (diff < 0) {
                $('#apptBalanceDifferenceHint').html(`<span class="text-danger"><i class="bx bx-minus-circle me-1"></i>Debit (Outstanding Due): ₹${Math.abs(diff).toFixed(2)}</span>`);
            } else {
                $('#apptBalanceDifferenceHint').html(`<span class="text-secondary"><i class="bx bx-check-circle me-1"></i>Exact Paid Amount (₹0.00 Difference)</span>`);
            }
        }

        function togglePaymentModeFields() {
            const pType = $('#create_payment_type').val();
            if (pType === 'split') {
                $('#paidAmountContainer').addClass('d-none');
                $('#splitCashContainer, #splitUpiContainer, #upiRefContainer').removeClass('d-none');
                calculateSplitTotal();
            } else if (pType === 'upi') {
                $('#paidAmountContainer, #upiRefContainer').removeClass('d-none');
                $('#splitCashContainer, #splitUpiContainer').addClass('d-none');
                calculateBalanceDifference();
            } else {
                $('#paidAmountContainer').removeClass('d-none');
                $('#splitCashContainer, #splitUpiContainer, #upiRefContainer').addClass('d-none');
                calculateBalanceDifference();
            }
        }

        function calculateSplitTotal() {
            if ($('#create_payment_type').val() === 'split') {
                const cashAmt = parseFloat($('#cash_amount').val()) || 0;
                const upiAmt = parseFloat($('#upi_amount').val()) || 0;
                const totalPaid = cashAmt + upiAmt;
                $('#paid_amount').val(totalPaid > 0 ? totalPaid.toFixed(2) : '0.00');
                calculateBalanceDifference();
            }
        }

        $('#create_payment_type').on('change', togglePaymentModeFields);
        $('#cash_amount, #upi_amount').on('input', calculateSplitTotal);
        togglePaymentModeFields();

        // Initial check on load
        const initCust = $('#customer_id').val();
        if (initCust) {
            fetchCustomerPackages(initCust);
            fetchCustomerBalance(initCust);
        }
    });
</script>
@endsection
