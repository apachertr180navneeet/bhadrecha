@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center bg-white py-3 gap-2">
                    <div>
                        <h5 class="card-title mb-0 fw-bold text-primary"><i class="bx bx-book-content me-2"></i>Store Ledger Report</h5>
                        <small class="text-muted">Showing combined Sales and Expenses ledger data</small>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <a href="{{ route('admin.reports.export.store-ledger.pdf', array_merge(['date_from' => $dateFrom, 'date_to' => $dateTo], request()->all())) }}" class="btn btn-sm btn-outline-danger me-1">
                            <i class="bx bxs-file-pdf me-1"></i>Export PDF
                        </a>
                        <a href="{{ route('admin.reports.export.store-ledger.excel', array_merge(['date_from' => $dateFrom, 'date_to' => $dateTo], request()->all())) }}" class="btn btn-sm btn-outline-success">
                            <i class="bx bx-spreadsheet me-1"></i>Export Excel
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.reports.store-ledger') }}" class="row g-3 align-items-end">
                        <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                            <label class="form-label text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600;">Date From</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from', $dateFrom) }}">
                        </div>
                        <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                            <label class="form-label text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600;">Date To</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to', $dateTo) }}">
                        </div>
                        <div class="col-12 col-sm-6 col-md-4 col-xl-3">
                            <label class="form-label text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600;">Store Selection</label>
                            <select name="store_id" id="ledgerReportStore" class="form-select" {{ (auth()->user()->store_id && !$canViewAllStores) ? 'disabled' : '' }}>
                                <option value="">All Stores (Default)</option>
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}" {{ (string)($selectedStoreId ?? request('store_id')) === (string)$store->id ? 'selected' : '' }}>
                                        {{ $store->store_name ?? $store->name }}
                                    </option>
                                @endforeach
                            </select>
                            @if(auth()->user()->store_id && !$canViewAllStores)
                                <input type="hidden" name="store_id" value="{{ auth()->user()->store_id }}">
                            @endif
                        </div>
                        <div class="col-12 col-sm-6 col-md-4 col-xl-3">
                            <label class="form-label text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600;">Branch Selection</label>
                            <select name="branch_id" id="ledgerReportBranch" class="form-select" {{ (auth()->user()->branch_id && !$canViewAllBranches) ? 'disabled' : '' }}>
                                <option value="">All Branches (Default)</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" data-store-id="{{ $branch->store_id }}" {{ (string)($selectedBranchId ?? request('branch_id')) === (string)$branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            @if(auth()->user()->branch_id && !$canViewAllBranches)
                                <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
                            @endif
                        </div>
                        <div class="col-12 col-sm-6 col-md-4 col-xl-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1"><i class="bx bx-filter-alt me-1"></i>Filter</button>
                            <a href="{{ route('admin.reports.store-ledger') }}" class="btn btn-outline-secondary flex-grow-1"><i class="bx bx-reset me-1"></i>Reset</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="row mb-4 g-3">
        <div class="col-12 col-sm-6 col-md-4 col-xl">
            <div class="card h-100 shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <span class="d-block text-muted mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px; text-transform: uppercase;">Total Sales (Credit)</span>
                            <h4 class="mb-0 text-success fw-bold">₹{{ number_format($totalSales, 2) }}</h4>
                        </div>
                        <div class="stat-card-icon bg-label-success rounded-circle" style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;">
                            <i class="bx bx-trending-up fs-4 text-success"></i>
                        </div>
                    </div>
                    <div class="pt-2 border-top d-flex justify-content-between text-muted" style="font-size: 0.72rem;">
                        <span class="text-success"><i class="bx bx-money me-1"></i>Cash: ₹{{ number_format($totalCashSales, 2) }} ({{ $totalCashCount }})</span>
                        <span class="text-primary"><i class="bx bx-qr-scan me-1"></i>UPI: ₹{{ number_format($totalUpiSales, 2) }} ({{ $totalUpiCount }})</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-xl">
            <div class="card h-100 shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div>
                            <span class="d-block text-muted mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px; text-transform: uppercase;">Cash Sales</span>
                            <h4 class="mb-0 text-success fw-bold">₹{{ number_format($totalCashSales, 2) }}</h4>
                        </div>
                        <div class="stat-card-icon bg-label-success rounded-circle" style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;">
                            <i class="bx bx-money fs-4 text-success"></i>
                        </div>
                    </div>
                    <div class="pt-2 border-top text-muted" style="font-size: 0.72rem;">
                        <span><i class="bx bx-receipt me-1 text-success"></i>Total Cash Txns: <strong class="text-success">{{ $totalCashCount }}</strong></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-xl">
            <div class="card h-100 shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div>
                            <span class="d-block text-muted mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px; text-transform: uppercase;">UPI Sales</span>
                            <h4 class="mb-0 text-primary fw-bold">₹{{ number_format($totalUpiSales, 2) }}</h4>
                        </div>
                        <div class="stat-card-icon bg-label-primary rounded-circle" style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;">
                            <i class="bx bx-qr-scan fs-4 text-primary"></i>
                        </div>
                    </div>
                    <div class="pt-2 border-top text-muted" style="font-size: 0.72rem;">
                        <span><i class="bx bx-receipt me-1 text-primary"></i>Total UPI Txns: <strong class="text-primary">{{ $totalUpiCount }}</strong></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-xl">
            <div class="card h-100 shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="d-block text-muted mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px; text-transform: uppercase;">Total Expenses (Debit)</span>
                            <h4 class="mb-0 text-danger fw-bold">₹{{ number_format($totalExpenses, 2) }}</h4>
                        </div>
                        <div class="stat-card-icon bg-label-danger rounded-circle" style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;">
                            <i class="bx bx-trending-down fs-4 text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-xl">
            <div class="card h-100 shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="d-block text-muted mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px; text-transform: uppercase;">Net Ledger Balance</span>
                            <h4 class="mb-0 {{ $netBalance >= 0 ? 'text-primary' : 'text-danger' }} fw-bold">
                                ₹{{ number_format($netBalance, 2) }}
                            </h4>
                        </div>
                        <div class="stat-card-icon bg-label-primary rounded-circle" style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;">
                            <i class="bx bx-wallet fs-4 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Store Breakdown Table -->
    @if(!$selectedStoreId && count($storeWiseBreakdown) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h6 class="card-title mb-0 fw-bold"><i class="bx bx-store me-2 text-primary"></i>Store-wise Ledger Breakdown</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Store Name</th>
                                <th class="text-end">Total Sales (₹)</th>
                                <th class="text-end">Cash Sales (Count)</th>
                                <th class="text-end">UPI Sales (Count)</th>
                                <th class="text-end">Expenses (Debit ₹)</th>
                                <th class="text-end">Net Cash Flow (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($storeWiseBreakdown as $stSummary)
                            @php $stSummary = (object) $stSummary; @endphp
                            <tr>
                                <td>
                                    <strong>{{ $stSummary->store_name }}</strong>
                                </td>
                                <td class="text-end text-success font-monospace fw-semibold">₹{{ number_format($stSummary->total_sales, 2) }}</td>
                                <td class="text-end text-success font-monospace">
                                    ₹{{ number_format($stSummary->cash_sales ?? 0, 2) }}
                                    <span class="badge bg-label-success ms-1">{{ $stSummary->cash_count ?? 0 }}</span>
                                </td>
                                <td class="text-end text-primary font-monospace">
                                    ₹{{ number_format($stSummary->upi_sales ?? 0, 2) }}
                                    <span class="badge bg-label-primary ms-1">{{ $stSummary->upi_count ?? 0 }}</span>
                                </td>
                                <td class="text-end text-danger font-monospace fw-semibold">₹{{ number_format($stSummary->total_expenses, 2) }}</td>
                                <td class="text-end font-monospace fw-bold {{ $stSummary->net_balance >= 0 ? 'text-primary' : 'text-danger' }}">
                                    ₹{{ number_format($stSummary->net_balance, 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Detailed Combined Ledger Table -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 fw-bold"><i class="bx bx-list-ul me-2 text-primary"></i>Ledger Transactions Timeline</h6>
                    <span class="badge bg-label-secondary">Filter Period: {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Date & Time</th>
                                <th>Store</th>
                                <th>Branch</th>
                                <th>Type</th>
                                <th>Ref No.</th>
                                <th>Details / Description</th>
                                <th>Payment Mode</th>
                                <th class="text-end">Credit (Sale ₹)</th>
                                <th class="text-end">Debit (Expense ₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($combinedLedger as $index => $entry)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    {{ $entry->date ? \Carbon\Carbon::parse($entry->date)->format('d-m-Y h:i A') : 'N/A' }}
                                </td>
                                <td><span class="badge bg-label-info">{{ $entry->store_name }}</span></td>
                                <td><span class="badge bg-label-secondary">{{ $entry->branch_name ?? 'N/A' }}</span></td>
                                <td>
                                    @if($entry->type === 'Sale')
                                        <span class="badge bg-label-success"><i class="bx bx-arrow-fall-circle me-1"></i>Sale</span>
                                    @else
                                        <span class="badge bg-label-danger"><i class="bx bx-arrow-rise-circle me-1"></i>Expense</span>
                                    @endif
                                </td>
                                <td><code class="text-dark fw-bold">{{ $entry->ref_no }}</code></td>
                                <td>
                                    <div>{{ $entry->details }}</div>
                                </td>
                                <td>
                                    @if(strtolower($entry->payment_type) === 'cash')
                                        <span class="badge bg-label-success"><i class="bx bx-money me-1"></i>Cash</span>
                                    @elseif(strtolower($entry->payment_type) === 'upi')
                                        <span class="badge bg-label-primary"><i class="bx bx-qr-scan me-1"></i>UPI</span>
                                        @if(!empty($entry->upi_reference))
                                            <small class="text-muted d-block mt-1 font-monospace" style="font-size: 0.72rem;">Ref: {{ $entry->upi_reference }}</small>
                                        @endif
                                    @else
                                        <span class="badge bg-label-secondary">{{ $entry->payment_type }}</span>
                                    @endif
                                </td>
                                <td class="text-end font-monospace text-success fw-semibold">
                                    {{ $entry->credit > 0 ? '₹' . number_format($entry->credit, 2) : '-' }}
                                </td>
                                <td class="text-end font-monospace text-danger fw-semibold">
                                    {{ $entry->debit > 0 ? '₹' . number_format($entry->debit, 2) : '-' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">
                                    <i class="bx bx-info-circle me-1"></i>No ledger transactions found for the selected date range.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if(count($combinedLedger) > 0)
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="8" class="text-end py-2 align-middle">
                                    <span class="badge bg-label-success px-3 py-2 me-2 font-monospace" style="font-size: 0.8rem;">
                                        <i class="bx bx-money me-1"></i>CASH SALES: ₹{{ number_format($totalCashSales, 2) }} ({{ $totalCashCount }} txns)
                                    </span>
                                    <span class="badge bg-label-primary px-3 py-2 me-2 font-monospace" style="font-size: 0.8rem;">
                                        <i class="bx bx-qr-scan me-1"></i>UPI SALES: ₹{{ number_format($totalUpiSales, 2) }} ({{ $totalUpiCount }} txns)
                                    </span>
                                    <span class="text-uppercase text-muted align-middle ms-2">TOTALS:</span>
                                </td>
                                <td class="text-end text-success font-monospace fs-6 align-middle">₹{{ number_format($totalSales, 2) }}</td>
                                <td class="text-end text-danger font-monospace fs-6 align-middle">₹{{ number_format($totalExpenses, 2) }}</td>
                            </tr>
                            <tr class="table-secondary">
                                <td colspan="8" class="text-end align-middle">NET LEDGER BALANCE:</td>
                                <td colspan="2" class="text-end font-monospace fs-6 align-middle {{ $netBalance >= 0 ? 'text-primary' : 'text-danger' }}">
                                    ₹{{ number_format($netBalance, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        if (window.setupDependentBranchSelect) {
            window.setupDependentBranchSelect('#ledgerReportStore', '#ledgerReportBranch');
        }
    });
</script>
@endsection
