@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h5 class="card-title mb-0">Leave Requests</h5>
                <small class="text-muted">Manage staff leave applications</small>
            </div>
            <div class="mt-2 mt-md-0">
                @can('create leave requests')
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addLeaveModal">
                    <i class="bx bx-plus me-1"></i> Add Leave
                </button>
                @endcan
            </div>
        </div>
        <div class="table-responsive text-nowrap">
<table class="table table-hover">
                <thead>
                    <tr>
                        <th>Staff</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Reason</th>
                        <th style="width:90px;">Status</th>
                        <th>Admin Remarks</th>
                        @if(auth()->user()->can('approve leave requests') || auth()->user()->can('delete leave requests'))
                        <th style="width:140px;">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaves as $leave)
                    <tr id="leave-row-{{ $leave->id }}">
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-3" style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;">
                                    <span class="avatar-initial rounded-circle bg-label-primary" style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:600;">
                                        {{ strtoupper(substr($leave->staff->full_name ?? '--', 0, 2)) }}
                                    </span>
                                </div>
                                <span class="fw-semibold">{{ $leave->staff->full_name ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}</td>
                        <td>{{ $leave->reason }}</td>
                        <td>
                            <span class="badge bg-label-{{ $leave->status == 'approved' ? 'success' : ($leave->status == 'rejected' ? 'danger' : 'warning') }}" id="leave-status-{{ $leave->id }}">
                                {{ ucfirst($leave->status) }}
                            </span>
                        </td>
                        <td>{{ $leave->admin_remarks ?? '-' }}</td>
                        @if(auth()->user()->can('approve leave requests') || auth()->user()->can('delete leave requests'))
                        <td>
                            <div class="dropdown">
                                <button class="btn p-0 dropdown-toggle hide-arrow" type="button" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    @can('approve leave requests')
                                    @if($leave->status == 'pending')
                                    <li>
                                        <a class="dropdown-item text-success leave-action" href="javascript:void(0);" data-id="{{ $leave->id }}" data-action="approved">
                                            <i class="bx bx-check me-1"></i> Approve
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-danger leave-action" href="javascript:void(0);" data-id="{{ $leave->id }}" data-action="rejected">
                                            <i class="bx bx-x me-1"></i> Reject
                                        </a>
                                    </li>
                                    @endif
                                    @endcan
                                    @can('delete leave requests')
                                    <li>
                                        <a class="dropdown-item text-danger leave-delete" href="javascript:void(0);" data-id="{{ $leave->id }}">
                                            <i class="bx bx-trash me-1"></i> Delete
                                        </a>
                                    </li>
                                    @endcan
                                </ul>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No leave requests found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Leave Modal -->
<div class="modal fade" id="addLeaveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.leave-requests.store') }}" id="addLeaveForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Leave Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Staff</label>
                        <select name="staff_id" class="form-select" required>
                            <option value="">Select Staff</option>
                            @foreach($staffs as $staff)
                            <option value="{{ $staff->id }}">{{ $staff->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <textarea name="reason" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(function() {
    $('.leave-action').on('click', function() {
        let id = $(this).data('id');
        let action = $(this).data('action');
        
        Swal.fire({
            title: 'Enter admin remarks',
            input: 'text',
            inputPlaceholder: 'Optional admin remarks...',
            showCancelButton: true,
            confirmButtonText: 'Submit',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                let remarks = result.value || '';
                $.ajax({
                    url: '{{ route("admin.leave-requests.update-status", ":id") }}'.replace(':id', id),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: id,
                        status: action,
                        admin_remarks: remarks
                    },
                    success: function(response) {
                        if (response.success) {
                            let badge = $('#leave-status-' + id);
                            let label = action.charAt(0).toUpperCase() + action.slice(1);
                            let cls = action === 'approved' ? 'bg-label-success' : 'bg-label-danger';
                            badge.removeClass('bg-label-success bg-label-danger bg-label-warning').addClass(cls).text(label);
                            $('#leave-row-' + id + ' .leave-action').remove();
                            showSuccessToast('Leave ' + action + ' successfully');
                        }
                    },
                    error: function() {
                        showErrorToast('Failed to update leave status');
                    }
                });
            }
        });
    });

    $('.leave-delete').on('click', function() {
        let id = $(this).data('id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.leave-requests.destroy", "") }}/' + id,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        if (response.success) {
                            $('#leave-row-' + id).fadeOut();
                            showSuccessToast('Leave deleted');
                        }
                    },
                    error: function() {
                        showErrorToast('Failed to delete leave');
                    }
                });
            }
        });
    });

    $('#addLeaveForm').on('submit', function(e) {
        e.preventDefault();
        let form = $(this);
        let btn = form.find('button[type="submit"]');
        let originalText = btn.text();
        btn.text('Submitting...').prop('disabled', true);

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    $('#addLeaveModal').modal('hide');
                    showSuccessToast(response.message);
                    setTimeout(() => location.reload(), 1000);
                }
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    let errors = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                    showErrorToast(errors);
                } else if (xhr.responseJSON && xhr.responseJSON.error) {
                    showErrorToast(xhr.responseJSON.error);
                } else {
                    showErrorToast('Failed to submit leave request');
                }
                btn.text(originalText).prop('disabled', false);
            }
        });
    });
});
</script>
@endsection
