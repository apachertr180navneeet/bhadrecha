@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="row">
        <!-- Send Email Form -->
        <div class="col-lg-5 mb-4 mb-lg-0">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Send Email</h5>
                    <small class="text-muted">Compose and send emails to customers</small>
                </div>
                <div class="card-body">
                    <form id="emailForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="emailCustomer">Customer <span class="text-danger">*</span></label>
                            <select class="form-select" name="customer_id" id="emailCustomer" required>
                                <option value="">Select Customer</option>
                                @foreach(\App\Models\Customer::all() as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->email }})</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="emailType">Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="type" id="emailType" required>
                                <option value="">Select Type</option>
                                <option value="promotional">Promotional</option>
                                <option value="transactional">Transactional</option>
                                <option value="reminder">Reminder</option>
                                <option value="followup">Follow Up</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="emailSubject">Subject <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="subject" id="emailSubject" placeholder="Enter email subject" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="emailMessage">Message <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="message" id="emailMessage" rows="5" placeholder="Type your message here..." required></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                        <button type="submit" class="btn btn-info w-100" id="emailSendBtn">
                            <i class="bx bx-envelope me-1"></i> Send Email
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Email Logs -->
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Email Logs</h5>
                    <small class="text-muted">History of sent emails</small>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover" id="emailLogsTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Email</th>
                                <th>Subject</th>
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
                                <td>{{ $log->email ?? $log->customer->email ?? 'N/A' }}</td>
                                <td>{{ Str::limit($log->subject, 30) }}</td>
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
    let emailTable = $('#emailLogsTable').DataTable({
        paging: false,
        info: false,
        language: {
            emptyTable: "No email logs found."
        }
    });

    $('#emailForm').on('submit', function(e) {
        e.preventDefault();
        let form = $(this);
        let btn = $('#emailSendBtn');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Sending...');

        $.ajax({
            url: '{{ route("admin.crm.email.send") }}',
            method: 'POST',
            data: form.serialize(),
            success: function(res) {
                if (res.success) {
                    form[0].reset();
                    toastr.success(res.message);
                    setTimeout(() => location.reload(), 500);
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="bx bx-envelope me-1"></i> Send Email');
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
