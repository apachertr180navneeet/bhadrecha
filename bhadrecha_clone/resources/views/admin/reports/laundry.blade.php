@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <!-- Filter Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-header border-bottom bg-transparent py-3">
                    <h5 class="card-title mb-0 fw-bold" style="color: #4b5563;">
                        <i class="bx bx-filter-alt me-2 text-primary"></i>Filter Laundry Report
                    </h5>
                </div>
                <div class="card-body mt-3">
                    <form method="GET" action="{{ route('admin.reports.laundry') }}" class="row g-3 align-items-end">
                        <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                            <label class="form-label text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600;">Date From</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                            <label class="form-label text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600;">Date To</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-12 col-sm-6 col-md-4 col-xl-3">
                            <label class="form-label text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600;">Store</label>
                            <select name="store_id" id="laundryReportStore" class="form-select" {{ (auth()->user()->store_id && !($canViewAllStores ?? (auth()->user()->hasRole('Admin') || auth()->user()->can('view stores')))) ? 'disabled' : '' }}>
                                <option value="">All Stores</option>
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}" {{ (string)($selectedStoreId ?? request('store_id')) === (string)$store->id ? 'selected' : '' }}>
                                        {{ $store->store_name ?? $store->name }}
                                    </option>
                                @endforeach
                            </select>
                            @if(auth()->user()->store_id && !($canViewAllStores ?? (auth()->user()->hasRole('Admin') || auth()->user()->can('view stores'))))
                                <input type="hidden" name="store_id" value="{{ auth()->user()->store_id }}">
                            @endif
                        </div>
                        <div class="col-12 col-sm-6 col-md-4 col-xl-3">
                            <label class="form-label text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600;">Branch</label>
                            <select name="branch_id" id="laundryReportBranch" class="form-select" {{ (auth()->user()->branch_id && !($canViewAllBranches ?? (auth()->user()->hasRole('Admin') || auth()->user()->can('view branches')))) ? 'disabled' : '' }}>
                                <option value="">All Branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" data-store-id="{{ $branch->store_id }}" {{ (string)($selectedBranchId ?? request('branch_id')) === (string)$branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            @if(auth()->user()->branch_id && !($canViewAllBranches ?? (auth()->user()->hasRole('Admin') || auth()->user()->can('view branches'))))
                                <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
                            @endif
                        </div>
                        <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                            <label class="form-label text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600;">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="partially_received" {{ request('status') == 'partially_received' ? 'selected' : '' }}>Partially Received</option>
                                <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4 col-xl-2 d-flex gap-2 align-items-end">
                            <button type="submit" class="btn btn-primary w-100 px-2 text-nowrap"><i class="bx bx-filter-alt me-1"></i>Filter</button>
                            <a href="{{ route('admin.reports.laundry') }}" class="btn btn-outline-secondary w-100 px-2 text-nowrap"><i class="bx bx-reset me-1"></i>Reset</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="row mb-4 g-3">
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card h-100 shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="d-block text-muted mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px; text-transform: uppercase;">Total Orders</span>
                            <h2 class="mb-0 text-primary fw-bold">{{ number_format($totalOrdersCount) }}</h2>
                        </div>
                        <div class="stat-card-icon bg-label-primary rounded-circle" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                            <i class="bx bx-droplet fs-3 text-primary"></i>
                        </div>
                    </div>
                    <div class="mt-3 pt-2 border-top d-flex gap-2">
                        <span class="badge bg-label-secondary" title="Pending"><i class="bx bx-time me-1"></i>{{ $pendingOrdersCount }} Pending</span>
                        <span class="badge bg-label-warning" title="Partially Received"><i class="bx bx-adjust me-1"></i>{{ $partiallyReceivedCount }} Partial</span>
                        <span class="badge bg-label-success" title="Received"><i class="bx bx-check-circle me-1"></i>{{ $receivedCount }} Done</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
            <div class="card h-100 shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="d-block text-muted mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px; text-transform: uppercase;">Total Laundry Amount</span>
                            <h2 class="mb-0 text-success fw-bold">₹{{ number_format($totalAmount, 2) }}</h2>
                        </div>
                        <div class="stat-card-icon bg-label-success rounded-circle" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                            <i class="bx bx-dollar-circle fs-3 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
            <div class="card h-100 shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="d-block text-muted mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px; text-transform: uppercase;">Total Sent Items</span>
                            <h2 class="mb-0 text-info fw-bold">{{ number_format($totalSentQty) }}</h2>
                        </div>
                        <div class="stat-card-icon bg-label-info rounded-circle" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                            <i class="bx bx-export fs-3 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
            <div class="card h-100 shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="d-block text-muted mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px; text-transform: uppercase;">Total Received Items</span>
                            <h2 class="mb-0 text-warning fw-bold">{{ number_format($totalReceivedQty) }}</h2>
                        </div>
                        <div class="stat-card-icon bg-label-warning rounded-circle" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                            <i class="bx bx-import fs-3 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Item-wise Summary -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-header border-bottom bg-transparent py-3">
                    <h5 class="card-title mb-0 fw-bold" style="color: #4b5563;">
                        <i class="bx bx-package me-2 text-primary"></i>Item-wise Laundry Summary
                    </h5>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb; padding-left: 1.5rem;">Item</th>
                                <th class="text-uppercase text-muted text-center" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb;">Sent Qty</th>
                                <th class="text-uppercase text-muted text-center" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb;">Received Qty</th>
                                <th class="text-uppercase text-muted text-center" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb;">Pending Qty</th>
                                <th class="text-uppercase text-muted text-end" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb; padding-right: 1.5rem;">Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($itemBreakdown as $row)
                                @php
                                    $pendingQty = max(0, $row->total_sent - $row->total_received);
                                @endphp
                                <tr>
                                    <td style="padding-left: 1.5rem;">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-3 bg-label-primary text-primary rounded-circle d-flex align-items-center justify-content-center">
                                                <i class="bx bx-closet fs-6"></i>
                                            </div>
                                            <span class="fw-semibold text-body">{{ $row->item->name ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center fw-semibold text-info">{{ $row->total_sent }}</td>
                                    <td class="text-center fw-semibold text-success">{{ $row->total_received }}</td>
                                    <td class="text-center fw-semibold {{ $pendingQty > 0 ? 'text-danger' : 'text-muted' }}">{{ $pendingQty }}</td>
                                    <td class="text-end fw-bold text-dark" style="padding-right: 1.5rem;">
                                        ₹{{ number_format($row->total_amount ?? 0, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">
                                        <i class="bx bx-package fs-1 text-light mb-3"></i>
                                        <p class="mb-0">No item data available for selected filters.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Detail Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-header border-bottom bg-transparent py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h5 class="card-title mb-0 fw-bold" style="color: #4b5563;">
                        <i class="bx bx-list-ul me-2 text-primary"></i>Laundry Orders List
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-label-primary fs-6 px-3 py-2">
                            Total Listed: ₹{{ number_format($laundryOrders->sum('total_amount'), 2) }}
                        </span>
                        @can('pay laundry orders')
                        <button type="button" class="btn btn-success btn-sm d-none" id="btnReportBulkPay">
                            <i class="bx bx-check-double me-1"></i> Pay Selected (<span id="reportSelectedCount">0</span>) - ₹<span id="reportSelectedAmount">0.00</span>
                        </button>
                        @endcan
                    </div>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover align-middle mb-0" id="reportLaundryTable">
                        <thead class="bg-light">
                            <tr>
                                @can('pay laundry orders')
                                <th style="width: 40px; padding-left: 1.5rem;">
                                    <input type="checkbox" class="form-check-input" id="reportSelectAll">
                                </th>
                                @endcan
                                <th class="text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb; {{ !auth()->user()->can('pay laundry orders') ? 'padding-left: 1.5rem;' : '' }}">#</th>
                                <th class="text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb;">Date</th>
                                <th class="text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb;">Store</th>
                                <th class="text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb;">Branch</th>
                                <th class="text-uppercase text-muted text-center" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb;">Items (Sent / Recv)</th>
                                <th class="text-uppercase text-muted text-end" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb;">Amount</th>
                                <th class="text-uppercase text-muted text-center" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb;">Status</th>
                                <th class="text-uppercase text-muted text-center" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb;">Payment</th>
                                <th class="text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb; padding-right: 1.5rem;">Remark</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($laundryOrders as $order)
                                @php
                                    $sentCount = $order->items->sum('qty');
                                    $recvCount = $order->items->sum('received_qty');
                                @endphp
                                <tr>
                                    @can('pay laundry orders')
                                    <td style="padding-left: 1.5rem;">
                                        @if(!$order->is_paid)
                                        <input type="checkbox" class="form-check-input report-order-checkbox" value="{{ $order->id }}" data-amount="{{ $order->total_amount }}" data-date="{{ $order->order_date ? $order->order_date->format('d M Y') : '' }}">
                                        @else
                                        <span class="text-success"><i class="bx bx-check-circle fs-5" title="Paid on {{ $order->paid_at ? $order->paid_at->format('d M, Y') : '' }}"></i></span>
                                        @endif
                                    </td>
                                    @endcan
                                    <td style="{{ !auth()->user()->can('pay laundry orders') ? 'padding-left: 1.5rem;' : '' }}">
                                        <span class="text-muted fw-semibold">#{{ $order->id }}</span>
                                    </td>
                                    <td>
                                        <span class="text-body fw-medium"><i class="bx bx-calendar-event me-1 text-primary"></i>{{ $order->order_date ? $order->order_date->format('d M, Y') : '-' }}</span>
                                    </td>
                                    <td>
                                        <span class="text-muted"><i class="bx bx-store-alt me-1"></i>{{ $order->store->store_name ?? $order->store->name ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <span class="text-muted"><i class="bx bx-git-branch me-1"></i>{{ $order->branch->name ?? '-' }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-label-info">{{ $sentCount }} Sent</span>
                                        <span class="badge bg-label-success ms-1">{{ $recvCount }} Recv</span>
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        ₹{{ number_format($order->total_amount, 2) }}
                                    </td>
                                    <td class="text-center">
                                        @if($order->status == 'received')
                                            <span class="badge bg-label-success px-3 py-2 rounded-pill"><i class="bx bx-check-circle me-1"></i>Received</span>
                                        @elseif($order->status == 'partially_received')
                                            <span class="badge bg-label-warning px-3 py-2 rounded-pill"><i class="bx bx-adjust me-1"></i>Partially Received</span>
                                        @else
                                            <span class="badge bg-label-secondary px-3 py-2 rounded-pill"><i class="bx bx-time me-1"></i>Pending</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($order->is_paid)
                                            <span class="badge bg-label-success px-3 py-2 rounded-pill" title="Paid on {{ $order->paid_at ? $order->paid_at->format('d M, Y') : '' }}"><i class="bx bx-check-circle me-1"></i>Paid</span>
                                        @else
                                            <span class="badge bg-label-danger px-3 py-2 rounded-pill"><i class="bx bx-x-circle me-1"></i>Unpaid</span>
                                        @endif
                                    </td>
                                    <td style="padding-right: 1.5rem;">
                                        <span class="text-muted small text-truncate d-inline-block" style="max-width: 180px;" title="{{ $order->remark }}">{{ $order->remark ?? '-' }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ auth()->user()->can('pay laundry orders') ? 10 : 9 }}" class="text-center text-muted py-5">
                                        <i class="bx bx-list-ul fs-1 text-light mb-3"></i>
                                        <p class="mb-0">No laundry orders found for selected criteria.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="table-light fw-bold">
                                <td colspan="{{ auth()->user()->can('pay laundry orders') ? 6 : 5 }}" class="text-end" style="padding-left: 1.5rem;">Total Listed Amount:</td>
                                <td class="text-end text-success fs-6 fw-bold">₹{{ number_format($laundryOrders->sum('total_amount'), 2) }}</td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @if($laundryOrders->hasPages())
                <div class="card-footer border-top bg-transparent pt-3 pb-2">
                    {{ $laundryOrders->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Bulk Pay Modal for Laundry Report -->
<div class="modal fade" id="reportBulkPayModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bx bx-dollar-circle text-success me-2"></i>Mark Laundry Orders as Paid</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="reportBulkPayForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-primary d-flex align-items-center mb-3">
                        <i class="bx bx-info-circle fs-4 me-2"></i>
                        <div>
                            <strong><span id="reportModalCount">0</span> order(s) selected</strong><br>
                            Total Payable Amount: <strong class="fs-5 text-success">₹<span id="reportModalAmount">0.00</span></strong>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" id="reportPaymentDate" class="form-control" value="{{ date('Y-m-d') }}" required>
                        <small class="text-muted">The expense record will be logged on this date under Category ID 9.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Remarks <span class="text-muted">(Editable)</span></label>
                        <textarea name="remarks" id="reportPaymentRemarks" class="form-control" rows="3" placeholder="Laundry payment from start date to end date..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="btnReportConfirmPay">
                        <span class="spinner-border spinner-border-sm d-none me-1" id="reportPaySpinner" role="status"></span>
                        Confirm Payment & Add Expense
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        if (window.setupDependentBranchSelect) {
            window.setupDependentBranchSelect('#laundryReportStore', '#laundryReportBranch');
        }

        // Selection & Pay Handlers
        function updateReportSelection() {
            let count = 0;
            let total = 0;

            $('.report-order-checkbox:checked').each(function() {
                count++;
                total += parseFloat($(this).data('amount')) || 0;
            });

            $('#reportSelectedCount').text(count);
            $('#reportSelectedAmount').text(total.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

            if (count > 0) {
                $('#btnReportBulkPay').removeClass('d-none');
            } else {
                $('#btnReportBulkPay').addClass('d-none');
            }
        }

        $(document).on('change', '#reportSelectAll', function() {
            $('.report-order-checkbox').prop('checked', $(this).is(':checked'));
            updateReportSelection();
        });

        $(document).on('change', '.report-order-checkbox', function() {
            const allCount = $('.report-order-checkbox').length;
            const checkedCount = $('.report-order-checkbox:checked').length;
            $('#reportSelectAll').prop('checked', allCount > 0 && checkedCount === allCount);
            updateReportSelection();
        });

        // Open Pay Modal
        $(document).on('click', '#btnReportBulkPay', function() {
            const checkedBoxes = $('.report-order-checkbox:checked');
            if (checkedBoxes.length === 0) return;

            let count = checkedBoxes.length;
            let total = 0;
            let dates = [];

            checkedBoxes.each(function() {
                total += parseFloat($(this).data('amount')) || 0;
                let dateStr = $(this).data('date');
                if (dateStr && !dates.includes(dateStr)) {
                    dates.push(dateStr);
                }
            });

            $('#reportModalCount').text(count);
            $('#reportModalAmount').text(total.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

            // Formulate remark: "Laundry payment from [Start Date] to [End Date]"
            let defaultRemark = '';
            const reqFrom = "{{ request('date_from') }}";
            const reqTo = "{{ request('date_to') }}";

            if (reqFrom && reqTo) {
                defaultRemark = `Laundry payment from ${reqFrom} to ${reqTo}`;
            } else if (dates.length > 0) {
                const startDate = dates[0];
                const endDate = dates[dates.length - 1];
                if (startDate === endDate) {
                    defaultRemark = `Laundry payment for ${startDate}`;
                } else {
                    defaultRemark = `Laundry payment from ${startDate} to ${endDate}`;
                }
            } else {
                defaultRemark = `Laundry payment for ${count} order(s)`;
            }

            $('#reportPaymentRemarks').val(defaultRemark);
            $('#reportPaymentDate').val(new Date().toISOString().split('T')[0]);
            $('#reportBulkPayModal').modal('show');
        });

        // Submit Pay Form
        $('#reportBulkPayForm').on('submit', function(e) {
            e.preventDefault();
            const selectedIds = [];
            $('.report-order-checkbox:checked').each(function() {
                selectedIds.push($(this).val());
            });

            if (selectedIds.length === 0) {
                Swal.fire('Warning', 'Please select at least one order to pay', 'warning');
                return;
            }

            const paymentDate = $('#reportPaymentDate').val();
            const remarks = $('#reportPaymentRemarks').val();

            $('#btnReportConfirmPay').prop('disabled', true);
            $('#reportPaySpinner').removeClass('d-none');

            $.ajax({
                url: `{{ route('admin.laundry-orders.mark-paid') }}`,
                type: 'POST',
                data: JSON.stringify({
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    order_ids: selectedIds,
                    payment_date: paymentDate,
                    remarks: remarks
                }),
                contentType: 'application/json',
                success: function(response) {
                    $('#btnReportConfirmPay').prop('disabled', false);
                    $('#reportPaySpinner').addClass('d-none');
                    $('#reportBulkPayModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Payment Recorded!',
                        text: response.message,
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    $('#btnReportConfirmPay').prop('disabled', false);
                    $('#reportPaySpinner').addClass('d-none');
                    Swal.fire('Error', xhr.responseJSON?.message || 'Failed to process payment', 'error');
                }
            });
        });
    });
</script>
@endsection
