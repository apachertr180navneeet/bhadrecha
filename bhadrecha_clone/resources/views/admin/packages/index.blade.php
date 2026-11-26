@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Packages</h4>
            <small class="text-muted">Manage your customer packages</small>
        </div>
        @can('create packages')
        <a href="{{ route('admin.packages.create') }}" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i> Add Package
        </a>
        @endcan
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive text-nowrap">
                <table class="table table-hover" id="packagesTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            @if(auth()->user()->can('view packages') || auth()->user()->can('edit packages') || auth()->user()->can('delete packages'))
                            <th class="text-nowrap" style="width: 80px;">Actions</th>
                            @endif
                            <th>Customer</th>
                            <th>Services Included & Qty</th>
                            <th>Duration</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Total Qty</th>
                            <th>Amount</th>
                            <th>Advance</th>
                            <th>Remaining</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($packages as $package)
                        <tr>
                            <td>{{ $package->id }}</td>
                            @if(auth()->user()->can('view packages') || auth()->user()->can('edit packages') || auth()->user()->can('delete packages'))
                            <td>
                                <div class="dropdown">
                                    <button class="btn p-0 dropdown-toggle hide-arrow" type="button" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        @can('edit packages')
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.packages.edit', $package->id) }}">
                                                <i class="bx bx-edit-alt me-1"></i> Edit
                                            </a>
                                        </li>
                                        @endcan
                                        @can('view packages')
                                        <li>
                                            <a class="dropdown-item view-usage-details" href="javascript:void(0);" data-id="{{ $package->id }}">
                                                <i class="bx bx-pie-chart-alt-2 me-1 text-primary"></i> Usage Summary
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item view-payments" href="javascript:void(0);" data-id="{{ $package->id }}" data-history="{{ json_encode($package->payment_history) }}">
                                                <i class="bx bx-history me-1"></i> Payment History
                                            </a>
                                        </li>
                                        @endcan
                                        @can('delete packages')
                                        <li>
                                            <a class="dropdown-item text-danger delete-package" href="javascript:void(0);" data-id="{{ $package->id }}">
                                                <i class="bx bx-trash me-1"></i> Delete
                                            </a>
                                        </li>
                                        @endcan
                                    </ul>
                                </div>
                            </td>
                            @endif
                            <td class="fw-semibold">{{ $package->customer->name ?? 'N/A' }}</td>
                            <td>
                                @php
                                    $items = $package->services_with_qty;
                                @endphp
                                @if(count($items) > 0)
                                    @foreach($items as $sItem)
                                        @php $sObj = isset($services) ? $services->get($sItem['service_id']) : null; @endphp
                                        <span class="badge bg-label-primary me-1 mb-1" style="font-size: 0.8rem;">
                                            {{ $sObj->service_name ?? ('Service #'.$sItem['service_id']) }} <strong>(Qty: {{ $sItem['qty'] }})</strong>
                                        </span>
                                    @endforeach
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $package->duration->name ?? 'N/A' }}</td>
                            <td>{{ $package->start_date ? \Carbon\Carbon::parse($package->start_date)->format('d-m-Y') : '-' }}</td>
                            <td>{{ $package->end_date ? \Carbon\Carbon::parse($package->end_date)->format('d-m-Y') : '-' }}</td>
                            <td class="text-center fw-bold">{{ $package->qty }}</td>
                            <td>₹{{ number_format($package->amount, 2) }}</td>
                            <td>₹{{ number_format($package->advance, 2) }}</td>
                            <td>
                                @if($package->remaining > 0)
                                    <span class="text-danger fw-semibold">₹{{ number_format($package->remaining, 2) }}</span>
                                @else
                                    <span class="text-success fw-semibold">₹0.00</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Payment History Modal -->
