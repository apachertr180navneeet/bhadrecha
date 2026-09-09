@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bx bx-gift me-1"></i> {{ now()->format('F') }} Birthdays</h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-label-warning fs-6 px-3 py-2">{{ $birthdays->count() }} {{ Str::plural('Birthday', $birthdays->count()) }} This Month</span>
                        @if($birthdays->isNotEmpty())
                        <button type="button" class="btn btn-success btn-sm d-none" id="btnSendWhatsApp">
                            <i class="bx bxl-whatsapp me-1"></i> Send WhatsApp (<span id="selectedCount">0</span>)
                        </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if($birthdays->isEmpty())
                        <div class="text-center py-5">
                            <i class="bx bx-gift" style="font-size: 3rem; color: #94a3b8;"></i>
                            <p class="text-muted mt-3 mb-0">No birthdays this month</p>
                        </div>
                    @else
                        <div class="table-responsive text-nowrap">
                            <table class="table table-hover" id="birthdaysTable">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="selectAll">
                                            </div>
                                        </th>
                                        <th>S.No.</th>
                                        <th class="text-center" style="width: 70px;">Action</th>
                                        <th>Name</th>
                                        <th>Mobile</th>
                                        <th>Email</th>
                                        <th>Date of Birth</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($birthdays as $index => $customer)
                                    @php
                                        $isToday = \Carbon\Carbon::parse($customer->dob)->format('m-d') === $today;
                                    @endphp
                                    <tr @if($isToday) style="background-color: #fff8e1;" @endif>
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input customer-checkbox" type="checkbox"
                                                    data-name="{{ $customer->name }}"
                                                    data-mobile="{{ $customer->mobile }}"
                                                    {{ !$customer->mobile ? 'disabled' : '' }}>
                                            </div>
                                        </td>
                                        <td>{{ $index + 1 }}</td>
                                        <td class="text-center">
                                            @if($customer->mobile)
                                            <button type="button" class="btn btn-sm btn-outline-success btn-send-single"
                                                data-name="{{ $customer->name }}"
                                                data-mobile="{{ $customer->mobile }}"
                                                title="Send WhatsApp">
                                                <i class="bx bxl-whatsapp"></i>
                                            </button>
                                            @else
                                            <span class="text-muted small">No mobile</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-3">
                                                    <span class="avatar-initial rounded-circle {{ $isToday ? 'bg-label-warning' : 'bg-label-primary' }} d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-weight: 600;">
                                                        {{ strtoupper(substr($customer->name, 0, 2)) }}
                                                    </span>
                                                </div>
                                                <span class="fw-semibold">{{ $customer->name }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $customer->mobile ?? 'N/A' }}</td>
                                        <td>{{ $customer->email ?? 'N/A' }}</td>
                                        <td>{{ date('d M Y', strtotime($customer->dob)) }}</td>
                                        <td>
                                            @if($isToday)
                                                <span class="badge bg-warning"><i class="bx bx-cake me-1"></i> Today's Birthday!</span>
                                            @else
                                                <span class="badge bg-label-info">{{ date('d M', strtotime($customer->dob)) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- WhatsApp Message Modal -->
<div class="modal fade" id="whatsappModal" tabindex="-1" aria-labelledby="whatsappModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="whatsappModalLabel">
                    <i class="bx bxl-whatsapp me-1"></i> Send WhatsApp Message
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Sending to: <span id="recipientInfo" class="text-primary"></span></label>
                </div>
                <div class="mb-3">
                    <label for="whatsappMessage" class="form-label fw-semibold">Custom Message</label>
                    <textarea class="form-control" id="whatsappMessage" rows="4" placeholder="Type your birthday wish here...">Happy Birthday {name}! 🎂🎉 Wishing you a wonderful day filled with joy and happiness. We'd love to see you at The Infinity Salon. Have a great birthday!</textarea>
                    <div class="mt-2 d-flex flex-wrap gap-1 align-items-center">
                        <small class="text-muted me-1">Insert Emoji:</small>
                        <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-0 btn-emoji" data-emoji="🎂">🎂</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-0 btn-emoji" data-emoji="🎉">🎉</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-0 btn-emoji" data-emoji="🎁">🎁</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-0 btn-emoji" data-emoji="🎈">🎈</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-0 btn-emoji" data-emoji="🥳">🥳</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-0 btn-emoji" data-emoji="✨">✨</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-0 btn-emoji" data-emoji="💖">💖</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-0 btn-emoji" data-emoji="💈">💈</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-0 btn-emoji" data-emoji="💇‍♀️">💇‍♀️</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-0 btn-emoji" data-emoji="🌸">🌸</button>
                    </div>
                    <div class="form-text mt-1">
                        <i class="bx bx-info-circle me-1"></i> Use <code>{name}</code> to auto-insert customer's name.
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label fw-semibold small text-muted">Preview:</label>
                    <div class="border rounded p-3 bg-light" id="messagePreview" style="white-space: pre-wrap; font-size: 0.9rem;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="btnConfirmSend">
                    <i class="bx bxl-whatsapp me-1"></i> Send via WhatsApp
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    var selectedCustomers = [];
    var isSingleSend = false;

    // Select All checkbox
    $('#selectAll').on('change', function() {
        var isChecked = $(this).is(':checked');
        $('.customer-checkbox:not(:disabled)').prop('checked', isChecked);
        updateSelectedCount();
    });

    // Individual checkbox change
    $(document).on('change', '.customer-checkbox', function() {
        var total = $('.customer-checkbox:not(:disabled)').length;
        var checked = $('.customer-checkbox:checked').length;
        $('#selectAll').prop('checked', total === checked);
        $('#selectAll').prop('indeterminate', checked > 0 && checked < total);
        updateSelectedCount();
    });

    // Update selected count and toggle button
    function updateSelectedCount() {
        var count = $('.customer-checkbox:checked').length;
        $('#selectedCount').text(count);
        if (count > 0) {
            $('#btnSendWhatsApp').removeClass('d-none');
        } else {
            $('#btnSendWhatsApp').addClass('d-none');
        }
    }

    // Get selected customers data
    function getSelectedCustomers() {
        var customers = [];
        $('.customer-checkbox:checked').each(function() {
            customers.push({
                name: $(this).data('name'),
                mobile: $(this).data('mobile')
            });
        });
        return customers;
    }

    // Format phone number for WhatsApp (remove spaces, dashes, leading 0, add 91 country code if needed)
    function formatPhone(mobile) {
        var phone = String(mobile).replace(/[\s\-\(\)]/g, '');
        // Remove leading + if present
        if (phone.startsWith('+')) {
            phone = phone.substring(1);
        }
        // If starts with 0, remove it and add 91 (India)
        if (phone.startsWith('0')) {
            phone = '91' + phone.substring(1);
        }
        // If it's 10 digits (Indian number without country code), add 91
        if (phone.length === 10) {
            phone = '91' + phone;
        }
        return phone;
    }

    // Update message preview
    function updatePreview() {
        var msg = $('#whatsappMessage').val();
        var previewName = selectedCustomers.length === 1 ? selectedCustomers[0].name : selectedCustomers[0].name;
        var preview = msg.replace(/{name}/gi, previewName);
        $('#messagePreview').text(preview);
    }

    $('#whatsappMessage').on('input', function() {
        if (selectedCustomers.length > 0) {
            updatePreview();
        }
    });

    // Emoji insertion handler
    $(document).on('click', '.btn-emoji', function() {
        var emoji = $(this).data('emoji');
        var textarea = $('#whatsappMessage');
        var el = textarea[0];
        var startPos = el.selectionStart || textarea.val().length;
        var endPos = el.selectionEnd || textarea.val().length;
        var val = textarea.val();
        var newVal = val.substring(0, startPos) + emoji + val.substring(endPos);
        textarea.val(newVal);
        textarea.focus();
        var newPos = startPos + emoji.length;
        el.setSelectionRange(newPos, newPos);
        if (selectedCustomers.length > 0) {
            updatePreview();
        }
    });

    // Bulk send button click - open modal
    $('#btnSendWhatsApp').on('click', function() {
        selectedCustomers = getSelectedCustomers();
        isSingleSend = false;
        if (selectedCustomers.length === 0) return;

        if (selectedCustomers.length === 1) {
            $('#recipientInfo').text(selectedCustomers[0].name);
        } else {
            $('#recipientInfo').text(selectedCustomers.length + ' customers selected');
        }
        updatePreview();
        $('#whatsappModal').modal('show');
    });

    // Single send button click - open modal
    $(document).on('click', '.btn-send-single', function() {
        var name = $(this).data('name');
        var mobile = $(this).data('mobile');
        selectedCustomers = [{ name: name, mobile: mobile }];
        isSingleSend = true;

        $('#recipientInfo').text(name);
        updatePreview();
        $('#whatsappModal').modal('show');
    });

    // Confirm send - save log and open WhatsApp for each customer
    $('#btnConfirmSend').on('click', function() {
        var message = $('#whatsappMessage').val();
        if (!message.trim()) {
            Swal.fire('Error', 'Please enter a message', 'error');
            return;
        }

        var btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Sending...');

        // Build log data with personalized messages
        var logData = [];
        selectedCustomers.forEach(function(customer) {
            logData.push({
                name: customer.name,
                mobile: String(customer.mobile),
                message: message.replace(/{name}/gi, customer.name)
            });
        });

        // Save log via AJAX
        $.ajax({
            url: '{{ route("admin.customers.birthday-whatsapp-log") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                customers: logData
            },
            success: function(res) {
                btn.prop('disabled', false).html('<i class="bx bxl-whatsapp me-1"></i> Send via WhatsApp');
                $('#whatsappModal').modal('hide');

                // Open WhatsApp for each customer
                var delay = 0;
                selectedCustomers.forEach(function(customer) {
                    setTimeout(function() {
                        var personalMessage = message.replace(/{name}/gi, customer.name);
                        var phone = formatPhone(customer.mobile);
                        var url = 'https://wa.me/' + phone + '?text=' + encodeURIComponent(personalMessage);
                        window.open(url, '_blank');
                    }, delay);
                    delay += 1000;
                });

                // Show success notification
                setTimeout(function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'WhatsApp Opened!',
                        html: selectedCustomers.length === 1
                            ? 'WhatsApp opened for <b>' + selectedCustomers[0].name + '</b>. Message log saved.'
                            : 'WhatsApp opened for <b>' + selectedCustomers.length + '</b> customers. Message logs saved.',
                        timer: 4000,
                        showConfirmButton: true
                    });
                }, delay);
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="bx bxl-whatsapp me-1"></i> Send via WhatsApp');
                Swal.fire('Error', 'Failed to save message log. Please try again.', 'error');
            }
        });
    });
});
</script>
@endsection
