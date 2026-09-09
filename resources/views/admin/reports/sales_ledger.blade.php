@extends('admin.layouts.app')

@section('style')
<style>
    .metric-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .table-responsive {
        border-radius: 8px;
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
    .bill-no-cell {
        display: flex;
        align-items: center;
        gap: 6px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1">Sales Ledger & Outstanding Report</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Reports</a></li>
                    <li class="breadcrumb-item active">Sales Ledger</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <div class="btn-group">
                <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bx bx-file me-1"></i> Export Excel
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.reports.sales-ledger.export-excel', array_merge(request()->all(), ['export_type' => 'detailed'])) }}">
                            <i class="bx bx-list-ul me-1 text-primary"></i> Detailed Ledger Export
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.reports.sales-ledger.export-excel', array_merge(request()->all(), ['export_type' => 'branch_overview'])) }}">
                            <i class="bx bx-buildings me-1 text-success"></i> Branch Overview Export
                        </a>
                    </li>
                </ul>
            </div>
            @if(auth()->user()->can('edit sales ledger') || auth()->user()->isSuperAdmin())
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#receiveAmountModal">
                <i class="bx bx-rupee me-1"></i> Receive Amount
            </button>
            @endif
            <a href="{{ route('admin.reports.sales-ledger.history') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bx bx-history me-1"></i> Receiving History
            </a>
            <a href="{{ route('admin.reports.tds-report') }}" class="btn btn-outline-info btn-sm">
                <i class="bx bx-receipt me-1"></i> TDS Report
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bx bx-check-circle fs-4 me-2"></i>
                <div>{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bx bx-error-circle fs-4 me-2"></i>
                <div>{{ session('error') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Summary Metrics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-2 col-sm-6 col-12">
            <div class="card border-0 shadow-sm metric-card bg-label-secondary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold text-secondary small">Total Base Amount</span>
                        <i class="bx bx-calculator fs-4 text-secondary"></i>
                    </div>
                    <h4 class="card-title mb-1 text-secondary">₹{{ number_format($totalAmountWithoutGstAll ?? 0, 2) }}</h4>
                    <p class="mb-0 text-muted small">w/o GST</p>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-6 col-12">
            <div class="card border-0 shadow-sm metric-card bg-label-info h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold text-info small">Total GST</span>
                        <i class="bx bx-receipt fs-4 text-info"></i>
                    </div>
                    <h4 class="card-title mb-1 text-info">₹{{ number_format($totalGstAll ?? 0, 2) }}</h4>
                    <p class="mb-0 text-muted small">GST Charged</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="card border-0 shadow-sm metric-card bg-label-primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold text-primary">Total Receivable Amount</span>
                        <i class="bx bx-money fs-4 text-primary"></i>
                    </div>
                    <h4 class="card-title mb-1 text-primary">₹{{ number_format($totalReceivableAll ?? 0, 2) }}</h4>
                    <p class="mb-0 text-muted small">Net Payable Amount</p>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-6 col-12">
            <div class="card border-0 shadow-sm metric-card bg-label-success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold text-success small">Total Received</span>
                        <i class="bx bx-check-double fs-4 text-success"></i>
                    </div>
                    <h4 class="card-title mb-1 text-success">₹{{ number_format($totalReceivedAll ?? 0, 2) }}</h4>
                    <p class="mb-0 text-muted small">Recv. Amt + GST</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            @php
                $netOut = $netOutstandingAll ?? 0.0;
                $cardClass = $netOut > 0 ? 'bg-label-danger' : 'bg-label-success';
                $textClass = $netOut > 0 ? 'text-danger' : 'text-success';
            @endphp
            <div class="card border-0 shadow-sm metric-card {{ $cardClass }} h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold {{ $textClass }}">Net Outstanding</span>
                        <i class="bx bx-wallet fs-4 {{ $textClass }}"></i>
                    </div>
                    <h4 class="card-title mb-1 {{ $textClass }}">₹{{ number_format($netOut, 2) }}</h4>
                    <p class="mb-0 text-muted small">Pending from Customers</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header border-bottom py-3">
            <h6 class="mb-0 fw-bold"><i class="bx bx-filter-alt me-1"></i> Filter Records</h6>
        </div>
        <div class="card-body mt-3">
            <form method="GET" action="{{ route('admin.reports.sales-ledger') }}" class="row g-3" id="salesLedgerFilterForm">
                @if(auth()->user()->isSuperAdmin())
                <div class="col-md-3 col-sm-6">
                    <label class="form-label small fw-bold">Company</label>
                    <select name="company_id" class="form-select">
                        <option value="all">All Companies</option>
                        @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-3 col-sm-6">
                    <label class="form-label small fw-bold">Branch</label>
                    <select name="branch_id" class="form-select">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label small fw-bold">Consigner</label>
                    <select name="consignor_id" class="form-select">
                        <option value="">All Consigners</option>
                        @foreach($consignors as $consignor)
                        <option value="{{ $consignor->id }}" {{ request('consignor_id') == $consignor->id ? 'selected' : '' }}>{{ $consignor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label small fw-bold">Bill Number</label>
                    <input type="text" name="bill_number" class="form-control" value="{{ request('bill_number') }}" placeholder="Search by Bill No">
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label small fw-bold">Bill To</label>
                    <input type="text" name="bill_to" class="form-control" value="{{ request('bill_to') }}" placeholder="Search by Bill To">
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label small fw-bold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label small fw-bold">Receiving Amount</label>
                    <select name="receiving_amount_status" class="form-select">
                        <option value="">All</option>
                        <option value="paid" {{ request('receiving_amount_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="unpaid" {{ request('receiving_amount_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    </select>
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label small fw-bold">Receiving GST</label>
                    <select name="receiving_gst_status" class="form-select">
                        <option value="">All</option>
                        <option value="paid" {{ request('receiving_gst_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="unpaid" {{ request('receiving_gst_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    </select>
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label small fw-bold">From Date</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label small fw-bold">To Date</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label small fw-bold">Date Sort</label>
                    <select name="date_sort" class="form-select">
                        <option value="latest" {{ request('date_sort', 'latest') == 'latest' ? 'selected' : '' }}>Latest to Oldest</option>
                        <option value="oldest" {{ request('date_sort') == 'oldest' ? 'selected' : '' }}>Oldest to Latest</option>
                    </select>
                </div>
                <div class="col-md-3 col-sm-6 d-flex align-items-end">
                    <div class="d-flex gap-2 w-100">
                        <button type="submit" class="btn btn-primary w-50"><i class="bx bx-search me-1"></i> Apply</button>
                        <a href="{{ route('admin.reports.sales-ledger') }}" class="btn btn-outline-secondary w-50"><i class="bx bx-refresh me-1"></i> Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabs Layout -->
    <div class="card shadow-sm border-0">
        <div class="card-header p-0">
            <ul class="nav nav-tabs nav-tabs-custom border-bottom" id="salesLedgerTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="branch-overview-tab" data-bs-toggle="tab" data-bs-target="#branch-overview" type="button" role="tab">
                        <i class="bx bx-buildings me-1"></i> Branch Overview (Outstanding Report)
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="sales-ledger-tab" data-bs-toggle="tab" data-bs-target="#sales-ledger" type="button" role="tab">
                        <i class="bx bx-book-open me-1"></i> Sales Ledger (Bills & Invoices)
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="payment-logs-tab" data-bs-toggle="tab" data-bs-target="#payment-logs" type="button" role="tab">
                        <i class="bx bx-credit-card me-1"></i> Payment Logs (Receiving History)
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content p-0">
                <!-- TAB 1: Branch Overview (Outstanding Report) -->
                <div class="tab-pane fade show active" id="branch-overview" role="tabpanel">
                    <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-3">
                        <i class="bx bx-info-circle me-2 fs-4"></i>
                        <span>This report provides a branch-wise calculation of total receivable amount, GST, received amounts, and outstanding balances.</span>
                    </div>

                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover align-middle table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>S.No</th>
                                    <th class="text-center" style="width: 110px;">Action</th>
                                    <th>Company</th>
                                    <th>Branch Name</th>
                                    <th class="text-center">Total Bills</th>
                                    <th class="text-end">Total Amt (w/o GST)</th>
                                    <th class="text-end">Total GST</th>
                                    <th class="text-end">Total Receivable</th>
                                    <th class="text-end">Total Received</th>
                                    <th class="text-end">Outstanding Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $bSno = 1;
                                    $bSumBills = 0; $bSumWoGst = 0; $bSumGst = 0; $bSumRecv = 0; $bSumTotRecv = 0; $bSumOut = 0;
                                @endphp
                                @forelse($branchOverviewData as $item)
                                @php
                                    $bSumBills += $item['total_bills'];
                                    $bSumWoGst += $item['total_amount_without_gst'];
                                    $bSumGst += $item['total_gst'];
                                    $bSumRecv += $item['total_receivable'];
                                    $bSumTotRecv += $item['total_received'];
                                    $bSumOut += $item['outstanding_amount'];
                                @endphp
                                <tr>
                                    <td>{{ $bSno++ }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.reports.sales-ledger', array_merge(request()->except('branch_id', 'page'), ['branch_id' => $item['branch_id']])) }}#sales-ledger" class="btn btn-sm btn-outline-primary" title="View Invoices for this Branch">
                                            <i class="bx bx-list-ul me-1"></i> View Bills
                                        </a>
                                    </td>
                                    <td><span class="fw-semibold text-dark">{{ $item['company_name'] }}</span></td>
                                    <td><span class="fw-bold text-primary">{{ $item['branch_name'] }}</span></td>
                                    <td class="text-center"><span class="badge bg-label-info">{{ $item['total_bills'] }}</span></td>
                                    <td class="text-end fw-semibold">₹ {{ number_format($item['total_amount_without_gst'], 2) }}</td>
                                    <td class="text-end">₹ {{ number_format($item['total_gst'], 2) }}</td>
                                    <td class="text-end fw-bold text-primary">₹ {{ number_format($item['total_receivable'], 2) }}</td>
                                    <td class="text-end fw-bold text-success">₹ {{ number_format($item['total_received'], 2) }}</td>
                                    <td class="text-end">
                                        <span class="badge {{ $item['outstanding_amount'] > 0 ? 'bg-label-danger' : 'bg-label-success' }} fw-bold fs-6">
                                            ₹ {{ number_format($item['outstanding_amount'], 2) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4 text-muted">No branch records found for the selected filters.</td>
                                </tr>
                                @endforelse
                            </tbody>
                            @if(count($branchOverviewData) > 0)
                            <tfoot class="table-light fw-bold border-top border-dark">
                                <tr>
                                    <th colspan="4" class="text-end">GRAND TOTAL:</th>
                                    <th class="text-center"><span class="badge bg-primary">{{ $bSumBills }}</span></th>
                                    <th class="text-end">₹ {{ number_format($bSumWoGst, 2) }}</th>
                                    <th class="text-end">₹ {{ number_format($bSumGst, 2) }}</th>
                                    <th class="text-end text-primary">₹ {{ number_format($bSumRecv, 2) }}</th>
                                    <th class="text-end text-success">₹ {{ number_format($bSumTotRecv, 2) }}</th>
                                    <th class="text-end text-danger">₹ {{ number_format($bSumOut, 2) }}</th>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>

                <!-- TAB 2: Sales Ledger (Bills & Invoices) -->
                <div class="tab-pane fade" id="sales-ledger" role="tabpanel">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>S.No</th>
                                    @if(auth()->user()->can('edit sales ledger') || auth()->user()->isSuperAdmin())
                                    <th class="text-center" style="width: 110px;">Action</th>
                                    @endif
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['date_sort' => request('date_sort') === 'oldest' ? 'latest' : 'oldest']) }}#sales-ledger" class="text-dark text-decoration-none d-inline-flex align-items-center gap-1">
                                            Date
                                            <i class="bx {{ request('date_sort') === 'oldest' ? 'bx-sort-up text-primary' : 'bx-sort-down text-primary' }}"></i>
                                        </a>
                                    </th>
                                    <th>Company</th>
                                    <th>Branch</th>
                                    <th>Bill No (Edit)</th>
                                    <th>Bill To</th>
                                    <th class="text-end">Total Amt<br><small class="text-muted">(w/o GST)</small></th>
                                    <th class="text-end">GST</th>
                                    <th class="text-end">TDS</th>
                                    <th class="text-end">Deduction</th>
                                    <th class="text-end">Net Payable</th>
                                    <th class="text-end">Recv. Amt</th>
                                    <th class="text-end">Recv. GST</th>
                                    <th class="text-end">Total Recv.</th>
                                    <th class="text-end">Outstanding</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @forelse($invoices as $index => $invoice)
                                    @php
                                        $amountWithoutGst = $invoice->total_freight + $invoice->total_other;
                                        $netPayable = $invoice->net_payable_amount;
                                        $totalReceived = $invoice->total_received_amount;
                                        $outstanding = $invoice->outstanding_amount;
                                        $displayBillNo = !empty($invoice->bill_number) ? $invoice->bill_number : ($invoice->invoice_no ?? ('INV-' . $invoice->id));
                                    @endphp
                                    <tr>
                                        <td>{{ $invoices->firstItem() + $index }}</td>
                                        @if(auth()->user()->can('edit sales ledger') || auth()->user()->isSuperAdmin())
                                        <td class="text-center">
                                            @if($invoice->billReceivings->isNotEmpty())
                                                @if($invoice->billReceivings->count() === 1)
                                                    @php $singleRec = $invoice->billReceivings->first(); @endphp
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-outline-primary btn-sm btn-edit-receiving" data-id="{{ $singleRec->id }}" title="Edit Receive Amount">
                                                            <i class="bx bx-edit me-1"></i> Edit Recv
                                                        </button>
                                                        @if($invoice->status !== 'paid')
                                                        <button type="button" class="btn btn-outline-success btn-sm btn-add-receiving" data-invoice-id="{{ $invoice->id }}" title="Add Another Receiving">
                                                            <i class="bx bx-plus"></i>
                                                        </button>
                                                        @endif
                                                    </div>
                                                @else
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="bx bx-edit me-1"></i> Edit ({{ $invoice->billReceivings->count() }})
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            @foreach($invoice->billReceivings as $rec)
                                                                <li>
                                                                    <a class="dropdown-item btn-edit-receiving" href="javascript:void(0);" data-id="{{ $rec->id }}">
                                                                        <i class="bx bx-receipt me-1 text-primary"></i> {{ $rec->date?->format('d-m-Y') }}: ₹{{ number_format($rec->receiving_amount + $rec->receiving_gst, 2) }}
                                                                    </a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                        @if($invoice->status !== 'paid')
                                                        <button type="button" class="btn btn-outline-success btn-sm btn-add-receiving" data-invoice-id="{{ $invoice->id }}" title="Add Another Receiving">
                                                            <i class="bx bx-plus"></i>
                                                        </button>
                                                        @endif
                                                    </div>
                                                @endif
                                            @else
                                                @if($invoice->status !== 'paid')
                                                <button type="button" class="btn btn-outline-success btn-sm btn-add-receiving" data-invoice-id="{{ $invoice->id }}" title="Receive Amount">
                                                    <i class="bx bx-rupee me-1"></i> Receive
                                                </button>
                                                @else
                                                <span class="text-muted small">-</span>
                                                @endif
                                            @endif
                                        </td>
                                        @endif
                                        <td>{{ $invoice->invoice_date?->format('d-m-Y') }}</td>
                                        <td>{{ $invoice->company?->name ?? 'N/A' }}</td>
                                        <td>{{ $invoice->branch?->name ?? 'N/A' }}</td>
                                        <td>
                                            <div class="bill-no-cell">
                                                <a href="{{ route('admin.transport.invoices.show', $invoice->id) }}" class="fw-bold text-primary" title="View / Print Invoice" target="_blank">
                                                    {{ $displayBillNo }}
                                                </a>
                                                @if(auth()->user()->can('edit sales ledger') || auth()->user()->isSuperAdmin())
                                                <button type="button" class="btn btn-xs btn-outline-primary p-1 btn-edit-bill" data-id="{{ $invoice->id }}" title="Edit Bill Details">
                                                    <i class="bx bx-edit fs-6"></i>
                                                </button>
                                                @endif
                                            </div>
                                        </td>
                                        <td>{{ $invoice->consignor_name }}</td>
                                        <td class="text-end">₹ {{ number_format($amountWithoutGst, 2) }}</td>
                                        <td class="text-end">₹ {{ number_format($invoice->total_gst, 2) }}</td>
                                        <td class="text-end text-danger">₹ {{ number_format($invoice->tds, 2) }}</td>
                                        <td class="text-end text-danger">₹ {{ number_format($invoice->deduction, 2) }}</td>
                                        <td class="text-end fw-bold">₹ {{ number_format($netPayable, 2) }}</td>
                                        <td class="text-end text-success">₹ {{ number_format($invoice->receiving_amount, 2) }}</td>
                                        <td class="text-end text-success">₹ {{ number_format($invoice->receiving_gst, 2) }}</td>
                                        <td class="text-end fw-bold text-success">₹ {{ number_format($totalReceived, 2) }}</td>
                                        <td class="text-end fw-bold {{ $outstanding > 0 ? 'text-danger' : 'text-success' }}">₹ {{ number_format($outstanding, 2) }}</td>
                                        <td>
                                            @if($invoice->status == 'paid')
                                                <span class="badge bg-label-success">Paid</span>
                                            @elseif($invoice->status == 'pending')
                                                <span class="badge bg-label-warning">Pending</span>
                                            @else
                                                <span class="badge bg-label-danger">{{ ucfirst($invoice->status) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ (auth()->user()->can('edit sales ledger') || auth()->user()->isSuperAdmin()) ? 17 : 16 }}" class="text-center py-4 text-muted">No records found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($invoices->hasPages())
                    <div class="card-footer d-flex justify-content-end py-3">
                        {{ $invoices->withQueryString()->links() }}
                    </div>
                    @endif
                </div>

                <!-- TAB 3: Payment Logs (Receiving History) -->
                <div class="tab-pane fade" id="payment-logs" role="tabpanel">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    @if(auth()->user()->can('edit sales ledger') || auth()->user()->isSuperAdmin())
                                    <th class="text-center" style="width: 90px;">Action</th>
                                    @endif
                                    <th>Date</th>
                                    <th>Bill No</th>
                                    <th>Consigner</th>
                                    <th>Company</th>
                                    <th>Branch</th>
                                    <th class="text-end">Recv. Amount</th>
                                    <th class="text-end">Recv. GST</th>
                                    <th class="text-end">Total Recv.</th>
                                    <th class="text-end">TDS</th>
                                    <th class="text-end">Deduction</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentReceivings as $idx => $rec)
                                @php
                                    $recBillNo = $rec->invoice ? (!empty($rec->invoice->bill_number) ? $rec->invoice->bill_number : ($rec->invoice->invoice_no ?? 'INV-'.$rec->invoice->id)) : 'N/A';
                                @endphp
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    @if(auth()->user()->can('edit sales ledger') || auth()->user()->isSuperAdmin())
                                    <td class="text-center">
                                        <button type="button" class="btn btn-xs btn-outline-primary btn-edit-receiving" data-id="{{ $rec->id }}" title="Edit">
                                            <i class="bx bx-edit"></i>
                                        </button>
                                        <form action="{{ route('admin.reports.sales-ledger.delete-receiving', $rec->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this receiving entry?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-outline-danger" title="Delete">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                    @endif
                                    <td>{{ $rec->date?->format('d-m-Y') }}</td>
                                    <td><strong class="text-primary">{{ $recBillNo }}</strong></td>
                                    <td>{{ $rec->invoice?->consignor_name ?? 'N/A' }}</td>
                                    <td>{{ $rec->company?->name ?? 'N/A' }}</td>
                                    <td>{{ $rec->branch?->name ?? 'N/A' }}</td>
                                    <td class="text-end text-success fw-semibold">₹ {{ number_format($rec->receiving_amount, 2) }}</td>
                                    <td class="text-end text-success">₹ {{ number_format($rec->receiving_gst, 2) }}</td>
                                    <td class="text-end fw-bold text-success">₹ {{ number_format($rec->receiving_amount + $rec->receiving_gst, 2) }}</td>
                                    <td class="text-end text-danger">₹ {{ number_format($rec->tds, 2) }}</td>
                                    <td class="text-end text-danger">₹ {{ number_format($rec->deduction, 2) }}</td>
                                    <td><small class="text-muted">{{ $rec->deduction_reason ?: '-' }}</small></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="13" class="text-center py-4 text-muted">No recent payment logs found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(auth()->user()->can('edit sales ledger') || auth()->user()->isSuperAdmin())
<!-- Edit Bill Modal -->
<div class="modal fade" id="editBillModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form id="editBillForm" action="" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bx bx-edit me-1 text-primary"></i> Edit Bill Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Bill Number <span class="text-danger">*</span></label>
                            <input type="text" name="bill_number" id="eb_bill_number" class="form-control fw-bold text-primary" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bill Date <span class="text-danger">*</span></label>
                            <input type="date" name="invoice_date" id="eb_invoice_date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Company</label>
                            <input type="text" id="eb_company" class="form-control bg-light" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Branch</label>
                            <input type="text" id="eb_branch" class="form-control bg-light" readonly>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Bill To / Consigner Name</label>
                            <input type="text" name="consignor_name" id="eb_consignor_name" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Billing Address</label>
                            <textarea name="billing_address" id="eb_billing_address" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Bill Status <span class="text-danger">*</span></label>
                            <select name="status" id="eb_status" class="form-select" required>
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Net Payable (₹)</label>
                            <input type="text" id="eb_net_payable" class="form-control bg-light fw-bold" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Outstanding (₹)</label>
                            <input type="text" id="eb_outstanding" class="form-control bg-light fw-bold" readonly>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <div>
                        <a href="#" id="eb_view_invoice_link" class="btn btn-outline-info btn-sm me-1" target="_blank">
                            <i class="bx bx-printer me-1"></i> View / Print Bill
                        </a>
                        <a href="#" id="eb_generate_link" class="btn btn-outline-secondary btn-sm" target="_blank">
                            <i class="bx bx-list-check me-1"></i> Manage LRs
                        </a>
                    </div>
                    <div>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Save Changes</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Receive Amount Modal -->
<div class="modal fade" id="receiveAmountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form action="{{ route('admin.reports.sales-ledger.receive') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bx bx-rupee me-1 text-success"></i> Receive Amount</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bill Number <span class="text-danger">*</span></label>
                            <select name="invoice_id" id="invoice_id" class="form-select" required>
                                <option value="">Select Bill Number</option>
                                @foreach($allBills as $bill)
                                    @php
                                        $displayBillNo = !empty($bill->bill_number) ? $bill->bill_number : ($bill->invoice_no ?? ('INV-' . $bill->id));
                                    @endphp
                                    <option value="{{ $bill->id }}">{{ $displayBillNo }} - {{ $bill->consignor_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Auto Filled Fields -->
                        <div class="col-md-12">
                            <label class="form-label">Bill To</label>
                            <input type="text" id="auto_bill_to" class="form-control bg-light" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Company</label>
                            <input type="text" id="auto_company" class="form-control bg-light" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Branch</label>
                            <input type="text" id="auto_branch" class="form-control bg-light" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bill Amount (₹)</label>
                            <input type="text" id="auto_bill_amount" class="form-control bg-light fw-bold" readonly value="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Outstanding (₹)</label>
                            <input type="text" id="auto_outstanding" class="form-control bg-light fw-bold" readonly value="0.00">
                        </div>
                        
                        <!-- Amount Inputs -->
                        <div class="col-md-6">
                            <label class="form-label">Receiving Amount (₹)</label>
                            <input type="number" step="0.01" name="receiving_amount" class="form-control" value="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Receiving GST (₹)</label>
                            <input type="number" step="0.01" name="receiving_gst" class="form-control" value="0.00">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">TDS (%)</label>
                            <input type="number" step="0.01" id="tds_percentage" class="form-control" value="1.00" placeholder="1.00">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">TDS Amount (₹)</label>
                            <input type="number" step="0.01" name="tds" id="auto_tds" class="form-control" value="0.00" placeholder="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Deduction (₹)</label>
                            <input type="number" step="0.01" name="deduction" id="modal_deduction" class="form-control" value="0.00">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Deduction Reason</label>
                            <input type="text" name="deduction_reason" class="form-control" placeholder="Enter reason if deduction applied">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Save Entry</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Receiving Modal -->
<div class="modal fade" id="editReceivingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form id="editReceivingForm" action="" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bx bx-edit me-1 text-primary"></i> Edit Receive Amount</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" id="edit_date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bill Number</label>
                            <input type="text" id="edit_bill_number" class="form-control bg-light" readonly>
                        </div>
                        
                        <!-- Auto Filled Fields -->
                        <div class="col-md-12">
                            <label class="form-label">Bill To</label>
                            <input type="text" id="edit_bill_to" class="form-control bg-light" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Company</label>
                            <input type="text" id="edit_company" class="form-control bg-light" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Branch</label>
                            <input type="text" id="edit_branch" class="form-control bg-light" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Net Payable Amount (₹)</label>
                            <input type="text" id="edit_net_payable" class="form-control bg-light fw-bold" readonly value="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Outstanding (₹)</label>
                            <input type="text" id="edit_outstanding" class="form-control bg-light fw-bold" readonly value="0.00">
                        </div>
                        
                        <!-- Amount Inputs -->
                        <div class="col-md-6">
                            <label class="form-label">Receiving Amount (₹)</label>
                            <input type="number" step="0.01" name="receiving_amount" id="edit_receiving_amount" class="form-control" value="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Receiving GST (₹)</label>
                            <input type="number" step="0.01" name="receiving_gst" id="edit_receiving_gst" class="form-control" value="0.00">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">TDS (%)</label>
                            <input type="number" step="0.01" id="edit_tds_percentage" class="form-control" value="1.00" placeholder="1.00">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">TDS Amount (₹)</label>
                            <input type="number" step="0.01" name="tds" id="edit_auto_tds" class="form-control" value="0.00" placeholder="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Deduction (₹)</label>
                            <input type="number" step="0.01" name="deduction" id="edit_modal_deduction" class="form-control" value="0.00">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Deduction Reason</label>
                            <input type="text" name="deduction_reason" id="edit_deduction_reason" class="form-control" placeholder="Enter reason if deduction applied">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Update Entry</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@section('script')
<script>
    $(document).ready(function() {
        // Tab preservation from URL hash
        var hash = window.location.hash;
        if (hash) {
            var tabBtn = $('button[data-bs-target="' + hash + '"]');
            if (tabBtn.length) {
                tabBtn.tab('show');
            }
        }

        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            var target = $(e.target).attr('data-bs-target');
            if (target) {
                history.replaceState(null, null, target);
            }
        });

        // Edit Bill Handler
        $(document).on('click', '.btn-edit-bill', function() {
            var invoiceId = $(this).data('id');
            if (!invoiceId) return;

            $.ajax({
                url: "{{ url('admin/reports/sales-ledger/bill-details') }}/" + invoiceId,
                type: "GET",
                success: function(response) {
                    if (response.success) {
                        var d = response.data;
                        $('#editBillForm').attr('action', "{{ url('admin/reports/sales-ledger/bill') }}/" + invoiceId);
                        $('#eb_bill_number').val(d.bill_number);
                        $('#eb_invoice_date').val(d.invoice_date);
                        $('#eb_company').val(d.company_name);
                        $('#eb_branch').val(d.branch_name);
                        $('#eb_consignor_name').val(d.consignor_name);
                        $('#eb_billing_address').val(d.billing_address);
                        $('#eb_status').val(d.status);
                        $('#eb_net_payable').val('₹ ' + parseFloat(d.net_payable_amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                        $('#eb_outstanding').val('₹ ' + parseFloat(d.outstanding_amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                        $('#eb_view_invoice_link').attr('href', d.show_url);
                        $('#eb_generate_link').attr('href', d.generate_url);

                        $('#editBillModal').modal('show');
                    } else {
                        alert(response.message || 'Failed to fetch bill details.');
                    }
                },
                error: function() {
                    alert('Error fetching bill details.');
                }
            });
        });

        @if(auth()->user()->can('edit sales ledger') || auth()->user()->isSuperAdmin())
        var currentGrossBaseAmount = 0;
        var editGrossBaseAmount = 0;

        function calculateTdsFromPercentage() {
            var deduction = parseFloat($('#modal_deduction').val()) || 0;
            var baseAmount = currentGrossBaseAmount - deduction;
            if (baseAmount < 0) baseAmount = 0;

            var percentage = parseFloat($('#tds_percentage').val()) || 0;
            var tdsAmount = (baseAmount * percentage) / 100;
            $('#auto_tds').val(tdsAmount.toFixed(2));
        }

        function calculatePercentageFromTds() {
            var deduction = parseFloat($('#modal_deduction').val()) || 0;
            var baseAmount = currentGrossBaseAmount - deduction;
            var tdsAmount = parseFloat($('#auto_tds').val()) || 0;

            if (baseAmount > 0) {
                var percentage = (tdsAmount / baseAmount) * 100;
                $('#tds_percentage').val(percentage.toFixed(2));
            }
        }

        function fetchInvoiceDetails(invoiceId) {
            if(invoiceId) {
                $.ajax({
                    url: "{{ url('admin/reports/sales-ledger/invoice-details') }}/" + invoiceId,
                    type: "GET",
                    success: function(response) {
                        if(response.success) {
                            $('#auto_bill_to').val(response.data.bill_to);
                            $('#auto_company').val(response.data.company_name);
                            $('#auto_branch').val(response.data.branch_name);
                            $('#auto_bill_amount').val('₹ ' + parseFloat(response.data.net_payable_amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                            $('#auto_outstanding').val('₹ ' + parseFloat(response.data.outstanding_amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

                            currentGrossBaseAmount = parseFloat(response.data.gross_base_amount) || 0;
                            $('#tds_percentage').val('1.00');
                            calculateTdsFromPercentage();
                        } else {
                            alert('Failed to fetch invoice details.');
                        }
                    },
                    error: function() {
                        alert('Error fetching invoice details.');
                    }
                });
            } else {
                currentGrossBaseAmount = 0;
                $('#auto_bill_to').val('');
                $('#auto_company').val('');
                $('#auto_branch').val('');
                $('#auto_bill_amount').val('₹ 0.00');
                $('#auto_outstanding').val('₹ 0.00');
                $('#tds_percentage').val('1.00');
                $('#auto_tds').val('0.00');
            }
        }

        $(document).on('change', '#invoice_id', function() {
            fetchInvoiceDetails($(this).val());
        });

        $(document).on('input change', '#tds_percentage', function() {
            calculateTdsFromPercentage();
        });

        $(document).on('input change', '#auto_tds', function() {
            calculatePercentageFromTds();
        });

        $(document).on('input change', '#modal_deduction', function() {
            calculateTdsFromPercentage();
        });

        $('#receiveAmountModal').on('hidden.bs.modal', function () {
            currentGrossBaseAmount = 0;
            $('#invoice_id').val('');
            $('#auto_bill_to').val('');
            $('#auto_company').val('');
            $('#auto_branch').val('');
            $('#auto_bill_amount').val('₹ 0.00');
            $('#auto_outstanding').val('₹ 0.00');
            $('#tds_percentage').val('1.00');
            $('#auto_tds').val('0.00');
            $('#modal_deduction').val('0.00');
        });

        // Edit Receiving Handler
        function calculateEditTdsFromPercentage() {
            var deduction = parseFloat($('#edit_modal_deduction').val()) || 0;
            var baseAmount = editGrossBaseAmount - deduction;
            if (baseAmount < 0) baseAmount = 0;

            var percentage = parseFloat($('#edit_tds_percentage').val()) || 0;
            var tdsAmount = (baseAmount * percentage) / 100;
            $('#edit_auto_tds').val(tdsAmount.toFixed(2));
        }

        function calculateEditPercentageFromTds() {
            var deduction = parseFloat($('#edit_modal_deduction').val()) || 0;
            var baseAmount = editGrossBaseAmount - deduction;
            var tdsAmount = parseFloat($('#edit_auto_tds').val()) || 0;

            if (baseAmount > 0) {
                var percentage = (tdsAmount / baseAmount) * 100;
                $('#edit_tds_percentage').val(percentage.toFixed(2));
            }
        }

        $(document).on('click', '.btn-edit-receiving', function() {
            var receivingId = $(this).data('id');
            if (!receivingId) return;

            $.ajax({
                url: "{{ url('admin/reports/sales-ledger/receiving') }}/" + receivingId,
                type: "GET",
                success: function(response) {
                    if (response.success) {
                        var d = response.data;
                        $('#editReceivingForm').attr('action', "{{ url('admin/reports/sales-ledger/receiving') }}/" + receivingId);
                        $('#edit_date').val(d.date);
                        $('#edit_bill_number').val(d.bill_number);
                        $('#edit_bill_to').val(d.bill_to);
                        $('#edit_company').val(d.company_name);
                        $('#edit_branch').val(d.branch_name);
                        $('#edit_net_payable').val('₹ ' + parseFloat(d.net_payable_amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                        $('#edit_outstanding').val('₹ ' + parseFloat(d.outstanding_amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                        
                        $('#edit_receiving_amount').val(parseFloat(d.receiving_amount).toFixed(2));
                        $('#edit_receiving_gst').val(parseFloat(d.receiving_gst).toFixed(2));
                        $('#edit_auto_tds').val(parseFloat(d.tds).toFixed(2));
                        $('#edit_modal_deduction').val(parseFloat(d.deduction).toFixed(2));
                        $('#edit_deduction_reason').val(d.deduction_reason);

                        editGrossBaseAmount = parseFloat(d.gross_base_amount) || 0;
                        calculateEditPercentageFromTds();

                        $('#editReceivingModal').modal('show');
                    } else {
                        alert(response.message || 'Failed to fetch receiving details.');
                    }
                },
                error: function() {
                    alert('Error fetching receiving details.');
                }
            });
        });

        $(document).on('click', '.btn-add-receiving', function() {
            var invoiceId = $(this).data('invoice-id');
            $('#receiveAmountModal').modal('show');
            if (invoiceId) {
                $('#invoice_id').val(invoiceId).trigger('change');
            }
        });

        $(document).on('input change', '#edit_tds_percentage', function() {
            calculateEditTdsFromPercentage();
        });

        $(document).on('input change', '#edit_auto_tds', function() {
            calculateEditPercentageFromTds();
        });

        $(document).on('input change', '#edit_modal_deduction', function() {
            calculateEditTdsFromPercentage();
        });
        @endif
    });
</script>
@endsection
