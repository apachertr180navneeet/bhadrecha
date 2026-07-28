@extends('admin.layouts.app')

@section('style')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single {
        height: calc(1.5em + .75rem + 2px);
        border: 1px solid #d9dee3;
        border-radius: var(--bs-border-radius);
        font-size: .875rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: calc(1.5em + .75rem);
        padding-left: .5rem;
        color: #697a8d;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: calc(1.5em + .75rem);
    }
    .metric-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .table-responsive {
        border-radius: 8px;
        overflow: hidden;
    }
    .nav-tabs-custom .nav-link {
        border: none;
        border-bottom: 3px solid transparent;
        color: #697a8d;
        font-weight: 500;
        padding: 0.8rem 1.2rem;
    }
    .nav-tabs-custom .nav-link.active {
        border-bottom-color: #696cff;
        color: #696cff;
        background: transparent;
    }
</style>
@endsection

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
@php
    $currentRoute = request()->routeIs('admin.reports.*') ? 'admin.reports.adblue-outstanding' : 'admin.transport.trips.adblue-outstanding';
@endphp

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1">
                @if(request()->routeIs('admin.reports.*')) AdBlue Outstanding Report @else AdBlue Outstanding Ledger @endif
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    @if(request()->routeIs('admin.reports.*'))
                        <li class="breadcrumb-item"><a href="#">Reports</a></li>
                        <li class="breadcrumb-item active">AdBlue Outstanding</li>
                    @else
                        <li class="breadcrumb-item"><a href="{{ route('admin.transport.trips.index') }}">Transport</a></li>
                        <li class="breadcrumb-item active">AdBlue Outstanding</li>
                    @endif
                </ol>
            </nav>
        </div>
        <div>
            @if(!request()->routeIs('admin.reports.*'))
            <button type="button" class="btn btn-primary" onclick="openPaymentModal()">
                <i class="bx bx-plus me-1"></i> Record AdBlue Payment (Credit)
            </button>
            @endif
        </div>
    </div>

    <!-- Summary Metrics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm metric-card bg-label-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold text-info">Total AdBlue Purchase</span>
                        <i class="bx bx-water fs-4 text-info"></i>
                    </div>
                    <h3 class="card-title mb-1 text-info">₹{{ number_format($totalAdBlueAmountAll, 2) }}</h3>
                    <p class="mb-0 text-muted small">Urea transactions from trips</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm metric-card bg-label-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold text-success">Total Payments Made</span>
                        <i class="bx bx-credit-card fs-4 text-success"></i>
                    </div>
                    <h3 class="card-title mb-1 text-success">₹{{ number_format($totalPaymentAmountAll, 2) }}</h3>
                    <p class="mb-0 text-muted small">Credit payments / deposits</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-12">
            @php
                $netOutstanding = ($openingBalanceAll ?? 0.0) + $totalAdBlueAmountAll - $totalPaymentAmountAll;
                $cardClass = $netOutstanding > 0 ? 'bg-label-danger' : 'bg-label-success';
                $textClass = $netOutstanding > 0 ? 'text-danger' : 'text-success';
            @endphp
            <div class="card border-0 shadow-sm metric-card {{ $cardClass }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold {{ $textClass }}">Net Outstanding Balance</span>
                        <i class="bx bx-money fs-4 {{ $textClass }}"></i>
                    </div>
                    <h3 class="card-title mb-1 {{ $textClass }}">₹{{ number_format($netOutstanding, 2) }}</h3>
                    <p class="mb-0 text-muted small">Amount owed to AdBlue companies</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route($currentRoute) }}" id="filterForm">
                <div class="row g-3">
                    @if(isset($companies) && count($companies) > 0)
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label small fw-bold">Operating Company</label>
                        <select name="company_id" id="filter_company_id" class="form-select select2">
                            <option value="">All Operating Companies</option>
                            @foreach($companies as $comp)
                            <option value="{{ $comp->id }}" {{ request('company_id') == $comp->id ? 'selected' : '' }}>{{ $comp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label small fw-bold">AdBlue Company</label>
                        <select name="adblue_company_id" id="filter_adblue_company_id" class="form-select select2">
                            <option value="">All Companies</option>
                            @foreach($adblueCompanies as $company)
                            <option value="{{ $company->id }}" {{ request('adblue_company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label small fw-bold">Truck (Vehicle)</label>
                        <select name="vehicle_id" class="form-select select2">
                            <option value="">All Trucks</option>
                            @foreach($vehicleList as $v)
                            <option value="{{ $v->id }}" {{ request('vehicle_id') == $v->id ? 'selected' : '' }}>{{ $v->vehicle_number }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label small fw-bold">LR No</label>
                        <input type="text" name="lr_no" class="form-control" placeholder="Search LR No" value="{{ request('lr_no') }}">
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label small fw-bold">From Date</label>
                        <input type="date" max="9999-12-31" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label small fw-bold">To Date</label>
                        <input type="date" max="9999-12-31" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter-alt me-1"></i> Apply Filters</button>
                        <a href="{{ route($currentRoute) }}" class="btn btn-outline-secondary w-100"><i class="bx bx-refresh me-1"></i> Reset</a>
                    </div>
                    <div class="col-md-12 d-flex justify-content-end gap-2 mt-3">
                        <button type="submit" name="export" value="excel" class="btn btn-success"><i class="bx bx-spreadsheet me-1"></i> Export Excel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabs Layout -->
    <div class="card shadow-sm border-0">
        <div class="card-header p-0">
            <ul class="nav nav-tabs nav-tabs-custom border-bottom" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">
                        <i class="bx bx-list-ul me-1"></i> Companies Overview
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="ledger-tab" data-bs-toggle="tab" data-bs-target="#ledger" type="button" role="tab">
                        <i class="bx bx-book-open me-1"></i> Transaction Ledger
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="payments-tab" data-bs-toggle="tab" data-bs-target="#payments" type="button" role="tab">
                        <i class="bx bx-credit-card me-1"></i> Payment Logs (Credits)
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content p-0">
                <!-- Tab 1: Overview -->
                <div class="tab-pane fade show active" id="overview" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-sm-compact">
                            <thead class="table-light">
                                <tr>
                                    <th>AdBlue Company</th>
                                    @if(request()->filled('date_from'))
                                    <th class="text-end">Opening Balance</th>
                                    @endif
                                    <th class="text-end">Total Qty (L)</th>
                                    <th class="text-end">AdBlue Amount (+)</th>
                                    <th class="text-end">Payments Made (-)</th>
                                    <th class="text-end">Net Outstanding</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($overviewData as $item)
                                <tr>
                                    <td class="fw-semibold text-dark">{{ $item['company_name'] }}</td>
                                    @if(request()->filled('date_from'))
                                    <td class="text-end fw-semibold">₹{{ number_format($item['opening_balance'] ?? 0, 2) }}</td>
                                    @endif
                                    <td class="text-end">{{ number_format($item['total_qty'], 2) }} L</td>
                                    <td class="text-end">₹{{ number_format($item['adblue_amount'], 2) }}</td>
                                    <td class="text-end text-success">₹{{ number_format($item['payment_amount'], 2) }}</td>
                                    <td class="text-end">
                                        <span class="badge {{ $item['net_outstanding'] > 0 ? 'bg-label-danger' : 'bg-label-success' }} fw-bold">
                                            ₹{{ number_format($item['net_outstanding'], 2) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button class="btn btn-sm btn-icon btn-outline-primary" onclick="viewLedgerForCompany({{ $item['adblue_company_id'] }})" title="View Ledger">
                                                <i class="bx bx-book-open"></i>
                                            </button>
                                            @if(!request()->routeIs('admin.reports.*'))
                                            <button class="btn btn-sm btn-icon btn-outline-success" onclick="recordPaymentForCompany({{ $item['adblue_company_id'] }})" title="Record Payment">
                                                <i class="bx bx-credit-card"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="{{ request()->routeIs('admin.reports.*') ? 6 : 7 }}" class="text-center text-muted py-4">No AdBlue outstandings found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab 2: Transaction Ledger -->
                <div class="tab-pane fade" id="ledger" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Ref / Method</th>
                                    <th>Vehicle</th>
                                    <th>Company</th>
                                    <th class="text-end">Qty (L)</th>
                                    <th class="text-end">Debit (Purchase)</th>
                                    <th class="text-end">Credit (Paid)</th>
                                    <th class="text-end">Running Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ledgerItems as $item)
                                <tr class="{{ $item['type'] === 'Opening Balance' ? 'table-warning text-dark font-weight-bold' : '' }}">
                                    <td>{{ $item['date'] ? date('d-m-Y', strtotime($item['date'])) : '-' }}</td>
                                    <td>
                                        @if($item['type'] === 'AdBlue Purchase')
                                            <span class="badge bg-label-info"><i class="bx bx-water me-1"></i>Purchase</span>
                                        @elseif($item['type'] === 'Payment')
                                            <span class="badge bg-label-success"><i class="bx bx-credit-card me-1"></i>Payment</span>
                                        @else
                                            <span class="badge bg-label-warning"><i class="bx bx-log-in me-1"></i>Opening</span>
                                        @endif
                                    </td>
                                    <td>{{ $item['ref_no'] }}</td>
                                    <td class="fw-semibold text-dark">{{ $item['vehicle'] }}</td>
                                    <td>{{ $item['company'] }}</td>
                                    <td class="text-end">
                                        @if($item['qty'] > 0)
                                            {{ number_format($item['qty'], 2) }} L
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-end font-monospace">
                                        @if($item['debit'] > 0)
                                            ₹{{ number_format($item['debit'], 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-end font-monospace text-success">
                                        @if($item['credit'] > 0)
                                            ₹{{ number_format($item['credit'], 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-end font-monospace fw-bold {{ $item['balance'] > 0 ? 'text-danger' : 'text-success' }}">
                                        ₹{{ number_format($item['balance'], 2) }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">No transactions found. Try expanding filter options.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab 3: Payment Logs -->
                <div class="tab-pane fade" id="payments" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Operating Company</th>
                                    <th>AdBlue Company</th>
                                    <th>Method</th>
                                    <th class="text-end">Amount</th>
                                    <th>Remark</th>
                                    @if(!request()->routeIs('admin.reports.*'))
                                    <th class="text-center">Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments as $p)
                                <tr>
                                    <td>{{ $p->date ? $p->date->format('d-m-Y') : '-' }}</td>
                                    <td><span class="badge bg-label-info">{{ $p->company->name ?? '-' }}</span></td>
                                    <td class="fw-semibold text-primary">{{ $p->adblueCompany->name ?? '-' }}</td>
                                    <td><span class="badge bg-label-secondary">{{ $p->payment_method ?? 'Bank Transfer' }}</span></td>
                                    <td class="text-end fw-bold text-success">₹{{ number_format($p->amount, 2) }}</td>
                                    <td>{{ $p->remark ?? '-' }}</td>
                                    @if(!request()->routeIs('admin.reports.*'))
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button class="btn btn-sm btn-icon btn-outline-warning" onclick="editPayment({{ $p->id }})" title="Edit">
                                                <i class="bx bx-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-icon btn-outline-danger" onclick="deletePayment({{ $p->id }})" title="Delete">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                    @endif
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="{{ request()->routeIs('admin.reports.*') ? 6 : 7 }}" class="text-center text-muted py-4">No payment logs found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if(method_exists($payments, 'links'))
                    <div class="mt-3">
                        {{ $payments->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="paymentForm" onsubmit="savePayment(event)">
            @csrf
            <input type="hidden" name="_method" id="payment_method_field" value="POST">
            <input type="hidden" name="id" id="payment_id">

            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="paymentModalTitle">Record AdBlue Payment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Operating Company *</label>
                        <select name="company_id" id="pay_company_id" class="form-select modal-select2" required>
                            <option value="">Select Company</option>
                            @if(isset($companies))
                            @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Payment Date *</label>
                        <input type="date" max="9999-12-31" name="date" id="pay_date" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">AdBlue Company *</label>
                        <select name="adblue_company_id" id="pay_adblue_company_id" class="form-select modal-select2" required>
                            <option value="">Select Company</option>
                            @foreach($adblueCompanies as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Amount Paid (₹) *</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" step="0.01" name="amount" id="pay_amount" class="form-control" required placeholder="0.00" min="0.01">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Payment Method</label>
                        <select name="payment_method" id="pay_method" class="form-select">
                            <option value="Bank Transfer">Bank Transfer (IMPS/NEFT/RTGS)</option>
                            <option value="UPI">UPI (PhonePe/GPay/Paytm)</option>
                            <option value="Cash">Cash</option>
                            <option value="Cheque">Cheque</option>
                            <option value="Direct Deposit">Direct Deposit</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Remark / Reference</label>
                        <textarea name="remark" id="pay_remark" class="form-control" rows="2" placeholder="e.g. Reference No, Cheque No, deposited by Harish Bhaiya" maxlength="500"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="savePaymentBtn">Save Record</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%'
        });

        // Initialize select2 inside modal
        $('.modal-select2').select2({
            dropdownParent: $('#paymentModal'),
            width: '100%'
        });
    });

    function openPaymentModal() {
        $('#payment_id').val('');
        $('#paymentModalTitle').text('Record AdBlue Payment');
        $('#paymentForm')[0].reset();
        $('#payment_method_field').val('POST');
        $('#pay_date').val(new Date().toISOString().substring(0, 10));
        
        var filterComp = $('#filter_company_id').length ? $('#filter_company_id').val() : '';
        var sessComp = '{{ session("current_company_id") }}';
        
        if (filterComp) {
            $('#pay_company_id').val(filterComp).trigger('change');
        } else if (sessComp && sessComp !== 'all') {
            $('#pay_company_id').val(sessComp).trigger('change');
        } else {
            $('#pay_company_id').val('').trigger('change');
        }

        $('#pay_adblue_company_id').val('').trigger('change');
        
        $('#paymentModal').modal('show');
    }

    function recordPaymentForCompany(companyId) {
        openPaymentModal();
        $('#pay_adblue_company_id').val(companyId).trigger('change');
    }

    function viewLedgerForCompany(companyId) {
        $('#filter_adblue_company_id').val(companyId).trigger('change');
        setTimeout(function() {
            $('#filterForm').submit();
        }, 100);
    }

    function savePayment(e) {
        e.preventDefault();
        var id = $('#payment_id').val();
        var isEdit = id !== '';
        var url = isEdit ? "{{ url('admin/transport/trips/adblue-payments') }}/" + id : "{{ route('admin.transport.trips.adblue-payments.store') }}";
        
        $('#payment_method_field').val(isEdit ? 'PUT' : 'POST');

        var formData = $('#paymentForm').serialize();

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#paymentModal').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Success!', text: response.message, toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
                    setTimeout(function(){ window.location.reload(); }, 1500);
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Error saving payment: ' + response.message, toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
                }
            },
            error: function(xhr) {
                var errors = xhr.responseJSON.errors;
                var errMessage = '';
                if (errors) {
                    $.each(errors, function(key, val) {
                        errMessage += val[0] + '<br>';
                    });
                    Swal.fire({ icon: 'error', title: 'Validation errors', html: errMessage, toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred. Please try again.', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
                }
            }
        });
    }

    function editPayment(id) {
        $.ajax({
            url: "{{ url('admin/transport/trips/adblue-payments') }}/" + id + "/edit",
            type: 'GET',
            success: function(payment) {
                $('#payment_id').val(payment.id);
                $('#paymentModalTitle').text('Edit Payment Record');
                $('#pay_date').val(payment.date.substring(0, 10));
                $('#pay_amount').val(payment.amount);
                $('#pay_method').val(payment.payment_method);
                $('#pay_remark').val(payment.remark);
                $('#payment_method_field').val('PUT');

                $('#paymentModal').modal('show');

                setTimeout(function() {
                    if ($('#pay_company_id').length) {
                        $('#pay_company_id').val(payment.company_id).trigger('change');
                    }
                    $('#pay_adblue_company_id').val(payment.adblue_company_id).trigger('change');
                }, 100);
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Could not fetch payment record.', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
            }
        });
    }

    function deletePayment(id) {
        if (confirm('Are you sure you want to delete this payment record? This action will restore outstanding balance.')) {
            $.ajax({
                url: "{{ url('admin/transport/trips/adblue-payments') }}/" + id,
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    _method: 'DELETE'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({ icon: 'success', title: 'Success!', text: response.message, toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
                        setTimeout(function(){ window.location.reload(); }, 1500);
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Could not delete payment: ' + response.message, toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
                    }
                },
                error: function() {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred while deleting the payment record.', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
                }
            });
        }
    }
</script>
@endsection
