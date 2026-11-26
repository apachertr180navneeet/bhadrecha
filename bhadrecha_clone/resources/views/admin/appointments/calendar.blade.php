@extends('admin.layouts.app')

@section('style')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
<style>
    #calendar { min-height: 600px; }
    .fc-toolbar-title { font-size: 1.25rem !important; font-weight: 600; }
    .fc-button-primary { background-color: #7367f0 !important; border-color: #7367f0 !important; }
    .fc-button-primary:not(:disabled).fc-button-active, .fc-button-primary:not(:disabled):active { background-color: #5e50ee !important; border-color: #5e50ee !important; }
    .fc-button-primary:hover { background-color: #5e50ee !important; border-color: #5e50ee !important; }
    .fc-event { cursor: pointer; border-radius: 6px; padding: 2px 6px; font-size: 0.8rem; border: none; }
    .fc-daygrid-event-dot { display: none; }
    .fc-col-header-cell-cushion { font-weight: 600; text-decoration: none; }
    .fc-daygrid-day-number { text-decoration: none; }
</style>
@endsection

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Appointments Calendar</h4>
            <small class="text-muted">View and manage appointments visually</small>
        </div>
        <div>
            <a href="{{ route('admin.appointments.index') }}" class="btn btn-outline-primary">
                <i class="bx bx-list-ul me-1"></i> List View
            </a>
        </div>
    <!-- Store & Branch Filter Bar -->
    <div class="card mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.appointments.calendar') }}" id="calendarFilterForm" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label text-muted fw-semibold">Store</label>
                    <select name="store_id" id="calendarStoreSelect" class="form-select" {{ (!$canViewAllStores && $selectedStoreId) ? 'disabled' : '' }}>
                        <option value="">All Stores</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}" {{ (string)($selectedStoreId ?? request('store_id')) === (string)$store->id ? 'selected' : '' }}>{{ $store->store_name ?? $store->name }}</option>
                        @endforeach
                    </select>
                    @if(!$canViewAllStores && $selectedStoreId)
                        <input type="hidden" name="store_id" value="{{ $selectedStoreId }}">
                    @endif
                </div>
                <div class="col-md-5">
                    <label class="form-label text-muted fw-semibold">Branch</label>
                    <select name="branch_id" id="calendarBranchSelect" class="form-select" {{ (!$canViewAllBranches && $selectedBranchId) ? 'disabled' : '' }}>
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" data-store-id="{{ $branch->store_id }}" {{ (string)($selectedBranchId ?? request('branch_id')) === (string)$branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    @if(!$canViewAllBranches && $selectedBranchId)
                        <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">
                    @endif
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1"><i class="bx bx-filter-alt me-1"></i> Filter</button>
                    <a href="{{ route('admin.appointments.calendar') }}" class="btn btn-outline-secondary flex-grow-1">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div id="calendar"></div>
        </div>
    </div>
</div>

<!-- Event Details Modal -->
<div class="modal fade" id="eventDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventDetailTitle">Appointment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="eventDetailBody">
                <div class="text-center py-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" class="btn btn-primary" id="eventDetailEditBtn">Edit Appointment</a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const events = @json($events ?? []);

        const calendarEl = document.getElementById('calendar');
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            eventTimeFormat: {
                hour: 'numeric',
                minute: '2-digit',
                meridiem: 'short'
            },
            slotLabelFormat: {
                hour: 'numeric',
                minute: '2-digit',
                meridiem: 'short'
            },
            editable: false,
            selectable: true,
            navLinks: true,
            dayMaxEvents: true,
            events: events,
            eventColor: '#7367f0',
            eventClick: function(info) {
                const eventId = info.event.id;

                $('#eventDetailTitle').text(info.event.title);
                $('#eventDetailBody').html('<div class="text-center py-3"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
                $('#eventDetailEditBtn').attr('href', 'javascript:void(0);');
                $('#eventDetailModal').modal('show');

                $.ajax({
                    url: '{{ route("admin.appointments.show", "") }}/' + eventId,
                    method: 'GET',
                    success: function(res) {
                        if (res.success) {
                            const a = res.data;
                            const statusColors = {
                                pending: 'warning', confirmed: 'info', checked_in: 'primary',
                                in_progress: 'secondary', completed: 'success', cancelled: 'danger', no_show: 'dark'
                            };
                            const sc = statusColors[a.status] || 'secondary';

                            let html = '<div class="mb-3">';
                            html += '<div class="d-flex justify-content-between mb-2"><span class="text-muted">Customer:</span><span class="fw-semibold">' + (a.customer?.full_name || a.customer?.name || 'N/A') + '</span></div>';
                            html += '<div class="d-flex justify-content-between mb-2"><span class="text-muted">Staff:</span><span class="fw-semibold">' + (a.staff?.full_name || a.staff?.name || 'N/A') + '</span></div>';
                            html += '<div class="d-flex justify-content-between mb-2"><span class="text-muted">Service:</span><span class="fw-semibold">' + (a.service?.name || 'N/A') + '</span></div>';
                            html += '<div class="d-flex justify-content-between mb-2"><span class="text-muted">Date/Time:</span><span class="fw-semibold">' + moment(a.date_time).format('MMM D, YYYY h:mm A') + '</span></div>';
                            html += '<div class="d-flex justify-content-between mb-2"><span class="text-muted">Amount:</span><span class="fw-semibold">₹' + parseFloat(a.amount).toFixed(2) + '</span></div>';
                            html += '<div class="d-flex justify-content-between align-items-center mb-2"><span class="text-muted">Status:</span><span class="badge bg-label-' + sc + '">' + a.status.replace(/_/g, ' ') + '</span></div>';
                            if (a.notes) {
                                html += '<div class="d-flex justify-content-between mb-2"><span class="text-muted">Notes:</span><span class="fw-semibold">' + a.notes + '</span></div>';
                            }
                            html += '<hr class="my-3">';
                            html += '<div class="mb-2"><label class="form-label text-muted fw-semibold">Quick Change Status</label>';
                            html += '<div class="input-group"><select class="form-select" id="calStatusSelect">';
                            ['pending', 'confirmed', 'checked_in', 'in_progress', 'completed', 'cancelled', 'no_show'].forEach(s => {
                                html += `<option value="${s}" ${a.status === s ? 'selected' : ''}>${s.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())}</option>`;
                            });
                            html += '</select><button class="btn btn-outline-primary" type="button" id="calUpdateStatusBtn" data-id="' + eventId + '">Update</button></div></div>';
                            html += '</div>';

                            $('#eventDetailBody').html(html);
                            $('#eventDetailEditBtn').attr('href', '{{ route("admin.appointments.index") }}/' + eventId + '/edit').off('click');
                        } else {
                            $('#eventDetailBody').html('<p class="text-danger mb-0">Failed to load details.</p>');
                        }
                    },
                    error: function() {
                        $('#eventDetailBody').html('<p class="text-danger mb-0">Failed to load appointment details.</p>');
                    }
                });

                modal.show();
            }
        });

        $(document).on('click', '#calUpdateStatusBtn', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            const status = $('#calStatusSelect').val();
            
            if (!id || !status) {
                alert('Invalid status update request.');
                return;
            }
            
            let ajaxData = { _token: '{{ csrf_token() }}', status: status };
            
            const doUpdate = (extraData = {}) => {
                const url = '{{ route("admin.appointments.update-status", ":id") }}'.replace(':id', id).replace('%3Aid', id);
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: Object.assign(ajaxData, extraData),
                    success: function(res) {
                        if (res.success) {
                            if (res.whatsapp_url) {
                                window.open(res.whatsapp_url, '_blank');
                            }
                            location.reload();
                        } else {
                            alert(res.message || 'Failed to update status.');
                        }
                    },
                    error: function(xhr) {
                        const msg = (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error)) 
                            ? (xhr.responseJSON.message || xhr.responseJSON.error) 
                            : 'Failed to update status.';
                        alert(msg);
                    }
                });
            };

            if (status === 'completed') {
                Swal.fire({
                    title: 'Complete Appointment',
                    html: `
                        <div class="mb-3 text-start mt-3">
                            <label class="form-label fw-semibold">Payment Mode</label>
                            <select id="swal-cal-pay-type" class="form-select">
                                <option value="cash">Cash</option>
                                <option value="upi">UPI</option>
                                <option value="card">Card</option>
                                <option value="split">Split Payment (Cash + UPI)</option>
                            </select>
                        </div>
                        <div class="mb-3 text-start" id="swal-cal-paid-box">
                            <label class="form-label fw-semibold">Paid Amount (₹)</label>
                            <input type="number" id="swal-cal-paid-amount" class="form-control" step="0.01" min="0" placeholder="Enter Paid Amount">
                        </div>
                        <div class="mb-3 text-start d-none" id="swal-cal-split-box">
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label fw-semibold text-success"><i class="bx bx-money me-1"></i>Cash (₹)</label>
                                    <input type="number" id="swal-cal-cash-amount" class="form-control" step="0.01" min="0" placeholder="0.00">
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold text-primary"><i class="bx bx-qr-scan me-1"></i>UPI (₹)</label>
                                    <input type="number" id="swal-cal-upi-amount" class="form-control" step="0.01" min="0" placeholder="0.00">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 text-start d-none" id="swal-cal-upi-box">
                            <label class="form-label fw-semibold">UPI Reference / Transaction ID</label>
                            <input type="text" id="swal-cal-upi-ref" class="form-control" placeholder="Enter UPI Reference">
                        </div>
                    `,
                    didOpen: () => {
                        const pt = document.getElementById('swal-cal-pay-type');
                        const upiBox = document.getElementById('swal-cal-upi-box');
                        const splitBox = document.getElementById('swal-cal-split-box');
                        const paidBox = document.getElementById('swal-cal-paid-box');

                        pt.addEventListener('change', (e) => {
                            const val = e.target.value;
                            if (val === 'split') {
                                paidBox.classList.add('d-none');
                                splitBox.classList.remove('d-none');
                                upiBox.classList.remove('d-none');
                            } else if (val === 'upi') {
                                paidBox.classList.remove('d-none');
                                splitBox.classList.add('d-none');
                                upiBox.classList.remove('d-none');
                            } else {
                                paidBox.classList.remove('d-none');
                                splitBox.classList.add('d-none');
                                upiBox.classList.add('d-none');
                            }
                        });
                    },
                    preConfirm: () => {
                        const payment_type = document.getElementById('swal-cal-pay-type').value;
                        const upi_reference = document.getElementById('swal-cal-upi-ref').value;
                        const paid_amount = document.getElementById('swal-cal-paid-amount').value;
                        const cash_amount = document.getElementById('swal-cal-cash-amount').value;
                        const upi_amount = document.getElementById('swal-cal-upi-amount').value;

                        if (payment_type === 'split') {
                            if (!cash_amount && !upi_amount) {
                                Swal.showValidationMessage('Please enter Cash or UPI amount');
                                return false;
                            }
                            if (parseFloat(upi_amount) > 0 && !upi_reference.trim()) {
                                Swal.showValidationMessage('Please enter UPI reference');
                                return false;
                            }
                            return { payment_type, cash_amount, upi_amount, upi_reference };
                        }

                        if (payment_type === 'upi' && !upi_reference.trim()) {
                            Swal.showValidationMessage('Please enter UPI reference');
                            return false;
                        }
                        const res = { payment_type, upi_reference };
                        if (paid_amount !== '' && paid_amount !== null) {
                            res.paid_amount = paid_amount;
                        }
                        return res;
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Confirm & Complete'
                }).then((r) => {
                    if (r.isConfirmed && r.value) {
                        doUpdate(r.value);
                    }
                });
            } else {
                doUpdate();
            }
        });

        calendar.render();

        if (window.setupDependentBranchSelect) {
            window.setupDependentBranchSelect('#calendarStoreSelect', '#calendarBranchSelect');
        }
    });
</script>
@endsection
