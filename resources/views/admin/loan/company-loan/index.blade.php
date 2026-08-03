@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <div>
            <h5 class="fw-bold mb-1">Company Loans</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Company Loans</li>
                </ol>
            </nav>
        </div>
        <div>
            @can('create company loans')
            <a href="{{ route('admin.loan.company-loan.create') }}" class="btn btn-primary"><i class="bx bx-plus me-1"></i> Add Company Loan</a>
            @endcan
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.loan.company-loan.index') }}" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search by loan ID or bank..." value="{{ request('search') }}">
                </div>
                @if(auth()->user()->isSuperAdmin())
                <div class="col-md-2">
                    <select name="company_id" class="form-select">
                        <option value="">All Companies</option>
                        @foreach($companies as $c)
                        <option value="{{ $c->id }}" {{ request('company_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </div>
                @if(request()->hasAny(['search','status','company_id']))
                <div class="col-md-2">
                    <a href="{{ route('admin.loan.company-loan.index') }}" class="btn btn-outline-secondary w-100">Clear</a>
                </div>
                @endif
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Loan ID</th>
                        @if(auth()->user()->isSuperAdmin())<th>Company</th>@endif
                        <th>Bank</th>
                        <th>Branch</th>
                        <th>Loan Amount</th>
                        <th>Tenure</th>
                        <th>EMI</th>
                        <th>Given Amount</th>
                        <th>Paid</th>
                        <th>Remaining</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($loans as $key => $loan)
                    <tr>
                        <td>{{ ($loans->currentPage() - 1) * $loans->perPage() + $key + 1 }}</td>
                        <td class="fw-semibold">{{ $loan->loan_id }}</td>
                        @if(auth()->user()->isSuperAdmin())<td>{{ $loan->company->name ?? '-' }}</td>@endif
                        <td>{{ $loan->bank->name ?? '-' }}</td>
                        <td>{{ $loan->branch->branch_name ?? '-' }}</td>
                        <td>{{ number_format($loan->loan_amount, 2) }}</td>
                        <td>{{ $loan->tenure_months }} months</td>
                        <td>{{ number_format($loan->emi_amount, 2) }}</td>
                        <td>{{ number_format($loan->given_amount, 2) }}</td>
                        <td class="fw-semibold text-success">{{ number_format($loan->total_paid, 2) }}</td>
                        <td class="fw-semibold {{ $loan->remaining_amount > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($loan->remaining_amount, 2) }}</td>
                        <td>
                            @php
                                $statusClass = $loan->status == 'active' ? 'success' : ($loan->status == 'closed' ? 'secondary' : 'danger');
                            @endphp
                            <span class="badge bg-label-{{ $statusClass }}">{{ ucfirst($loan->status) }}</span>
                        </td>
                        <td class="text-center text-nowrap">
                            @canany(['record company loan payments', 'edit company loans'])
                            <button type="button" class="btn btn-sm btn-icon btn-outline-success" onclick="showPayModal({{ $loan->id }}, '{{ $loan->loan_id }}', {{ $loan->emi_amount }})" title="Record Payment"><i class="bx bx-money"></i></button>
                            @endcanany
                            <button type="button" class="btn btn-sm btn-icon btn-outline-info" onclick="showPayments({{ $loan->id }}, '{{ $loan->loan_id }}')" title="View Payments"><i class="bx bx-list-ul"></i></button>
                            @can('edit company loans')
                            <a href="{{ route('admin.loan.company-loan.edit', $loan->id) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Edit"><i class="bx bx-edit"></i></a>
                            <form action="{{ route('admin.loan.company-loan.toggle-status', $loan->id) }}" method="POST" class="d-inline">@csrf
                                <button type="submit" class="btn btn-sm btn-icon btn-outline-{{ $loan->status == 'active' ? 'warning' : 'success' }}" title="Change Status"><i class="bx bx-{{ $loan->status == 'active' ? 'pause' : 'play' }}"></i></button>
                            </form>
                            @endcan
                            @can('delete company loans')
                            <button type="button" class="btn btn-sm btn-icon btn-outline-danger" onclick="handleDelete({{ $loan->id }}, '{{ $loan->loan_id }}')" title="Delete"><i class="bx bx-trash"></i></button>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="15" class="text-center py-4">
                            <p class="text-muted mb-0">No company loans found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($loans->hasPages())
        <div class="card-footer bg-transparent border-top py-3">
            {{ $loans->links() }}
        </div>
        @endif
    </div>
</div>

<form id="delete-form" method="POST" style="display: none;">@csrf @method('DELETE')</form>

<div class="modal fade" id="payModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="" id="payForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Record Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-1">Loan: <strong id="payLoanId"></strong></p>
                    <p class="mb-3">EMI Amount: <strong id="payEmiAmount"></strong></p>
                    <div class="mb-3">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" id="payAmount" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" max="9999-12-31" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="bx bx-money me-1"></i> Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="paymentsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payment History - <span id="paymentsLoanId"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr><th>#</th><th>Amount</th><th>Date</th><th>Notes</th></tr>
                    </thead>
                    <tbody id="paymentsBody"></tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    function handleDelete(id, loanId) {
        Swal.fire({
            title: 'Delete Loan?',
            text: "Are you sure you want to delete '" + loanId + "'?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, Delete it',
            cancelButtonText: 'Cancel',
            customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-secondary' },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('delete-form');
                form.action = "{{ url('admin/loan/company-loan') }}/" + id;
                form.submit();
            }
        })
    }

    function showPayModal(id, loanId, emiAmount) {
        document.getElementById('payLoanId').textContent = loanId;
        document.getElementById('payEmiAmount').textContent = emiAmount.toFixed(2);
        document.getElementById('payAmount').value = emiAmount;
        document.getElementById('payForm').action = "{{ url('admin/loan/company-loan') }}/" + id + "/pay";
        new bootstrap.Modal(document.getElementById('payModal')).show();
    }

    function showPayments(id, loanId) {
        document.getElementById('paymentsLoanId').textContent = loanId;
        const tbody = document.getElementById('paymentsBody');
        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-3">Loading...</td></tr>';
        new bootstrap.Modal(document.getElementById('paymentsModal')).show();

        fetch('{{ url("admin/loan/company-loan") }}/' + id + '/payments')
            .then(res => res.json())
            .then(data => {
                tbody.innerHTML = '';
                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center py-3 text-muted">No payments recorded</td></tr>';
                    return;
                }
                data.forEach((p, i) => {
                    tbody.innerHTML += '<tr><td>' + (i + 1) + '</td><td class="fw-semibold text-success">' + parseFloat(p.amount).toFixed(2) + '</td><td>' + p.payment_date + '</td><td>' + (p.notes || '-') + '</td></tr>';
                });
            });
    }
</script>
@endsection
