@extends('admin.layouts.app')

@section('style')
<style>
    .leave-status { padding: 4px 12px; border-radius: 30px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
    .status-pending { background: #fff3cd; color: #856404; }
    .status-approved { background: #d4edda; color: #155724; }
    .status-rejected { background: #f8d7da; color: #721c24; }
</style>
@endsection

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1">Leave Management</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Leaves</li>
                </ol>
            </nav>
        </div>
        <div>
            @if(!auth()->user()->isSuperAdmin() && !auth()->user()->isCompanyAdmin())
            <a href="{{ route('admin.leaves.create') }}" class="btn btn-primary"><i class="bx bx-plus me-1"></i> Apply Leave</a>
            @endif
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                @if(auth()->user()->isSuperAdmin())
                <div class="col-md-3">
                    <label class="form-label">Company</label>
                    <select name="company_id" class="form-select">
                        <option value="">All Companies</option>
                        @foreach($companies as $c)
                            <option value="{{ $c->id }}" {{ request('company_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Leave Type</label>
                    <select name="leave_type" class="form-select">
                        <option value="">All</option>
                        <option value="sick" {{ request('leave_type') == 'sick' ? 'selected' : '' }}>Sick</option>
                        <option value="casual" {{ request('leave_type') == 'casual' ? 'selected' : '' }}>Casual</option>
                        <option value="annual" {{ request('leave_type') == 'annual' ? 'selected' : '' }}>Annual</option>
                        <option value="other" {{ request('leave_type') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter-alt me-1"></i> Filter</button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="{{ route('admin.leaves.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="py-3">Employee</th>
                        <th class="py-3">Company</th>
                        <th class="py-3">Type</th>
                        <th class="py-3">Duration</th>
                        <th class="py-3">Days</th>
                        <th class="py-3">Reason</th>
                        <th class="py-3">Status</th>
                        <th class="py-3">Approved By</th>
                        <th class="py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaves as $leave)
                    <tr>
                        <td>
                            <span class="fw-semibold text-dark">{{ $leave->user->full_name }}</span>
                            <small class="d-block text-muted">{{ $leave->user->roles->first()?->name ?? 'N/A' }}</small>
                        </td>
                        <td><span class="badge bg-label-info">{{ $leave->user->company?->name ?? '-' }}</span></td>
                        <td><span class="badge bg-label-secondary">{{ ucfirst($leave->leave_type) }}</span></td>
                        <td>{{ $leave->start_date->format('d M') }} - {{ $leave->end_date->format('d M Y') }}</td>
                        <td><strong>{{ $leave->start_date->diffInDays($leave->end_date) + 1 }}</strong></td>
                        <td><small class="text-muted">{{ Str::limit($leave->reason, 40) }}</small></td>
                        <td>
                            <span class="leave-status status-{{ $leave->status }}">{{ ucfirst($leave->status) }}</span>
                        </td>
                        <td><small class="text-muted">{{ $leave->approver?->full_name ?? '-' }}</small></td>
                        <td class="text-center">
                            @if($leave->status === 'pending' && (auth()->user()->isSuperAdmin() || auth()->user()->isCompanyAdmin()))
                                @if(auth()->user()->isCompanyAdmin() && $leave->user->company_id !== auth()->user()->company_id)
                                    <span class="text-muted small">-</span>
                                @else
                                <div class="d-flex justify-content-center gap-1">
                                    <form method="POST" action="{{ route('admin.leaves.approve', $leave->id) }}" style="display:inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Approve"><i class="bx bx-check"></i></button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-danger" title="Reject" onclick="openRejectModal({{ $leave->id }})"><i class="bx bx-x"></i></button>
                                </div>
                                @endif
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center py-4">No leave records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $leaves->links() }}</div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">Reject Leave</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <hr class="my-3">
            <div class="modal-body pt-0">
                <form method="POST" action="" id="rejectForm">
                    @csrf
                    <p class="text-muted">Are you sure you want to reject this leave application?</p>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger"><i class="bx bx-x me-1"></i> Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openRejectModal(leaveId) {
    document.getElementById('rejectForm').action = '{{ route('admin.leaves.reject', '') }}/' + leaveId;
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}
</script>
@endsection
