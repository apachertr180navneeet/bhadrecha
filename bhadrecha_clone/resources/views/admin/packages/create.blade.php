@extends('admin.layouts.app')

@section('style')
<style>
    .table-responsive.has-open-dropdown,
    .card-body.has-open-dropdown,
    .card.has-open-dropdown,
    .row.has-open-dropdown {
        position: relative !important;
        z-index: 9999 !important;
        overflow: visible !important;
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
</style>
@endsection

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Add Package</h4>
            <small class="text-muted">Create a new customer package</small>
        </div>
        <a href="{{ route('admin.packages.index') }}" class="btn btn-secondary">
            <i class="bx bx-arrow-back me-1"></i> Back to List
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.packages.store') }}" method="POST" id="addPackageForm">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Customer <span class="text-danger">*</span></label>
                        <div class="custom-ajax-wrapper" id="clientPickerWrapper">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bx bx-user text-muted"></i></span>
                                @php
                                    $oldCust = old('customer_id') ? $customers->firstWhere('id', old('customer_id')) : null;
                                    $oldCustText = $oldCust ? ($oldCust->name . ($oldCust->mobile ? ' (' . $oldCust->mobile . ')' : '')) : '';
                                @endphp
                                <input type="text" class="form-control @error('customer_id') is-invalid @enderror" id="client_search_input" placeholder="Type name or phone to search customer..." value="{{ $oldCustText }}" autocomplete="off" required>
                                <button type="button" class="btn btn-outline-secondary" id="clear_client_btn" style="display: {{ old('customer_id') ? 'inline-block' : 'none' }};"><i class="bx bx-x"></i></button>
                            </div>
                            <input type="hidden" name="customer_id" id="customer_id" value="{{ old('customer_id') }}" required>
                            <div class="custom-ajax-menu" id="client_results_menu"></div>
                        </div>
                        @error('customer_id') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Duration <span class="text-danger">*</span></label>
                        <select name="duration_id" id="duration_id" class="form-select" required>
                            <option value="" data-days="0">Select Duration</option>
                            @foreach($durations as $duration)
                                <option value="{{ $duration->id }}" data-days="{{ $duration->days }}" {{ old('duration_id') == $duration->id ? 'selected' : '' }}>
                                    {{ $duration->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('duration_id') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" id="start_date" class="form-control" value="{{ old('start_date', date('Y-m-d')) }}" required>
                        @error('start_date') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">End Date <span class="text-danger">*</span></label>
                        <input type="date" name="end_date" id="end_date" class="form-control" value="{{ old('end_date') }}" required>
                        @error('end_date') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-12 mb-4">
                        <div class="card bg-light border p-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0 text-primary"><i class="bx bx-cut me-1"></i> Select Services & Quantities <span class="text-danger">*</span></h6>
                            </div>

                            <div class="row g-2 align-items-center mb-3">
                                <div class="col-md-8">
                                    <div class="custom-ajax-wrapper" id="servicePickerWrapper">
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i class="bx bx-cut text-muted"></i></span>
                                            <input type="text" class="form-control" id="service_search_input" placeholder="Type name to search service to add..." autocomplete="off">
                                            <button type="button" class="btn btn-outline-secondary" id="clear_service_btn" style="display: none;"><i class="bx bx-x"></i></button>
                                        </div>
                                        <input type="hidden" id="selected_service_id" value="">
                                        <input type="hidden" id="selected_service_name" value="">
                                        <input type="hidden" id="selected_service_price" value="">
                                        <div class="custom-ajax-menu" id="service_results_menu"></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <button type="button" class="btn btn-primary w-100" id="addServiceRowBtn">
                                        <i class="bx bx-plus me-1"></i> Add Service to Package
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm align-middle bg-white mb-0" id="packageServicesTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 60px;" class="text-center">Action</th>
                                            <th>Service Name</th>
                                            <th style="width: 150px;">Price (₹)</th>
                                            <th style="width: 160px;">Quantity (Qty) <span class="text-danger">*</span></th>
                                            <th style="width: 160px;">Subtotal (₹)</th>
                                        </tr>
                                    </thead>
                                    <tbody id="packageServicesBody">
                                        <!-- Dynamic service rows added here -->
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <th></th>
                                            <th colspan="2" class="text-end fw-bold">Total Services & Amount:</th>
                                            <th id="totalServicesQtyDisplay" class="fw-bold text-primary fs-6">0</th>
                                            <th id="totalServicesAmountDisplay" class="fw-bold text-success fs-6">₹0.00</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <small class="text-muted mt-2 d-block"><i class="bx bx-info-circle me-1"></i> Search services and specify their quantities for this package (e.g. Hair Color: 2, Hair Cut: 4).</small>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Package Amount <span class="text-danger">*</span></label>
                        <input type="number" name="amount" id="amount" class="form-control" value="{{ old('amount', 0) }}" step="0.01" min="0" required>
                        @error('amount') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-12 mb-3 mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0 fw-bold">Payments <span class="text-danger">*</span></label>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addPaymentBtn">
                                <i class="bx bx-plus"></i> Add Payment
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm" id="paymentTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px; text-align: center;">Act</th>
                                        <th>Date</th>
                                        <th>Amount Paid</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-icon btn-danger remove-payment"><i class="bx bx-trash"></i></button>
                                        </td>
                                        <td><input type="date" name="payment_date[]" class="form-control form-control-sm payment-date" value="{{ date('Y-m-d') }}" required></td>
                                        <td><input type="number" name="payment_amount[]" class="form-control form-control-sm payment-amount" value="0" step="0.01" min="0" required></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Remaining Balance</label>
                        <input type="text" id="remaining" class="form-control" value="0.00" readonly style="background-color: #f8f9fa; font-weight: bold;">
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Save Package</button>
                    <a href="{{ route('admin.packages.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        function showCustomMenu(menu) {
            if (!menu || typeof menu.show !== 'function') return;
            $('.custom-ajax-menu').not(menu).hide();
            $('.has-open-dropdown').removeClass('has-open-dropdown');
            
            menu.show();
            menu.parents('.custom-ajax-wrapper, .table-responsive, .card-body, .card, .row').addClass('has-open-dropdown');
        }

        function hideCustomMenu() {
            $('.custom-ajax-menu').hide();
            $('.has-open-dropdown').removeClass('has-open-dropdown');
        }

        // Hide all menus when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.custom-ajax-wrapper').length) {
                hideCustomMenu();
            }
        });

        // ------------------ CUSTOMER AJAX SEARCH ------------------
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
                                    <div class="dropdown-item select-client-item" data-id="${c.id}" data-name="${c.name}" data-phone="${c.phone || ''}">
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
            const phone = $(this).data('phone');
            const displayText = phone ? `${name} (${phone})` : name;

            $('#customer_id').val(id);
            $('#client_search_input').val(displayText);
            $('#clear_client_btn').show();
            hideCustomMenu();
        });

        $('#clear_client_btn').on('click', function() {
            $('#customer_id').val('');
            $('#client_search_input').val('');
            $(this).hide();
        });

        // ------------------ SERVICE AJAX SEARCH ------------------
        let serviceSearchTimer;
        $('#service_search_input').on('focus input', function() {
            clearTimeout(serviceSearchTimer);
            const q = $(this).val();
            const menu = $('#service_results_menu');

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
                                    <div class="dropdown-item select-package-service-item" data-id="${s.id}" data-name="${s.name}" data-price="${s.price}">
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

        $(document).on('click', '.select-package-service-item', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const price = parseFloat($(this).data('price')) || 0;

            $('#selected_service_id').val(id);
            $('#selected_service_name').val(name);
            $('#selected_service_price').val(price);
            $('#service_search_input').val(`${name} (₹${price.toFixed(2)})`);
            $('#clear_service_btn').show();
            hideCustomMenu();
        });

        $('#clear_service_btn').on('click', function() {
            $('#selected_service_id').val('');
            $('#selected_service_name').val('');
            $('#selected_service_price').val('');
            $('#service_search_input').val('');
            $(this).hide();
        });

        function calculateRemaining() {
            let amount = parseFloat($('#amount').val()) || 0;
            let totalPaid = 0;
            
            $('.payment-amount').each(function() {
                totalPaid += parseFloat($(this).val()) || 0;
            });
            
            let remaining = amount - totalPaid;
            if (remaining < 0) remaining = 0;
            $('#remaining').val(remaining.toFixed(2));
        }

        // Bind events and calculate initially
        $('#amount').on('input keyup change', calculateRemaining);
        $(document).on('input keyup change', '.payment-amount', calculateRemaining);

        // Dynamic Services & Quantities Logic
        function updateServicesSummary() {
            let totalQty = 0;
            let totalAmount = 0;

            $('#packageServicesBody tr').each(function() {
                let price = parseFloat($(this).find('.service-price').val()) || 0;
                let qty = parseInt($(this).find('.service-qty').val()) || 1;
                let subtotal = price * qty;
                $(this).find('.service-subtotal').text('₹' + subtotal.toFixed(2));
                totalQty += qty;
                totalAmount += subtotal;
            });

            $('#totalServicesQtyDisplay').text(totalQty);
            $('#totalServicesAmountDisplay').text('₹' + totalAmount.toFixed(2));
            
            // Auto update Package Amount field
            if ($('#packageServicesBody tr').length > 0) {
                $('#amount').val(totalAmount.toFixed(2));
            }
            calculateRemaining();
        }

        $('#addServiceRowBtn').on('click', function() {
            let serviceId = $('#selected_service_id').val();
            let serviceName = $('#selected_service_name').val();
            let servicePrice = parseFloat($('#selected_service_price').val()) || 0;

            if (!serviceId) {
                alert('Please search and select a service to add.');
                return;
            }

            // Check if already added
            if ($(`#packageServicesBody tr[data-service-id="${serviceId}"]`).length > 0) {
                let existingQtyInput = $(`#packageServicesBody tr[data-service-id="${serviceId}"]`).find('.service-qty');
                existingQtyInput.val(parseInt(existingQtyInput.val()) + 1);
                updateServicesSummary();
                $('#clear_service_btn').click();
                return;
            }

            let rowHtml = `<tr data-service-id="${serviceId}">
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-icon btn-outline-danger remove-service-row"><i class="bx bx-trash"></i></button>
                </td>
                <td>
                    <strong>${serviceName}</strong>
                    <input type="hidden" name="service_ids[]" value="${serviceId}">
                </td>
                <td>
                    <input type="number" name="service_prices[]" class="form-control form-control-sm service-price" value="${servicePrice.toFixed(2)}" step="0.01" min="0" required>
                </td>
                <td>
                    <input type="number" name="service_quantities[]" class="form-control form-control-sm service-qty" value="1" min="1" required>
                </td>
                <td class="fw-bold text-success service-subtotal">
                    ₹${servicePrice.toFixed(2)}
                </td>
            </tr>`;

            $('#packageServicesBody').append(rowHtml);
            updateServicesSummary();
            
            // Reset search selection
            $('#clear_service_btn').click();
        });

        $(document).on('input keyup change', '.service-qty, .service-price', function() {
            updateServicesSummary();
        });

        $(document).on('click', '.remove-service-row', function() {
            $(this).closest('tr').remove();
            updateServicesSummary();
        });

        function calculateEndDate() {
            let months = parseInt($('#duration_id').find(':selected').data('days')) || 0;
            let startDate = $('#start_date').val();
            if (months > 0 && startDate) {
                let parts = startDate.split('-');
                let date = new Date(parts[0], parts[1] - 1, parts[2]);
                date.setMonth(date.getMonth() + months);
                
                let y = date.getFullYear();
                let m = String(date.getMonth() + 1).padStart(2, '0');
                let d = String(date.getDate()).padStart(2, '0');
                $('#end_date').val(`${y}-${m}-${d}`);
            }
        }
        $('#duration_id, #start_date').on('change', calculateEndDate);
        
        // Auto calculate on load if duration is set but end_date is empty (e.g. from old input)
        if ($('#duration_id').val() && !$('#end_date').val()) {
            calculateEndDate();
        }
        
        $('#addPaymentBtn').on('click', function() {
            let today = new Date().toISOString().split('T')[0];
            let row = `<tr>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-icon btn-danger remove-payment"><i class="bx bx-trash"></i></button>
                </td>
                <td><input type="date" name="payment_date[]" class="form-control form-control-sm payment-date" value="${today}" required></td>
                <td><input type="number" name="payment_amount[]" class="form-control form-control-sm payment-amount" value="0" step="0.01" min="0" required></td>
            </tr>`;
            $('#paymentTable tbody').append(row);
        });

        $(document).on('click', '.remove-payment', function() {
            if ($('#paymentTable tbody tr').length > 1) {
                $(this).closest('tr').remove();
                calculateRemaining();
            } else {
                alert('At least one payment row is required.');
            }
        });

        calculateRemaining();
    });
</script>
@endsection
