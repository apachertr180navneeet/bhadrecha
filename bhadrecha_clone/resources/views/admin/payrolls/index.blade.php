@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h5 class="card-title mb-0">Payroll Management</h5>
                <small class="text-muted">Manage staff salaries and payments</small>
            </div>
            <div class="mt-2 mt-md-0 d-flex gap-2">
                @can('edit payrolls')
                <button type="button" class="btn btn-sm btn-outline-success d-none" id="bulkMarkPaidBtn">
                    <i class="bx bx-check-double me-1"></i> Mark Selected as Paid
                </button>
                @endcan
                @can('create payrolls')
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#generateBulkModal">
                    <i class="bx bx-cog me-1"></i> Generate Bulk Payroll
                </button>
                <a href="{{ route('admin.payrolls.create') }}" class="btn btn-sm btn-primary"><i class="bx bx-plus me-1"></i> Add Payroll</a>
                @endcan
            </div>
        </div>
        <div class="table-responsive text-nowrap">
<table class="table table-hover">
                <thead>
                    <tr>
                        <th style="width: 40px;"><input type="checkbox" class="form-check-input" id="checkAllPayrolls"></th>
                        @if(auth()->user()->can('view payrolls') || auth()->user()->can('edit payrolls') || auth()->user()->can('delete payrolls'))
                        <th class="text-center text-nowrap" style="width:80px;">Actions</th>
                        @endif
                        <th>Staff</th>
                        <th>Month</th>
                        <th>Year</th>
                        <th>Basic Salary</th>
                        <th>Attendance Deductions</th>
                        <th>Advance Deduction</th>
                        <th>Net Amount</th>
                        <th style="width:90px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payrolls as $payroll)
                    <tr id="payroll-row-{{ $payroll->id }}">
                        <td>
                            @if($payroll->status == 'pending')
                            <input type="checkbox" class="form-check-input payroll-checkbox" value="{{ $payroll->id }}">
                            @endif
                        </td>
                        @if(auth()->user()->can('view payrolls') || auth()->user()->can('edit payrolls') || auth()->user()->can('delete payrolls'))
                        <td class="text-center text-nowrap">
                            <div class="dropdown">
                                <button class="btn p-0 dropdown-toggle hide-arrow" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    @can('edit payrolls')
                                    <li>
                                        <a class="dropdown-item text-primary" href="{{ route('admin.payrolls.edit', $payroll->id) }}">
                                            <i class="bx bx-edit-alt me-1"></i> Edit Payroll
                                        </a>
                                    </li>
                                    @if($payroll->status == 'pending')
                                    <li>
                                        <a class="dropdown-item text-success mark-paid" href="javascript:void(0);" data-id="{{ $payroll->id }}">
                                            <i class="bx bx-check-circle me-1"></i> Mark as Paid
                                        </a>
                                    </li>
                                    @endif
                                    @endcan
                                    @can('view payrolls')
                                    <li>
                                        <a class="dropdown-item text-info" href="{{ route('admin.payrolls.payslip', $payroll->id) }}" target="_blank">
                                            <i class="bx bx-download me-1"></i> Download Payslip
                                        </a>
                                    </li>
                                    @endcan
                                    @can('delete payrolls')
                                    <li>
                                        <a class="dropdown-item text-danger delete-payroll" href="javascript:void(0);" data-id="{{ $payroll->id }}">
                                            <i class="bx bx-trash me-1"></i> Delete
                                        </a>
                                    </li>
                                    @endcan
                                </ul>
                            </div>
                        </td>
                        @endif
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-3" style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;">
                                    <span class="avatar-initial rounded-circle bg-label-primary" style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:600;">
                                        {{ strtoupper(substr($payroll->staff->full_name ?? '--', 0, 2)) }}
                                    </span>
                                </div>
                                <span class="fw-semibold">{{ $payroll->staff->full_name ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <td>{{ date('F', mktime(0, 0, 0, $payroll->month, 1)) }}</td>
                        <td>{{ $payroll->year }}</td>
                        <td>₹{{ number_format($payroll->basic_salary, 2) }}</td>
                        <td class="text-danger">₹{{ number_format($payroll->deductions ?? 0, 2) }}</td>
                        <td class="text-warning fw-semibold">₹{{ number_format($payroll->advance_amount ?? 0, 2) }}</td>
                        <td class="fw-semibold">₹{{ number_format($payroll->net_amount, 2) }}</td>
                        <td>
                            <span class="badge bg-label-{{ $payroll->status == 'paid' ? 'success' : 'warning' }}" id="payroll-status-{{ $payroll->id }}">
                                {{ ucfirst($payroll->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">No payroll records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Generate Bulk Modal -->
<div class="modal fade" id="generateBulkModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel2">Generate Bulk Payroll</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.payrolls.generate-all') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col mb-3">
                            <label class="form-label">Month</label>
                            <select name="month" class="form-select" required>
                                <option value="">Select</option>
                                @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-0">
                            <label class="form-label">Year</label>
                            <select name="year" class="form-select" required>
                                <option value="">Select</option>
                                @foreach(range(date('Y'), date('Y') - 5) as $y)
                                <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Generate</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
$(function() {
    // Checkbox Logic
    $('#checkAllPayrolls').on('change', function() {
        $('.payroll-checkbox').prop('checked', $(this).prop('checked'));
        toggleBulkBtn();
    });

    $('.payroll-checkbox').on('change', function() {
        toggleBulkBtn();
    });

    function toggleBulkBtn() {
        let checkedCount = $('.payroll-checkbox:checked').length;
        if (checkedCount > 0) {
            $('#bulkMarkPaidBtn').removeClass('d-none');
        } else {
            $('#bulkMarkPaidBtn').addClass('d-none');
        }
    }

    // Bulk Mark Paid
    $('#bulkMarkPaidBtn').on('click', function() {
        let ids = [];
        $('.payroll-checkbox:checked').each(function() {
            ids.push($(this).val());
        });

        if (ids.length === 0) return;

        Swal.fire({
            title: 'Are you sure?',
            text: "Mark " + ids.length + " selected payrolls as paid?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, mark as paid',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.payrolls.bulk-mark-paid") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        ids: ids
                    },
                    success: function(res) {
                        if(res.success){
                            showSuccessToast(res.message);
                            window.location.reload();
                        } else {
                            showErrorToast(res.message);
                        }
                    },
                    error: function(){
                        showErrorToast('Failed to mark payrolls as paid');
                    }
                });
            }
        });
    });

    $('.mark-paid').on('click', function() {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "Mark this payroll as paid?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, mark as paid',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.payrolls.mark-paid", ":id") }}'.replace(':id', id),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: id
                    },
                    success: function(res) {
                        if(res.success){
                            showSuccessToast('Payroll marked as paid');
                            window.location.reload();
                        } else {
                            showErrorToast('Failed to mark payroll as paid');
                        }
                    },
                    error: function(err){
                        showErrorToast('Failed to mark payroll as paid');
                    }
                });
            }
        });
    });

    $('.delete-payroll').on('click', function() {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "Delete this payroll record?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.payrolls.destroy", "") }}/' + id,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        if(res.success){
                            showSuccessToast('Payroll deleted');
                            window.location.reload();
                        } else {
                            showErrorToast('Failed to delete payroll');
                        }
                    },
                    error: function(err){
                        showErrorToast('Failed to delete payroll');
                    }
                });
            }
        });
    });
});
</script>
@endsection
