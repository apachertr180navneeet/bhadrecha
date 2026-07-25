@extends('admin.layouts.app')

@section('style')
<style>
    .adv-status { padding: 4px 12px; border-radius: 30px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
    .status-pending { background: #fff3cd; color: #856404; }
    .status-approved { background: #d4edda; color: #155724; }
    .status-rejected { background: #f8d7da; color: #721c24; }
    .status-paid { background: #cce5ff; color: #004085; }
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
            <h4 class="fw-bold mb-1">Salary Advance Management</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Advances</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.advances.create') }}" class="btn btn-primary"><i class="bx bx-plus me-1"></i> Request Advance</a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                @if(auth()->user()->isSuperAdmin())
                <div class="col-md-3">
                    <label class="form-label">Company</label>
                    <select name="company_id" class="form-select">
                        <option value="">All</option>
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
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter-alt me-1"></i> Filter</button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="{{ route('admin.advances.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
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
                        <th class="py-3 text-end">Amount</th>
                        <th class="py-3">Type</th>
                        <th class="py-3 text-end">Balance</th>
                        <th class="py-3">Reason</th>
                        <th class="py-3">Status</th>
                        <th class="py-3">Approved By</th>
                        @if(auth()->user()->isSuperAdmin() || auth()->user()->isCompanyAdmin())
                        <th class="py-3 text-center">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($advances as $adv)
                    <tr>
                        <td>
                            <span class="fw-semibold text-dark">{{ $adv->user->full_name }}</span>
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->isCompanyAdmin())
                            <small class="d-block text-muted">{{ $adv->user->roles->first()?->name ?? 'N/A' }}</small>
                            @endif
                        </td>
                        <td><span class="badge bg-label-info">{{ $adv->user->company?->name ?? '-' }}</span></td>
                        <td class="text-end fw-bold text-dark">₹{{ number_format($adv->amount, 2) }}</td>
                        <td>
                            <span class="badge bg-label-secondary">
                                {{ $adv->deduction_type === 'monthly' ? 'Monthly' : 'Full' }}
                            </span>
                            @if($adv->deduction_type === 'monthly')
                                <br><small class="text-muted">₹{{ number_format($adv->monthly_deduction, 2) }}/mo</small>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($adv->status === 'approved' || $adv->status === 'paid')
                                <span class="fw-bold {{ $adv->balance > 0 ? 'text-warning' : 'text-success' }}">₹{{ number_format($adv->balance, 2) }}</span>
                                @if($adv->is_cleared || $adv->status === 'paid')
                                    <br><span class="badge bg-success" style="font-size: 0.65rem;">Cleared</span>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td><small class="text-muted">{{ Str::limit($adv->reason, 50) }}</small></td>
                        <td><span class="adv-status status-{{ $adv->status }}">{{ ucfirst($adv->status) }}</span></td>
                        <td><small class="text-muted">{{ $adv->approver?->full_name ?? '-' }}</small></td>
                        @if(auth()->user()->isSuperAdmin() || auth()->user()->isCompanyAdmin())
                        <td class="text-center">
                            @if($adv->status === 'pending')
                                @if(auth()->user()->isCompanyAdmin() && $adv->user->company_id !== auth()->user()->company_id)
                                    <span class="text-muted small">-</span>
                                @else
                                <div class="d-flex justify-content-center gap-1">
                                    <form method="POST" action="{{ route('admin.advances.approve', $adv->id) }}" style="display:inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Approve"><i class="bx bx-check"></i></button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.advances.reject', $adv->id) }}" style="display:inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger" title="Reject"><i class="bx bx-x"></i></button>
                                    </form>
                                </div>
                                @endif
                            @elseif($adv->status === 'approved')
                                <form method="POST" action="{{ route('admin.advances.mark-paid', $adv->id) }}" style="display:inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary" title="Mark Paid"><i class="bx bx-money"></i> Mark Paid</button>
                                </form>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-4">No advance records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $advances->links() }}</div>
</div>
@endsection
