@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h5 class="card-title mb-0">Laundry Management</h5>
                        <small class="text-muted">Manage laundry orders and items</small>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        @can('pay laundry orders')
                        <button type="button" class="btn btn-success d-none" id="btnBulkPay">
                            <i class="bx bx-check-double me-1"></i> Pay Selected (<span id="selectedCountBadge">0</span>) - ₹<span id="selectedAmountBadge">0.00</span>
                        </button>
                        @endcan
                        @can('create laundry orders')
                        <button type="button" class="btn btn-primary" id="btnCreateOrder">
                            <i class="bx bx-plus me-1"></i> New Order
                        </button>
                        @endcan
                    </div>
                </div>

                <!-- Filters -->
                <div class="card-body border-bottom">
                    <form method="GET" action="{{ route('admin.laundry-orders.index') }}" class="row g-3 align-items-end">
                        @if($canViewAllStores)
                        <div class="col-12 col-sm-6 col-md-6 col-xl-3">
                            <label class="form-label">Store</label>
                            <select name="store_id" class="form-select" id="filterStore">
                                <option value="">All Stores</option>
                                @foreach($stores as $store)
                                <option value="{{ $store->id }}" {{ $selectedStoreId == $store->id ? 'selected' : '' }}>{{ $store->store_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        @if($canViewAllBranches || (!$canViewAllStores && count($branches) > 1))
                        <div class="col-12 col-sm-6 col-md-6 col-xl-3">
                            <label class="form-label">Branch</label>
                            <select name="branch_id" class="form-select" id="filterBranch">
                                <option value="">All Branches</option>
                                @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" data-store-id="{{ $branch->store_id }}" {{ $selectedBranchId == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="col-12 col-sm-6 col-md-3 col-xl-2">
                            <label class="form-label">Date From</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-12 col-sm-6 col-md-3 col-xl-2">
                            <label class="form-label">Date To</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-12 col-sm-12 col-md-6 col-xl-2 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary w-100 px-2 text-nowrap">
                                <i class="bx bx-search me-1"></i> Filter
                            </button>
                            <a href="{{ route('admin.laundry-orders.index') }}" class="btn btn-outline-secondary w-100 px-2 text-nowrap">
                                <i class="bx bx-reset me-1"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>

                <div class="card-body">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover align-middle" id="laundryOrdersTable">
                            <thead>
                                <tr>
                                    @can('pay laundry orders')
                                    <th style="width: 40px;">
                                        <input type="checkbox" class="form-check-input" id="selectAllOrders">
                                    </th>
                                    @endcan
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Store</th>
                                    <th>Branch</th>
                                    <th>Items</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>Payment Status</th>
                                    <th>Remark</th>
                                    @if(auth()->user()->can('edit laundry orders') || auth()->user()->can('delete laundry orders') || auth()->user()->can('receive laundry orders'))
                                    <th class="text-end">Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                <tr>
                                    @can('pay laundry orders')
                                    <td>
                                        @if(!$order->is_paid)
                                        <input type="checkbox" class="form-check-input order-checkbox" value="{{ $order->id }}" data-amount="{{ $order->total_amount }}" data-date="{{ $order->order_date ? $order->order_date->format('d M Y') : '' }}">
                                        @else
                                        <span class="text-success"><i class="bx bx-check-circle fs-5" title="Paid on {{ $order->paid_at ? $order->paid_at->format('d M Y') : '' }}"></i></span>
                                        @endif
                                    </td>
                                    @endcan
                                    <td>{{ $loop->iteration + ($orders->currentPage() - 1) * $orders->perPage() }}</td>
                                    <td>
                                        <span class="fw-bold text-dark">{{ $order->order_date ? $order->order_date->format('d M Y') : '-' }}</span>
                                    </td>
                                    <td>{{ $order->store->store_name ?? '-' }}</td>
                                    <td>{{ $order->branch->name ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-label-info">{{ $order->items->count() }} item(s)</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-success">₹{{ number_format($order->total_amount, 2) }}</span>
                                    </td>
                                    <td>
                                        @if($order->status == 'received')
                                            <span class="badge bg-label-success">Received</span>
                                        @elseif($order->status == 'partially_received')
                                            <span class="badge bg-label-warning">Partially Received</span>
                                        @else
                                            <span class="badge bg-label-secondary">Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($order->is_paid)
                                            <span class="badge bg-label-success"><i class="bx bx-check-circle me-1"></i>Paid</span>
                                        @else
                                            <span class="badge bg-label-danger"><i class="bx bx-x-circle me-1"></i>Unpaid</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 150px;" title="{{ $order->remark }}">
                                            {{ $order->remark ?? '-' }}
                                        </span>
                                    </td>
                                    @if(auth()->user()->can('edit laundry orders') || auth()->user()->can('delete laundry orders') || auth()->user()->can('receive laundry orders'))
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button class="btn p-0 dropdown-toggle hide-arrow" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                @can('receive laundry orders')
                                                <li>
                                                    <a class="dropdown-item text-primary btn-receive-order" href="javascript:void(0);" data-id="{{ $order->id }}">
                                                        <i class="bx bx-check-shield me-1"></i> Receive Items
                                                    </a>
                                                </li>
                                                @endcan
                                                @can('edit laundry orders')
                                                <li>
                                                    <a class="dropdown-item btn-edit-order" href="javascript:void(0);" data-id="{{ $order->id }}">
                                                        <i class="bx bx-edit-alt me-1"></i> Edit
                                                    </a>
                                                </li>
                                                @endcan
                                                @can('delete laundry orders')
                                                <li>
                                                    <a class="dropdown-item text-danger btn-delete-order" href="javascript:void(0);" data-id="{{ $order->id }}">
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
                                    <td colspan="11" class="text-center py-4">
                                        <div class="text-muted">No laundry orders found.</div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 d-flex justify-content-center justify-content-md-end flex-wrap">
                        {{ $orders->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Laundry Order Modal -->
<div class="modal fade" id="orderModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderModalTitle">New Laundry Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="orderForm">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="id" id="orderId">

                <div class="modal-body">
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-sm-6 col-md-3">
                            <label class="form-label" for="orderDate">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="order_date" id="orderDate" value="{{ date('Y-m-d') }}" required>
                            <div class="invalid-feedback" id="error-order_date"></div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <label class="form-label" for="orderStore">Store <span class="text-danger">*</span></label>
                            <select class="form-select" name="store_id" id="orderStore" required>
                                <option value="">Select Store</option>
                                @foreach($stores as $store)
                                <option value="{{ $store->id }}" {{ (auth()->user()->store_id == $store->id) ? 'selected' : '' }}>{{ $store->store_name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="error-store_id"></div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <label class="form-label" for="orderBranch">Branch <span class="text-danger">*</span></label>
                            <select class="form-select" name="branch_id" id="orderBranch" required>
                                <option value="">Select Branch</option>
                                @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" data-store-id="{{ $branch->store_id }}" {{ (auth()->user()->branch_id == $branch->id) ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="error-branch_id"></div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <label class="form-label" for="orderRemark">Remark</label>
                            <input type="text" class="form-control" name="remark" id="orderRemark" placeholder="Enter remark...">
                            <div class="invalid-feedback" id="error-remark"></div>
                        </div>
                    </div>

                    <!-- Items Section -->
                    <div class="border rounded p-3">
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            <h6 class="mb-0"><i class="bx bx-list-ul me-1"></i> Order Items</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddRow">
                                <i class="bx bx-plus me-1"></i> Add Item
                            </button>
                        </div>
                        <div class="invalid-feedback d-block" id="error-order_items" style="display:none !important;"></div>
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0 align-middle" id="itemsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:40px;">#</th>
                                        <th style="min-width:180px;">Item <span class="text-danger">*</span></th>
                                        <th style="min-width:90px; width:120px;">Qty <span class="text-danger">*</span></th>
                                        <th style="min-width:110px; width:140px;">Price <span class="text-danger">*</span></th>
                                        <th style="min-width:110px; width:140px;">Total</th>
                                        <th style="width:50px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemRows">
                                    <!-- Dynamic rows added via JS -->
                                </tbody>
                                <tfoot>
                                    <tr class="table-light">
                                        <td colspan="4" class="text-end fw-bold">Grand Total:</td>
                                        <td class="fw-bold text-success" id="grandTotal">₹0.00</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="modal-footer flex-wrap gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveOrder">
                        <span class="spinner-border spinner-border-sm d-none me-1" id="saveSpinner" role="status"></span>
                        Save Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Receive Items Modal -->
<div class="modal fade" id="receiveModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="receiveModalTitle">Receive Laundry Items</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="receiveForm">
                @csrf
                <input type="hidden" id="receiveOrderId">

                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40px;">#</th>
                                    <th style="min-width:160px;">Item Name</th>
                                    <th style="min-width:90px;">Sent Qty</th>
                                    <th style="min-width:120px;">Previously Received</th>
                                    <th style="min-width:120px;">Remaining Qty</th>
                                    <th style="min-width:140px;">Total Received Qty <span class="text-danger">*</span></th>
                                </tr>
                            </thead>
                            <tbody id="receiveItemRows">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer flex-wrap gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="btnSaveReceive">
                        <span class="spinner-border spinner-border-sm d-none me-1" id="receiveSpinner" role="status"></span>
                        Save Received Qty
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // Available items for dropdown
    const availableItems = @json($items);
    let rowIndex = 0;

    // Store-Branch dependency for modal
    if ($('#orderStore').length && $('#orderBranch').length) {
        window.setupDependentBranchSelect('#orderStore', '#orderBranch');
    }
    // Store-Branch dependency for filter
    if ($('#filterStore').length && $('#filterBranch').length) {
        window.setupDependentBranchSelect('#filterStore', '#filterBranch');
    }

    function buildItemOptions(selectedId) {
        let html = '<option value="">Select Item</option>';
        availableItems.forEach(item => {
            const sel = (selectedId && selectedId == item.id) ? 'selected' : '';
            html += `<option value="${item.id}" ${sel}>${item.name}</option>`;
        });
        return html;
    }

    function addItemRow(data = {}) {
        rowIndex++;
        const itemId = data.item_id || '';
        const qty = data.qty || 1;
        const price = data.price || '';
        const total = data.total || '0.00';

        const row = `
            <tr data-row="${rowIndex}">
                <td class="align-middle text-center row-num">${rowIndex}</td>
                <td>
                    <select class="form-select form-select-sm item-select" name="order_items[${rowIndex}][item_id]" required>
                        ${buildItemOptions(itemId)}
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm item-qty" name="order_items[${rowIndex}][qty]" value="${qty}" min="1" required>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm item-price" name="order_items[${rowIndex}][price]" value="${price}" min="0" step="0.01" required>
                </td>
                <td class="align-middle">
                    <span class="fw-bold item-total">₹${parseFloat(total).toFixed(2)}</span>
                </td>
                <td class="align-middle text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" title="Remove">
                        <i class="bx bx-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#itemRows').append(row);
        reIndexRows();
    }

    function reIndexRows() {
        let i = 1;
        $('#itemRows tr').each(function() {
            $(this).find('.row-num').text(i);
            i++;
        });
    }

    function calculateRowTotal(row) {
        const qty = parseFloat(row.find('.item-qty').val()) || 0;
        const price = parseFloat(row.find('.item-price').val()) || 0;
        const total = qty * price;
        row.find('.item-total').text('₹' + total.toFixed(2));
        calculateGrandTotal();
    }

    function calculateGrandTotal() {
        let grand = 0;
        $('#itemRows tr').each(function() {
            const qty = parseFloat($(this).find('.item-qty').val()) || 0;
            const price = parseFloat($(this).find('.item-price').val()) || 0;
            grand += qty * price;
        });
        $('#grandTotal').text('₹' + grand.toFixed(2));
    }

    // Add Row
    $('#btnAddRow').on('click', function() {
        addItemRow();
    });

    // Remove Row
    $(document).on('click', '.btn-remove-row', function() {
        $(this).closest('tr').remove();
        reIndexRows();
        calculateGrandTotal();
    });

    // Recalculate on qty/price change
    $(document).on('input', '.item-qty, .item-price', function() {
        calculateRowTotal($(this).closest('tr'));
    });

    function resetForm() {
        $('#orderForm')[0].reset();
        $('#orderId').val('');
        $('#formMethod').val('POST');
        $('#orderModalTitle').text('New Laundry Order');
        $('#orderDate').val('{{ date("Y-m-d") }}');
        $('#itemRows').empty();
        rowIndex = 0;
        $('#grandTotal').text('₹0.00');
        $('.form-control, .form-select').removeClass('is-invalid');
        $('.invalid-feedback').text('').hide();

        // Reset store/branch to user defaults
        @if(auth()->user()->store_id)
        $('#orderStore').val('{{ auth()->user()->store_id }}').trigger('change.depBranch');
        @endif
        @if(auth()->user()->branch_id)
        setTimeout(() => { $('#orderBranch').val('{{ auth()->user()->branch_id }}'); }, 100);
        @endif
    }

    // Create
    $('#btnCreateOrder').on('click', function() {
        resetForm();
        addItemRow(); // Start with one empty row
        $('#orderModal').modal('show');
    });

    // Edit
    $(document).on('click', '.btn-edit-order', function() {
        resetForm();
        const id = $(this).data('id');
        $('#orderModalTitle').text('Edit Laundry Order');
        $('#orderId').val(id);
        $('#formMethod').val('PUT');

        $.ajax({
            url: `{{ url('admin/laundry-orders') }}/${id}/edit`,
            type: 'GET',
            success: function(data) {
                $('#orderDate').val(data.order_date ? data.order_date.substring(0, 10) : '');
                $('#orderStore').val(data.store_id).trigger('change.depBranch');
                setTimeout(() => { $('#orderBranch').val(data.branch_id); }, 100);
                $('#orderRemark').val(data.remark);

                // Load items
                if (data.items && data.items.length) {
                    data.items.forEach(item => {
                        addItemRow({
                            item_id: item.item_id,
                            qty: item.qty,
                            price: item.price,
                            total: item.total
                        });
                    });
                } else {
                    addItemRow();
                }

                calculateGrandTotal();
                $('#orderModal').modal('show');
            },
            error: function() {
                Swal.fire('Error', 'Failed to fetch order details', 'error');
            }
        });
    });

    // Receive Items click handler
    $(document).on('click', '.btn-receive-order', function() {
        const id = $(this).data('id');
        $('#receiveOrderId').val(id);
        $('#receiveItemRows').empty();

        $.ajax({
            url: `{{ url('admin/laundry-orders') }}/${id}/receive`,
            type: 'GET',
            success: function(data) {
                $('#receiveModalTitle').text(`Receive Laundry Items - Order #${data.id}`);
                
                if (data.items && data.items.length) {
                    data.items.forEach((item, index) => {
                        const itemName = item.item ? item.item.name : 'Unknown Item';
                        const sentQty = parseInt(item.qty) || 0;
                        const prevReceived = (item.received_qty !== null && item.received_qty !== undefined) ? parseInt(item.received_qty) : 0;
                        const currentReceived = prevReceived;
                        const remaining = Math.max(0, sentQty - currentReceived);
                        const remainingBadgeClass = remaining === 0 ? 'bg-label-success' : 'bg-label-warning';

                        const row = `
                            <tr data-item-id="${item.id}">
                                <td class="text-center align-middle">${index + 1}</td>
                                <td class="align-middle"><span class="fw-bold text-dark">${itemName}</span></td>
                                <td class="align-middle"><span class="badge bg-label-secondary fs-6">${sentQty}</span></td>
                                <td class="align-middle"><span class="badge bg-label-info fs-6">${prevReceived}</span></td>
                                <td class="align-middle"><span class="badge ${remainingBadgeClass} fs-6 remaining-badge">${remaining}</span></td>
                                <td>
                                    <input type="number" class="form-control form-control-sm rec-qty-input" 
                                           data-sent="${sentQty}"
                                           value="${currentReceived}" min="0" max="${sentQty}" required>
                                </td>
                            </tr>
                        `;
                        $('#receiveItemRows').append(row);
                    });
                }
                $('#receiveModal').modal('show');
            },
            error: function() {
                Swal.fire('Error', 'Failed to fetch receive details', 'error');
            }
        });
    });

    // Live update remaining qty badge on input change
    $(document).on('input', '.rec-qty-input', function() {
        const $row = $(this).closest('tr');
        const sentQty = parseInt($(this).data('sent')) || 0;
        const currentVal = parseInt($(this).val()) || 0;
        const remaining = Math.max(0, sentQty - currentVal);
        const $badge = $row.find('.remaining-badge');
        
        $badge.text(remaining);
        if (remaining === 0) {
            $badge.removeClass('bg-label-warning bg-label-danger').addClass('bg-label-success');
        } else {
            $badge.removeClass('bg-label-success').addClass('bg-label-warning');
        }
    });

    // Submit Receive Form
    $('#receiveForm').on('submit', function(e) {
        e.preventDefault();
        const id = $('#receiveOrderId').val();

        const orderItems = [];
        $('#receiveItemRows tr').each(function() {
            const itemId = $(this).data('item-id');
            const receivedQty = parseInt($(this).find('.rec-qty-input').val()) || 0;
            orderItems.push({
                id: itemId,
                received_qty: receivedQty
            });
        });

        $('#btnSaveReceive').prop('disabled', true);
        $('#receiveSpinner').removeClass('d-none');

        $.ajax({
            url: `{{ url('admin/laundry-orders') }}/${id}/receive`,
            type: 'POST',
            data: JSON.stringify({
                _token: $('meta[name="csrf-token"]').attr('content'),
                order_items: orderItems
            }),
            contentType: 'application/json',
            success: function(response) {
                $('#btnSaveReceive').prop('disabled', false);
                $('#receiveSpinner').addClass('d-none');
                $('#receiveModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                $('#btnSaveReceive').prop('disabled', false);
                $('#receiveSpinner').addClass('d-none');
                Swal.fire('Error', xhr.responseJSON?.message || 'Something went wrong', 'error');
            }
        });
    });

    // Submit Order Form
    $('#orderForm').on('submit', function(e) {
        e.preventDefault();
        $('.form-control, .form-select').removeClass('is-invalid');
        $('.invalid-feedback').text('').hide();

        // Collect items data
        const orderItems = [];
        let hasError = false;
        $('#itemRows tr').each(function() {
            const itemId = $(this).find('.item-select').val();
            const qty = $(this).find('.item-qty').val();
            const price = $(this).find('.item-price').val();

            if (!itemId || !qty || !price) {
                hasError = true;
                if (!itemId) $(this).find('.item-select').addClass('is-invalid');
                if (!qty) $(this).find('.item-qty').addClass('is-invalid');
                if (!price) $(this).find('.item-price').addClass('is-invalid');
            }

            orderItems.push({
                item_id: itemId,
                qty: parseInt(qty),
                price: parseFloat(price)
            });
        });

        if (orderItems.length === 0) {
            $('#error-order_items').text('Please add at least one item.').show();
            return;
        }

        if (hasError) return;

        const id = $('#orderId').val();
        const method = $('#formMethod').val();
        const url = method === 'PUT'
            ? `{{ url('admin/laundry-orders') }}/${id}`
            : `{{ route('admin.laundry-orders.store') }}`;

        const payload = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            _method: method,
            order_date: $('#orderDate').val(),
            store_id: $('#orderStore').val(),
            branch_id: $('#orderBranch').val(),
            remark: $('#orderRemark').val(),
            order_items: orderItems
        };

        $('#btnSaveOrder').prop('disabled', true);
        $('#saveSpinner').removeClass('d-none');

        $.ajax({
            url: url,
            type: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json',
            success: function(response) {
                $('#btnSaveOrder').prop('disabled', false);
                $('#saveSpinner').addClass('d-none');
                $('#orderModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                $('#btnSaveOrder').prop('disabled', false);
                $('#saveSpinner').addClass('d-none');
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    $.each(errors, function(field, messages) {
                        // Handle nested item errors
                        if (field.startsWith('order_items.')) {
                            const parts = field.split('.');
                            const idx = parseInt(parts[1]);
                            const subField = parts[2];
                            const row = $(`#itemRows tr`).eq(idx);
                            if (row.length) {
                                if (subField === 'item_id') row.find('.item-select').addClass('is-invalid');
                                if (subField === 'qty') row.find('.item-qty').addClass('is-invalid');
                                if (subField === 'price') row.find('.item-price').addClass('is-invalid');
                            }
                        } else {
                            const input = $(`[name="${field}"]`);
                            input.addClass('is-invalid');
                            $(`#error-${field}`).text(messages[0]).show();
                        }
                    });
                } else {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Something went wrong', 'error');
                }
            }
        });
    });

    // Delete
    $(document).on('click', '.btn-delete-order', function() {
        const id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to delete this laundry order?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `{{ url('admin/laundry-orders') }}/${id}`,
                    type: 'DELETE',
                    success: function(response) {
                        Swal.fire('Deleted!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Failed to delete order', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endsection