<div class="modal fade" id="paymentHistoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payment History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Amount Paid</th>
                            </tr>
                        </thead>
                        <tbody id="paymentHistoryBody">
                            <!-- Populated by JS -->
                        </tbody>
                    </table>
                </div>
                <hr>
                <form id="addPaymentForm" method="POST" action="">
                    @csrf
                    <h6 class="fw-bold mb-3">Add New Payment</h6>
                    <div class="row">
                        <div class="col-md-5 mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="payment_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label class="form-label">Amount</label>
                            <input type="number" name="payment_amount" class="form-control form-control-sm" step="0.01" min="1" required>
                        </div>
                        <div class="col-md-2 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-sm btn-primary w-100">Add</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Package Usage Details Modal -->
<div class="modal fade" id="packageUsageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <div>
                    <h5 class="modal-title fw-bold" id="modalCustomerName">Package Usage Details</h5>
                    <small class="text-muted" id="modalPackageDates"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Summary Chips -->
                <div class="row g-2 mb-4" id="modalSummaryCards"></div>

                <!-- Per Service Breakdown -->
                <h6 class="fw-bold mb-2 text-dark"><i class="bx bx-list-check me-1 text-primary"></i> Services Breakdown</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Service Name</th>
                                <th class="text-center">Allocated</th>
                                <th class="text-center">Used</th>
                                <th class="text-center">Remaining</th>
                                <th style="width: 30%;">Usage Progress</th>
                            </tr>
                        </thead>
                        <tbody id="modalServicesBreakdownBody"></tbody>
                    </table>
                </div>

                <!-- Appointment Log -->
                <h6 class="fw-bold mb-2 text-dark"><i class="bx bx-calendar-check me-1 text-success"></i> Customer Appointments (Package Date Range)</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Apt #</th>
                                <th>Date & Time</th>
                                <th>Service</th>
                                <th>Staff</th>
                                <th>Status</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody id="modalAppointmentsBody"></tbody>
                    </table>
                </div>
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
    $(document).ready(function() {
        $('#packagesTable').DataTable({
            order: [[0, 'desc']]
        });

        $(document).on('click', '.view-usage-details', function() {
            const packageId = $(this).data('id');
            const modal = $('#packageUsageModal');

            $('#modalCustomerName').text('Loading Details...');
            $('#modalPackageDates').text('');
            $('#modalSummaryCards').empty();
            $('#modalServicesBreakdownBody').html('<tr><td colspan="5" class="text-center text-muted py-3"><div class="spinner-border spinner-border-sm text-primary me-2"></div> Fetching data...</td></tr>');
            $('#modalAppointmentsBody').html('<tr><td colspan="6" class="text-center text-muted py-3">Fetching data...</td></tr>');

            modal.modal('show');

            $.ajax({
                url: `{{ url('admin/packages') }}/${packageId}/usage-details`,
                method: 'GET',
                success: function(res) {
                    if (res.success) {
                        const p = res.package;
                        const u = res.usage;

                        $('#modalCustomerName').text(`Package #${p.id} - ${p.customer_name}`);
                        $('#modalPackageDates').html(`<strong>Taken Date:</strong> ${p.start_date} | <strong>End Date:</strong> ${p.end_date} | <strong>Amount:</strong> ₹${p.amount}`);

                        let statusBadge = '<span class="badge bg-success">Active</span>';
                        if (u.status === 'Fully Utilized') {
                            statusBadge = '<span class="badge bg-secondary">Fully Utilized</span>';
                        } else if (u.status === 'Expired') {
                            statusBadge = '<span class="badge bg-danger">Expired</span>';
                        }

                        $('#modalSummaryCards').html(`
                            <div class="col-md-3">
                                <div class="p-2 border rounded text-center bg-light">
                                    <small class="text-muted d-block">Allocated Qty</small>
                                    <span class="fw-bold text-dark fs-5">${u.total_allocated_qty}</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-2 border rounded text-center bg-light">
                                    <small class="text-muted d-block">Used Qty</small>
                                    <span class="fw-bold text-success fs-5">${u.total_used_qty}</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-2 border rounded text-center bg-light">
                                    <small class="text-muted d-block">Remaining Qty</small>
                                    <span class="fw-bold text-warning fs-5">${u.total_remaining_qty}</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-2 border rounded text-center bg-light d-flex flex-column justify-content-center align-items-center" style="height: 100%;">
                                    <small class="text-muted d-block mb-1">Status</small>
                                    ${statusBadge}
                                </div>
                            </div>
                        `);

                        let sbHtml = '';
                        if (u.services_breakdown && u.services_breakdown.length > 0) {
                            u.services_breakdown.forEach(function(sb) {
                                let pct = sb.allocated_qty > 0 ? Math.min(100, Math.round((sb.used_qty / sb.allocated_qty) * 100)) : 0;
                                let barClass = pct >= 100 ? 'bg-success' : 'bg-primary';

                                sbHtml += `<tr>
                                    <td class="fw-semibold text-dark">${sb.service_name}</td>
                                    <td class="text-center fw-bold">${sb.allocated_qty}</td>
                                    <td class="text-center fw-bold text-success">${sb.used_qty}</td>
                                    <td class="text-center fw-bold ${sb.remaining_qty > 0 ? 'text-warning' : 'text-muted'}">${sb.remaining_qty}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress w-100 me-2" style="height: 8px;">
                                                <div class="progress-bar ${barClass}" role="progressbar" style="width: ${pct}%"></div>
                                            </div>
                                            <small class="fw-semibold text-muted" style="min-width: 35px;">${pct}%</small>
                                        </div>
                                    </td>
                                </tr>`;
                            });
                        } else {
                            sbHtml = '<tr><td colspan="5" class="text-center text-muted">No services included in this package.</td></tr>';
                        }
                        $('#modalServicesBreakdownBody').html(sbHtml);

                        let aptHtml = '';
                        if (u.appointments && u.appointments.length > 0) {
                            u.appointments.forEach(function(apt) {
                                let stBadge = `<span class="badge bg-label-info">${apt.status}</span>`;
                                if (apt.status.toLowerCase() === 'completed') {
                                    stBadge = `<span class="badge bg-label-success">${apt.status}</span>`;
                                } else if (apt.status.toLowerCase() === 'cancelled') {
                                    stBadge = `<span class="badge bg-label-danger">${apt.status}</span>`;
                                }

                                aptHtml += `<tr>
                                    <td class="fw-bold text-primary">${apt.appointment_number}</td>
                                    <td>${apt.appointment_date}</td>
                                    <td><span class="badge bg-label-primary">${apt.service_name}</span></td>
                                    <td>${apt.staff_name}</td>
                                    <td>${stBadge}</td>
                                    <td class="fw-semibold">₹${apt.final_amount}</td>
                                </tr>`;
                            });
                        } else {
                            aptHtml = '<tr><td colspan="6" class="text-center text-muted py-3">No appointments found for this customer in the package validity period.</td></tr>';
                        }
                        $('#modalAppointmentsBody').html(aptHtml);
                    } else {
                        showErrorToast('Failed to load package details.');
                    }
                },
                error: function() {
                    showErrorToast('An error occurred while fetching package usage details.');
                }
            });
        });

        $('.view-payments').on('click', function() {
            let history = $(this).data('history');
            let packageId = $(this).data('id');
            let tbody = $('#paymentHistoryBody');
            
            // Set form action
            $('#addPaymentForm').attr('action', '{{ url("admin/packages") }}/' + packageId + '/add-payment');
            
            tbody.empty();
            if (history && history.length > 0) {
                history.forEach(function(item) {
                    tbody.append(`<tr>
                        <td>${item.date || '-'}</td>
                        <td class="fw-semibold">₹${parseFloat(item.amount).toFixed(2)}</td>
                    </tr>`);
                });
            } else {
                tbody.append('<tr><td colspan="2" class="text-center text-muted">No payments found.</td></tr>');
            }
            $('#paymentHistoryModal').modal('show');
        });

        $('.delete-package').on('click', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("admin.packages.destroy", "") }}/' + id,
                        method: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(res) {
                            if (res.success) {
                                showSuccessToast(res.message || 'Package deleted successfully.');
                                setTimeout(() => location.reload(), 1000);
                            }
                        },
                        error: function() {
                            showErrorToast('Failed to delete package.');
                        }
                    });
                }
            });
        });
    });
</script>
@endsection
