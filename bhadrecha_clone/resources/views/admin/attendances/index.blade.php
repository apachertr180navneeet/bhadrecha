@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h5 class="card-title mb-0">Attendance Management</h5>
                        <small class="text-muted">Mark and manage staff attendance</small>
                    </div>
                    <div class="mt-2 mt-md-0">
                        <form method="GET" action="{{ route('admin.attendances.index') }}" class="d-flex align-items-center gap-2">
                            <select name="month" class="form-select form-select-sm" onchange="this.form.submit()">
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                @endforeach
                            </select>
                            <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                                @foreach(range(date('Y'), date('Y') - 5) as $y)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                            <a href="{{ route('admin.attendances.report') }}" class="btn btn-sm btn-outline-primary"><i class="bx bx-bar-chart-alt-2 me-1"></i> Report</a>
                        </form>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="white-space: nowrap; font-size: 0.875rem;">
                        <thead class="table-light border-bottom">
                            <tr>
                                <th scope="col" class="py-3 px-3 text-secondary fw-medium" style="min-width: 250px;">Employee</th>
                                
                                @foreach (range(1, $daysInMonth) as $day)
                                    @php
                                        $dayOfWeek = \Carbon\Carbon::createFromDate($year, $month, $day)->format('D');
                                    @endphp
                                    <th scope="col" class="text-center px-1 py-2" style="min-width: 32px;">
                                        <div class="text-dark fw-bold" style="font-size: 0.85rem;">{{ $day }}</div>
                                        <div class="text-muted" style="font-size: 0.7rem;">{{ $dayOfWeek }}</div>
                                    </th>
                                @endforeach
                                
                                <th scope="col" class="text-center py-3 px-3 text-secondary fw-medium" style="min-width: 80px;">Total</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @forelse($staffs as $staff)
                            <tr>
                                <td class="px-3 py-2">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3" style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;">
                                            <span class="avatar-initial rounded-circle bg-label-primary" style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;font-size:0.9rem;font-weight:600;">
                                                {{ strtoupper(substr($staff->full_name ?? '--', 0, 2)) }}
                                            </span>
                                        </div>
                                        
                                        <div>
                                            <div class="d-flex align-items-center mb-0 text-dark fw-bold" style="font-size: 0.9rem;">
                                                {{ $staff->full_name }}
                                                @if(auth()->id() == ($staff->user_id ?? 0))
                                                <span class="badge bg-secondary ms-2" style="font-size: 0.65rem; border-radius: 4px;">It's you</span>
                                                @endif
                                            </div>
                                            <div class="text-muted" style="font-size: 0.8rem;">Staff</div>
                                        </div>
                                    </div>
                                </td>
                                
                                @foreach (range(1, $daysInMonth) as $day)
                                    @php
                                        $currentDate = \Carbon\Carbon::createFromDate($year, $month, $day)->format('Y-m-d');
                                        // Find attendance record for this date from the pre-loaded relationship
                                        $attendance = null;
                                        if ($staff->attendances) {
                                            $attendance = $staff->attendances->first(function($item) use ($currentDate) {
                                                $itemDate = is_string($item->date) ? substr($item->date, 0, 10) : $item->date->format('Y-m-d');
                                                return $itemDate === $currentDate;
                                            });
                                        }
                                        $currentStatus = $attendance ? $attendance->status : '';
                                        $checkInTime = $attendance && $attendance->check_in ? $attendance->check_in->format('H:i') : '';
                                        $checkOutTime = $attendance && $attendance->check_out ? $attendance->check_out->format('H:i') : '';
                                    @endphp
                                    <td class="text-center px-1 border-start-0 border-end-0 attendance-cell"
                                        @if(auth()->user()->can('create attendances') || auth()->user()->can('edit attendances')) style="cursor: pointer;" @else style="cursor: default;" @endif
                                        data-staff="{{ $staff->id }}"
                                        data-date="{{ $currentDate }}"
                                        data-status="{{ $currentStatus }}"
                                        data-checkin="{{ $checkInTime }}"
                                        data-checkout="{{ $checkOutTime }}"
                                        id="cell-{{ $staff->id }}-{{ $day }}"
                                    >
                                        <div class="status-icon-container">
                                        @if ($currentStatus == 'present')
                                            <!-- Green Check -->
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#198754" viewBox="0 0 16 16"><path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/></svg>
                                        @elseif ($currentStatus == 'holiday')
                                            <!-- Yellow Star -->
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="#ffc107" viewBox="0 0 16 16"><path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/></svg>
                                        @elseif ($currentStatus == 'absent')
                                            <!-- Red Cross -->
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="#dc3545" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8 2.146 2.854Z"/></svg>
                                        @elseif ($currentStatus == 'half_day')
                                            <!-- Purple Circle Half -->
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="#6f42c1" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 0 8 1v14zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16z"/></svg>
                                        @elseif ($currentStatus == 'late')
                                            <!-- Orange Clock -->
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="#fd7e14" viewBox="0 0 16 16"><path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/></svg>
                                        @elseif ($currentStatus == 'leave')
                                            <!-- Airplane (Leave) -->
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="#0dcaf0" viewBox="0 0 16 16"><path d="M6.428 1.151C6.708.527 7.265 0 8 0s1.292.527 1.572 1.151C9.861 1.73 10 2.431 10 3v3.691l5.17 2.585a1.5 1.5 0 0 1 .83 1.342V12a.5.5 0 0 1-.582.493l-5.507-.918-.375 2.253 1.318 1.318A.5.5 0 0 1 10.5 16h-5a.5.5 0 0 1-.354-.854l1.319-1.318-.376-2.253-5.507.918A.5.5 0 0 1 0 12v-1.382a1.5 1.5 0 0 1 .83-1.342L6 6.691V3c0-.568.14-1.271.428-1.849Z"/></svg>
                                        @else
                                            <!-- Blue Dash for No Record -->
                                            <span class="text-primary fw-bold" style="font-size: 0.9rem;">-</span>
                                        @endif
                                        </div>
                                    </td>
                                @endforeach
                                
                                <td class="text-center px-3 text-dark fw-bold" style="font-size: 0.9rem;">
                                    {{ $staff->attendances->whereIn('status', ['present', 'late', 'leave', 'holiday', 'half_day'])->count() }} <span class="text-muted fw-normal" style="font-size: 0.8rem;">/ {{ $daysInMonth }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="33" class="text-center py-4 text-muted">No staff members found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Modal -->
<div class="modal fade" id="attendanceModal" tabindex="-1" aria-labelledby="attendanceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="attendanceModalLabel">Update Attendance</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="attendanceUpdateForm">
            <input type="hidden" id="modal_staff_id">
            <input type="hidden" id="modal_date">
            <input type="hidden" id="modal_day_index">
            
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select class="form-select" id="modal_status">
                    <option value="present">Present</option>
                    <option value="absent">Absent</option>
                    <option value="half_day">Half Day</option>
                    <option value="late">Late</option>
                    <option value="leave">Leave</option>
                    <option value="holiday">Holiday</option>
                </select>
            </div>
            
            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label">Check In</label>
                    <input type="time" class="form-control" id="modal_check_in">
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label">Check Out</label>
                    <input type="time" class="form-control" id="modal_check_out">
                </div>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="saveAttendanceBtn">Save changes</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
<script>
$(function() {
    // Open modal on cell click
    @if(auth()->user()->can('create attendances') || auth()->user()->can('edit attendances'))
    $('.attendance-cell').on('click', function() {
        let staffId = $(this).data('staff');
        let date = $(this).data('date');
        let status = $(this).data('status');
        let checkin = $(this).data('checkin');
        let checkout = $(this).data('checkout');
        let dayIndex = $(this).attr('id').split('-')[2];
        
        $('#modal_staff_id').val(staffId);
        $('#modal_date').val(date);
        $('#modal_status').val(status);
        $('#modal_check_in').val(checkin);
        $('#modal_check_out').val(checkout);
        $('#modal_day_index').val(dayIndex);
        
        $('#attendanceModal').modal('show');
    });
    @endif

    // Save attendance from modal
    $('#saveAttendanceBtn').on('click', function() {
        let btn = $(this);
        let staffId = $('#modal_staff_id').val();
        let date = $('#modal_date').val();
        let status = $('#modal_status').val();
        let check_in = $('#modal_check_in').val();
        let check_out = $('#modal_check_out').val();
        let dayIndex = $('#modal_day_index').val();
        
        let originalText = btn.text();
        btn.text('Saving...').prop('disabled', true);
        
        $.ajax({
            url: '{{ route("admin.attendances.store") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                staff_id: staffId,
                date: date,
                status: status,
                check_in: check_in,
                check_out: check_out
            },
            success: function(response) {
                $('#attendanceModal').modal('hide');
                if (window.toastr) {
                    window.toastr.success('Attendance updated successfully');
                } else if (window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Attendance updated successfully',
                        showConfirmButton: false,
                        timer: 1500
                    });
                } else {
                    alert('Attendance updated successfully');
                }
                
                // Update UI dynamically
                let cell = $('#cell-' + staffId + '-' + dayIndex);
                cell.data('status', status);
                cell.data('checkin', check_in);
                cell.data('checkout', check_out);
                
                let iconHtml = '';
                if (status === 'present') {
                    iconHtml = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#198754" viewBox="0 0 16 16"><path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/></svg>';
                } else if (status === 'holiday') {
                    iconHtml = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="#ffc107" viewBox="0 0 16 16"><path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/></svg>';
                } else if (status === 'absent') {
                    iconHtml = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="#dc3545" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8 2.146 2.854Z"/></svg>';
                } else if (status === 'half_day') {
                    iconHtml = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="#6f42c1" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 0 8 1v14zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16z"/></svg>';
                } else if (status === 'late') {
                    iconHtml = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="#fd7e14" viewBox="0 0 16 16"><path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/></svg>';
                } else if (status === 'leave') {
                    iconHtml = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="#0dcaf0" viewBox="0 0 16 16"><path d="M6.428 1.151C6.708.527 7.265 0 8 0s1.292.527 1.572 1.151C9.861 1.73 10 2.431 10 3v3.691l5.17 2.585a1.5 1.5 0 0 1 .83 1.342V12a.5.5 0 0 1-.582.493l-5.507-.918-.375 2.253 1.318 1.318A.5.5 0 0 1 10.5 16h-5a.5.5 0 0 1-.354-.854l1.319-1.318-.376-2.253-5.507.918A.5.5 0 0 1 0 12v-1.382a1.5 1.5 0 0 1 .83-1.342L6 6.691V3c0-.568.14-1.271.428-1.849Z"/></svg>';
                } else {
                    iconHtml = '<span class="text-primary fw-bold" style="font-size: 0.9rem;">-</span>';
                }
                
                cell.find('.status-icon-container').html(iconHtml);
            },
            error: function() {
                showErrorToast('Failed to update attendance');
            },
            complete: function() {
                btn.text(originalText).prop('disabled', false);
            }
        });
    });
});
</script>
@endsection
