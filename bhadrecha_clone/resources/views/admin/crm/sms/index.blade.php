@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="row">
        <!-- Send SMS Form -->
        <div class="col-lg-5 mb-4 mb-lg-0">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Send SMS</h5>
                    <small class="text-muted">Compose and send SMS to customers</small>
                </div>
                <div class="card-body">
                    <form id="smsForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="smsCustomer">Customer <span class="text-danger">*</span></label>
                            <select class="form-select" name="customer_id" id="smsCustomer" required>
                                <option value="">Select Customer</option>
                                @foreach(\App\Models\Customer::all() as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->mobile }})</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="smsType">Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="type" id="smsType" required>
                                <option value="">Select Type</option>
                                <option value="promotional">Promotional</option>
                                <option value="transactional">Transactional</option>
                                <option value="reminder">Reminder</option>
                                <option value="followup">Follow Up</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="smsMessage">Message <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="message" id="smsMessage" rows="5" placeholder="Type your message here..." required></textarea>
                            <div class="invalid-feedback"></div>
                            <small class="text-muted" id="smsCharCount">0 characters</small>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" id="smsSendBtn">
                            <i class="bx bx-send me-1"></i> Send SMS
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- SMS Logs -->
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">SMS Logs</h5>
                    <small class="text-muted">History of sent SMS messages</small>
                </div>
                <div class="table-responsive text-nowrap">
<table class="table table-hover" id="smsLogsTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Mobile</th>
                                <th>Message</th>
                                <th>Type</th>
                                <th>Status</th>
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
    let smsTable = $('#smsLogsTable').DataTable({
        paging: false,
        info: false,
        language: {
            emptyTable: "No SMS logs found."
        }
    });

    $('#smsMessage').on('input', function() {
        $('#smsCharCount').text($(this).val().length + ' characters');
    });

    $('#smsForm').on('submit', function(e) {
        e.preventDefault();
        let form = $(this);
        let btn = $('#smsSendBtn');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Sending...');

        $.ajax({
            url: '{{ route("admin.crm.sms.send") }}',
            method: 'POST',
            data: form.serialize(),
            success: function(res) {
                if (res.success) {
                    form[0].reset();
                    $('#smsCharCount').text('0 characters');
                    toastr.success(res.message);
                    setTimeout(() => location.reload(), 500);
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="bx bx-send me-1"></i> Send SMS');
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, msg) {
                        let input = form.find('[name="' + key + '"]');
                        input.addClass('is-invalid');
                        input.siblings('.invalid-feedback').text(msg[0]);
                    });
                }
            }
        });
    });
});
</script>
@endsection
