@extends('admin.layouts.app')

@section('style')
<style>
    .custom-ajax-wrapper {
        position: relative;
    }
    .custom-ajax-menu {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 1050 !important;
        background: #ffffff !important;
        border: 1px solid rgba(0, 0, 0, 0.15);
        border-radius: 8px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15) !important;
        max-height: 230px;
        overflow-y: auto;
        display: none;
    }
    .custom-ajax-menu .dropdown-item {
        padding: 10px 14px;
        cursor: pointer;
        border-bottom: 1px solid #f8f9fa;
        white-space: normal;
    }
    .custom-ajax-menu .dropdown-item:hover {
        background-color: rgba(105, 108, 255, 0.08) !important;
        color: #696cff;
    }
</style>
@endsection

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="row">
        <!-- Send WhatsApp Form -->
        <div class="col-lg-5 mb-4 mb-lg-0">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Send WhatsApp</h5>
                    <small class="text-muted">Compose and send WhatsApp messages to customers</small>
                </div>
                <div class="card-body">
                    <form id="whatsappForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="client_search_input">Customer <span class="text-danger">*</span></label>
                            <div class="custom-ajax-wrapper" id="clientPickerWrapper">
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bx bx-user text-muted"></i></span>
                                    <input type="text" class="form-control" id="client_search_input" placeholder="Type customer name or mobile to search..." autocomplete="off" required>
                                    <button type="button" class="btn btn-outline-secondary" id="clear_client_btn" style="display: none;"><i class="bx bx-x"></i></button>
                                </div>
                                <input type="hidden" name="customer_id" id="customer_id" required>
                                <input type="hidden" id="customer_mobile">
                                <div class="custom-ajax-menu" id="client_results_menu"></div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="waType">Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="type" id="waType" required>
                                <option value="">Select Type</option>
                                <option value="promotional">Promotional</option>
                                <option value="transactional">Transactional</option>
                                <option value="reminder">Reminder</option>
                                <option value="followup">Follow Up</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="waMessage">Message <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="message" id="waMessage" rows="5" placeholder="Type your message here..." required></textarea>
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
                            </div>
                            <div class="invalid-feedback"></div>
                            <small class="text-muted" id="waCharCount">0 characters</small>
                        </div>
                        <button type="submit" class="btn btn-success w-100" id="waSendBtn">
                            <i class="bx bxl-whatsapp me-1"></i> Send WhatsApp
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- WhatsApp Logs -->
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">WhatsApp Logs</h5>
                    <small class="text-muted">History of sent WhatsApp messages</small>
                </div>
                <div class="table-responsive text-nowrap">
