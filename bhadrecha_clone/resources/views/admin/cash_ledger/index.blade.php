@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <div>
            <h5 class="fw-bold mb-1"><i class="bx bx-wallet-alt me-2 text-primary"></i>Cash Ledger</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Cash Ledger</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.cash-ledger.export-excel', request()->all()) }}" class="btn btn-outline-success btn-sm">
                <i class="bx bx-download me-1"></i> Export Excel
            </a>
            @if(auth()->user()->can('add cash entry') || auth()->user()->can('create cash entry') || auth()->user()->isSuperAdmin())
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addEntryModal">
                <i class="bx bx-plus-circle me-1"></i> Add Cash Entry
            </button>
            @endif
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100 bg-label-{{ $currentBalance >= 0 ? 'success' : 'danger' }}">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-semibold text-uppercase text-muted">Current Account Balance</span>
                        <i class="bx bx-wallet fs-4 text-{{ $currentBalance >= 0 ? 'success' : 'danger' }}"></i>
                    </div>
                    <h3 class="mb-0 fw-bold text-{{ $currentBalance >= 0 ? 'success' : 'danger' }}">
                        ₹ {{ number_format($currentBalance, 2) }}
                    </h3>
                    <small class="text-muted">Total Credit - Total Debit</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100 bg-label-info">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-semibold text-uppercase text-muted">Total Credit (Deposits)</span>
                        <i class="bx bx-down-arrow-circle fs-4 text-info"></i>
                    </div>
                    <h3 class="mb-0 fw-bold text-info">₹ {{ number_format($totalCredit, 2) }}</h3>
                    <small class="text-muted">Incoming Cash Received</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100 bg-label-danger">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-semibold text-uppercase text-muted">Total Debit (Expenses)</span>
                        <i class="bx bx-up-arrow-circle fs-4 text-danger"></i>
                    </div>
                    <h3 class="mb-0 fw-bold text-danger">₹ {{ number_format($totalDebit, 2) }}</h3>
                    <small class="text-muted">Outgoing Expenses Incurred</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100 bg-label-primary">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-semibold text-uppercase text-muted">Total Transactions</span>
                        <i class="bx bx-receipt fs-4 text-primary"></i>
                    </div>
                    <h3 class="mb-0 fw-bold text-primary">{{ number_format($totalCount) }}</h3>
                    <small class="text-muted">Matching Entries</small>
                </div>
            </div>
        </div>
    </div>

    @if($accountSummaries->isNotEmpty())
    <!-- Account-Wise Outstanding Balances -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header py-2 px-3 bg-light border-bottom d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold text-dark"><i class="bx bx-list-ul me-1 text-primary"></i>Ledger-wise Account Balances</h6>
            <small class="text-muted">Overview by account</small>
        </div>
        <div class="card-body p-3">
            <div class="row g-2">
                @foreach($accountSummaries as $acc)
                <div class="col-md-4 col-sm-6">
                    <div class="border rounded p-2 bg-white d-flex justify-content-between align-items-center">
                        <div>
                            <strong class="d-block text-dark">{{ $acc->account_name ?: 'Cash in Hand' }}</strong>
                            <small class="text-muted">
                                Cr: <span class="text-info fw-semibold">₹{{ number_format($acc->total_credit, 2) }}</span> &bull; 
                                Dr: <span class="text-danger fw-semibold">₹{{ number_format($acc->total_debit, 2) }}</span>
                            </small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-label-{{ $acc->balance >= 0 ? 'success' : 'danger' }} fs-7 fw-bold">
                                ₹{{ number_format($acc->balance, 2) }}
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Filter Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.cash-ledger.index') }}" class="row g-2 align-items-end">
                <div class="col-md-2 col-sm-6">
                    <label class="form-label small fw-bold">From Date</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2 col-sm-6">
                    <label class="form-label small fw-bold">To Date</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2 col-sm-6">
                    <label class="form-label small fw-bold">Transaction Type</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">All Transactions</option>
                        <option value="credit" {{ request('type') === 'credit' ? 'selected' : '' }}>Credit (Deposits)</option>
                        <option value="debit" {{ request('type') === 'debit' ? 'selected' : '' }}>Debit (Expenses)</option>
                    </select>
                </div>
                <div class="col-md-2 col-sm-6">
                    <label class="form-label small fw-bold">Account</label>
                    <select name="account_name" class="form-select form-select-sm">
                        <option value="">All Accounts</option>
                        @foreach($existingAccounts as $accName)
                        <option value="{{ $accName }}" {{ request('account_name') === $accName ? 'selected' : '' }}>{{ $accName }}</option>
                        @endforeach
                    </select>
                </div>
                @if(isset($branches) && $branches->count() > 1)
                <div class="col-md-2 col-sm-6">
                    <label class="form-label small fw-bold">Branch</label>
                    <select name="branch_id" class="form-select form-select-sm">
                        <option value="">All Branches</option>
                        @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ (request('branch_id') == $b->id || (empty(request('branch_id')) && $branchId == $b->id)) ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-2 col-sm-6">
                    <label class="form-label small fw-bold">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Voucher, description..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2 col-sm-6 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="bx bx-filter-alt me-1"></i>Filter</button>
                    <a href="{{ route('admin.cash-ledger.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filters"><i class="bx bx-refresh"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">Cash Transaction History</h6>
            <div class="small text-muted">
                Showing {{ $entries->firstItem() ?? 0 }} - {{ $entries->lastItem() ?? 0 }} of {{ $entries->total() }} entries
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Date</th>
                        <th>Voucher / Ref No</th>
                        <th>Account</th>
                        <th>Branch</th>
                        <th>Category</th>
                        <th>Particulars / Description</th>
                        <th class="text-end text-danger">Debit (₹)</th>
                        <th class="text-end text-success">Credit (₹)</th>
                        <th class="text-end fw-bold">Balance (₹)</th>
                        <th>Mode</th>
                        <th class="text-center" style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entries as $index => $item)
                    @php
                        $isCredit = strtolower($item->transaction_type) === 'credit';
                        $amt = (float)$item->amount;
                    @endphp
                    <tr>
                        <td>{{ $entries->firstItem() + $index }}</td>
                        <td><span class="text-nowrap">{{ $item->date ? $item->date->format('d M Y') : '-' }}</span></td>
                        <td>
                            @if($item->voucher_no)
                                <span class="badge bg-label-secondary font-monospace">{{ $item->voucher_no }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td><strong>{{ $item->account_name ?: 'Cash in Hand' }}</strong></td>
                        <td><small class="text-muted">{{ $item->branch?->name ?: 'N/A' }}</small></td>
                        <td>
                            @if($item->category)
                                <span class="badge bg-label-info">{{ $item->category }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div style="max-width: 320px; white-space: normal;" title="{{ $item->description }}">
                                {{ $item->description }}
                            </div>
                        </td>
                        <td class="text-end">
                            @if(!$isCredit)
                                <strong class="text-danger">₹ {{ number_format($amt, 2) }}</strong>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($isCredit)
                                <strong class="text-success">₹ {{ number_format($amt, 2) }}</strong>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-end fw-bold text-{{ ($item->running_balance ?? 0) >= 0 ? 'dark' : 'danger' }}">
                            ₹ {{ number_format($item->running_balance ?? 0, 2) }}
                        </td>
                        <td><span class="badge bg-label-dark">{{ $item->payment_mode ?: 'Cash' }}</span></td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                @if(auth()->user()->can('edit cash entry') || auth()->user()->isSuperAdmin())
                                <button type="button" class="btn btn-xs btn-outline-primary p-1 btn-edit-entry" 
                                    data-id="{{ $item->id }}"
                                    data-account="{{ $item->account_name }}"
                                    data-type="{{ $item->transaction_type }}"
                                    data-amount="{{ $item->amount }}"
                                    data-date="{{ $item->date ? $item->date->format('Y-m-d') : '' }}"
                                    data-voucher="{{ $item->voucher_no }}"
                                    data-category="{{ $item->category }}"
                                    data-mode="{{ $item->payment_mode }}"
                                    data-branch="{{ $item->branch_id }}"
                                    data-description="{{ $item->description }}"
                                    title="Edit Entry">
                                    <i class="bx bx-edit"></i>
                                </button>
                                @endif
                                @if(auth()->user()->can('delete cash entry') || auth()->user()->isSuperAdmin())
                                <form action="{{ route('admin.cash-ledger.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this cash ledger entry?');" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-outline-danger p-1" title="Delete Entry">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="text-center py-5 text-muted">
                            <i class="bx bx-wallet fs-1 d-block mb-2 text-secondary"></i>
                            No cash ledger transactions found. Click <strong>"Add Cash Entry"</strong> to record incoming or expense cash.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($entries->isNotEmpty())
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="7" class="text-end">Current Page Total:</td>
                        <td class="text-end text-danger">
                            ₹ {{ number_format($entries->where('transaction_type', 'debit')->sum('amount'), 2) }}
                        </td>
                        <td class="text-end text-success">
                            ₹ {{ number_format($entries->where('transaction_type', 'credit')->sum('amount'), 2) }}
                        </td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
        @if($entries->hasPages())
        <div class="card-footer py-2 border-top">
            {{ $entries->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Add Cash Entry Modal -->
<div class="modal fade" id="addEntryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form action="{{ route('admin.cash-ledger.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="bx bx-plus-circle me-1"></i> Add Cash Transaction</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Transaction Type <span class="text-danger">*</span></label>
                            <select name="transaction_type" class="form-select fw-bold" required>
                                <option value="debit" class="text-danger fw-bold">&#x25BC; Debit (Expense / Cash Out)</option>
                                <option value="credit" class="text-success fw-bold">&#x25B2; Credit (Deposit / Cash In)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Amount (₹) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" step="0.01" min="0.01" name="amount" class="form-control form-control-lg fw-bold" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Account Name <span class="text-danger">*</span></label>
                            <input type="text" name="account_name" class="form-control" list="account_suggestions" value="Cash in Hand" placeholder="e.g. Cash in Hand, Petty Cash" required>
                            <datalist id="account_suggestions">
                                <option value="Cash in Hand">
                                <option value="Main Branch Cash">
                                <option value="Petty Cash">
                                <option value="Driver Cash Advance">
                                <option value="Head Office Cash">
                            </datalist>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Voucher / Receipt No</label>
                            <input type="text" name="voucher_no" class="form-control" placeholder="Optional ref/voucher #">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Category</label>
                            <input type="text" name="category" class="form-control" list="category_suggestions" placeholder="e.g. Office Expense, Fuel, Client Payment">
                            <datalist id="category_suggestions">
                                <option value="Office Expense">
                                <option value="Client Payment">
                                <option value="Freight Advance">
                                <option value="Driver Advance">
                                <option value="Fuel / FastTag">
                                <option value="Bank Withdrawal">
                                <option value="Capital / Deposit">
                                <option value="Miscellaneous">
                            </datalist>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Payment Mode</label>
                            <select name="payment_mode" class="form-select">
                                <option value="Cash">Cash</option>
                                <option value="Cheque">Cheque</option>
                                <option value="UPI">UPI</option>
                                <option value="NEFT/RTGS">NEFT / RTGS</option>
                            </select>
                        </div>

                        @if(isset($branches) && $branches->count() > 1)
                        <div class="col-md-6">
                            <label class="form-label">Branch</label>
                            <select name="branch_id" class="form-select">
                                @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ $branchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div class="col-12">
                            <label class="form-label fw-bold">Description / Particulars <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Enter reason, party name, or particulars..." required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Save Cash Entry</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Cash Entry Modal -->
<div class="modal fade" id="editEntryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form id="editEntryForm" action="" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="bx bx-edit me-1"></i> Edit Cash Transaction</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" id="ee_date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Transaction Type <span class="text-danger">*</span></label>
                            <select name="transaction_type" id="ee_type" class="form-select fw-bold" required>
                                <option value="debit" class="text-danger fw-bold">&#x25BC; Debit (Expense / Cash Out)</option>
                                <option value="credit" class="text-success fw-bold">&#x25B2; Credit (Deposit / Cash In)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Amount (₹) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" step="0.01" min="0.01" name="amount" id="ee_amount" class="form-control form-control-lg fw-bold" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Account Name <span class="text-danger">*</span></label>
                            <input type="text" name="account_name" id="ee_account" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Voucher / Receipt No</label>
                            <input type="text" name="voucher_no" id="ee_voucher" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Category</label>
                            <input type="text" name="category" id="ee_category" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Payment Mode</label>
                            <select name="payment_mode" id="ee_mode" class="form-select">
                                <option value="Cash">Cash</option>
                                <option value="Cheque">Cheque</option>
                                <option value="UPI">UPI</option>
                                <option value="NEFT/RTGS">NEFT / RTGS</option>
                            </select>
                        </div>

                        @if(isset($branches) && $branches->count() > 1)
                        <div class="col-md-6">
                            <label class="form-label">Branch</label>
                            <select name="branch_id" id="ee_branch" class="form-select">
                                @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div class="col-12">
                            <label class="form-label fw-bold">Description / Particulars <span class="text-danger">*</span></label>
                            <textarea name="description" id="ee_description" class="form-control" rows="2" required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Update Entry</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    $('.btn-edit-entry').click(function() {
        const id = $(this).data('id');
        const url = "{{ url('admin/cash-ledger') }}/" + id;
        $('#editEntryForm').attr('action', url);

        $('#ee_date').val($(this).data('date'));
        $('#ee_type').val($(this).data('type'));
        $('#ee_amount').val($(this).data('amount'));
        $('#ee_account').val($(this).data('account'));
        $('#ee_voucher').val($(this).data('voucher'));
        $('#ee_category').val($(this).data('category'));
        $('#ee_mode').val($(this).data('mode'));
        $('#ee_branch').val($(this).data('branch'));
        $('#ee_description').val($(this).data('description'));

        $('#editEntryModal').modal('show');
    });
});
</script>
@endsection
