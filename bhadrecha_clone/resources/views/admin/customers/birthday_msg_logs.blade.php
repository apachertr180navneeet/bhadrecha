@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bx bxl-whatsapp me-1 text-success"></i> Birthday WhatsApp Logs</h5>
                    <a href="{{ route('admin.customers.birthdays') }}" class="btn btn-primary btn-sm">
                        <i class="bx bx-cake me-1"></i> Birthday Customers
                    </a>
                </div>
                <div class="card-body">
                    @if($logs->isEmpty())
                        <div class="text-center py-5">
                            <i class="bx bxl-whatsapp" style="font-size: 3rem; color: #94a3b8;"></i>
                            <p class="text-muted mt-3 mb-0">No birthday messages sent yet</p>
                        </div>
                    @else
                        <div class="table-responsive text-nowrap">
                            <table class="table table-hover" id="msgLogsTable">
                                <thead>
                                    <tr>
                                        <th>S.No.</th>
                                        <th>Date & Time</th>
                                        <th>Customer</th>
                                        <th>Mobile</th>
                                        <th>Message</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($logs as $index => $log)
                                    <tr>
                                        <td>{{ $logs->firstItem() + $index }}</td>
                                        <td>{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y h:i A') }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-2">
                                                    <span class="avatar-initial rounded-circle bg-label-success d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-weight: 600;">
                                                        {{ $log->customer ? strtoupper(substr($log->customer->name, 0, 2)) : 'NA' }}
                                                    </span>
                                                </div>
                                                <span class="fw-semibold">{{ $log->customer->name ?? 'N/A' }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $log->mobile }}</td>
                                        <td>
                                            <span data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $log->message }}">
                                                {{ Str::limit($log->message, 50) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($log->status == 'sent')
                                                <span class="badge bg-label-success"><i class="bx bx-check me-1"></i>Sent</span>
                                            @elseif($log->status == 'failed')
                                                <span class="badge bg-label-danger"><i class="bx bx-x me-1"></i>Failed</span>
                                            @else
                                                <span class="badge bg-label-warning"><i class="bx bx-time me-1"></i>Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">{{ $logs->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    $('#msgLogsTable').DataTable({
        paging: false,
        info: false,
        lengthChange: false,
        order: [[0, 'asc']],
        columnDefs: [{ targets: 0, orderable: false }]
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(el) { return new bootstrap.Tooltip(el); });
});
</script>
@endsection