<table class="table table-hover" id="waLogsTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Mobile</th>
                                <th>Message</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y h:i A') }}</td>
                                <td class="fw-semibold">{{ $log->customer->name ?? 'N/A' }}</td>
                                <td>{{ $log->mobile ?? $log->customer->mobile ?? 'N/A' }}</td>
                                <td>{{ Str::limit($log->message, 40) }}</td>
                                <td><span class="badge bg-label-info">{{ ucfirst($log->type) }}</span></td>
                                <td>
                                    @if($log->status == 'sent')
                                        <span class="badge bg-label-success">Sent</span>
                                    @elseif($log->status == 'failed')
                                        <span class="badge bg-label-danger">Failed</span>
                                    @else
                                        <span class="badge bg-label-warning">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->whatsapp_url)
                                        <a href="{{ $log->whatsapp_url }}" target="_blank" class="btn btn-sm btn-success px-2 py-1" title="Open WhatsApp Chat">
                                            <i class="bx bxl-whatsapp me-1"></i> Send
                                        </a>
                                    @else
                                        <span class="text-muted small">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $logs->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    function showCustomMenu(menuElem) {
        if (!menuElem || typeof menuElem.show !== 'function') return;
        menuElem.show();
    }
    function hideCustomMenu() {
        $('.custom-ajax-menu').hide();
    }

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
                url: '{{ route("admin.customers.ajax-search") }}',
                method: 'GET',
                data: { q: q },
                success: function(data) {
                    menu.empty();
                    if (!data || data.length === 0) {
                        menu.append('<div class="p-3 text-muted text-center small">No customers found</div>');
                    } else {
                        data.forEach(function(c) {
                            menu.append(`
                                <div class="dropdown-item select-client-item" data-id="${c.id}" data-name="${c.name}" data-mobile="${c.mobile || ''}">
                                    <div class="fw-bold">${c.name}</div>
                                    <small class="text-muted"><i class="bx bx-phone me-1"></i>${c.mobile || 'No phone'}</small>
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
        const mobile = $(this).data('mobile');

        $('#customer_id').val(id);
        $('#customer_mobile').val(mobile);
        $('#client_search_input').val(name + (mobile ? ' (' + mobile + ')' : ''));
        $('#clear_client_btn').show();
        hideCustomMenu();
    });

    $('#clear_client_btn').on('click', function() {
        $('#customer_id').val('');
        $('#customer_mobile').val('');
        $('#client_search_input').val('');
        $(this).hide();
        hideCustomMenu();
    });

    let waTable = $('#waLogsTable').DataTable({
        paging: false,
        info: false,
        language: {
            emptyTable: "No WhatsApp logs found."
        }
    });

    $('#waMessage').on('input', function() {
        $('#waCharCount').text($(this).val().length + ' characters');
    });

    $(document).on('click', '.btn-emoji', function() {
        var emoji = $(this).data('emoji');
        var textarea = $('#waMessage');
        var el = textarea[0];
        var startPos = el.selectionStart || textarea.val().length;
        var endPos = el.selectionEnd || textarea.val().length;
        var val = textarea.val();
        var newVal = val.substring(0, startPos) + emoji + val.substring(endPos);
        textarea.val(newVal);
        textarea.focus();
        var newPos = startPos + emoji.length;
        el.setSelectionRange(newPos, newPos);
        $('#waCharCount').text(textarea.val().length + ' characters');
    });

    $('#whatsappForm').on('submit', function(e) {
        e.preventDefault();
        let form = $(this);
        let btn = $('#waSendBtn');

        if (!$('#customer_id').val()) {
            $('#client_search_input').addClass('is-invalid');
            toastr.error('Please select a customer');
            return;
        }

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Sending...');
        form.find('.is-invalid').removeClass('is-invalid');

        $.ajax({
            url: '{{ route("admin.crm.whatsapp.send") }}',
            method: 'POST',
            data: form.serialize(),
            success: function(res) {
                btn.prop('disabled', false).html('<i class="bx bxl-whatsapp me-1"></i> Send WhatsApp');
                if (res.success) {
                    form[0].reset();
                    $('#customer_id').val('');
                    $('#customer_mobile').val('');
                    $('#client_search_input').val('');
                    $('#clear_client_btn').hide();
                    $('#waCharCount').text('0 characters');

                    if (res.whatsapp_url) {
                        // Open WhatsApp Web directly
                        window.open(res.whatsapp_url, '_blank');

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'WhatsApp Message Saved!',
                                html: `Log recorded.<br><br><a href="${res.whatsapp_url}" target="_blank" class="btn btn-success"><i class="bx bxl-whatsapp me-1"></i> Open WhatsApp Chat</a>`,
                                showConfirmButton: true
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            toastr.success('WhatsApp message logged successfully');
                            setTimeout(() => location.reload(), 1500);
                        }
                    } else {
                        toastr.success(res.message || 'Message logged');
                        setTimeout(() => location.reload(), 1200);
                    }
                } else {
                    toastr.error(res.message || 'Error sending message');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="bx bxl-whatsapp me-1"></i> Send WhatsApp');
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    let errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, msg) {
                        let input = form.find('[name="' + key + '"]');
                        input.addClass('is-invalid');
                        if (key === 'customer_id') {
                            $('#client_search_input').addClass('is-invalid');
                        }
                        input.siblings('.invalid-feedback').text(msg[0]);
                    });
                } else {
                    let msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'An error occurred while sending message';
                    toastr.error(msg);
                }
            }
        });
    });
});
</script>
@endsection
