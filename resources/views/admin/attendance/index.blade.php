@extends('admin.layouts.app')

@section('style')
<style>
    .stat-card {
        border: none;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        transition: all 0.3s ease;
        overflow: hidden;
        position: relative;
    }
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
    }
    .stat-card.card-green::before { background: linear-gradient(90deg, #71dd37, #8be65c); }
    .stat-card.card-red::before { background: linear-gradient(90deg, #ff3e1d, #ff6b52); }
    .stat-card.card-orange::before { background: linear-gradient(90deg, #ffab00, #ffc44d); }
    .stat-card.card-blue::before { background: linear-gradient(90deg, #696cff, #8592ff); }
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
    }
    .stat-card .icon-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }
    .stat-card .stat-value {
        font-size: 26px;
        font-weight: 800;
        line-height: 1.1;
    }
    .stat-card .stat-label {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        opacity: 0.7;
    }

    .filter-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    }

    .attendance-grid {
        font-size: 13px;
    }
    .attendance-grid thead th {
        text-align: center;
        vertical-align: middle;
        padding: 12px 6px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #5d6778;
        background: #f8f9fc;
        border-bottom: 2px solid #eaecef;
    }
    .attendance-grid thead th.emp-head {
        text-align: left;
        padding-left: 16px;
    }
    .attendance-grid tbody td {
        text-align: center;
        padding: 10px 6px;
        vertical-align: middle;
    }
    .attendance-grid tbody tr {
        transition: background 0.2s;
    }
    .attendance-grid tbody tr:hover {
        background: #f8f9fc;
    }
    .attendance-grid .emp-cell {
        text-align: left;
        min-width: 190px;
        padding-left: 16px;
    }
    .att-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.3px;
        transition: all 0.2s ease;
        cursor: default;
    }
    .att-badge:hover {
        transform: scale(1.2);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .att-present { background: #e8f5e9; color: #2e7d32; }
    .att-absent { background: #fbe9e7; color: #c62828; }
    .att-half-day { background: #fff8e1; color: #f57f17; }
    .att-leave { background: #e3f2fd; color: #1565c0; }
    .att-sunday { background: #f5f5f6; color: #b0b0b8; }

    .day-header {
        font-weight: 400;
        font-size: 9px;
        margin-top: 2px;
        opacity: 0.6;
    }
    .day-header.sun { color: #e53935; opacity: 1; }

    .legend-dot {
        display: inline-block;
        width: 14px;
        height: 14px;
        border-radius: 4px;
        margin-right: 8px;
    }
    .legend-item {
        font-size: 13px;
        color: #5d6778;
    }

    .summary-num {
        font-size: 18px;
        font-weight: 800;
    }

    .mark-btn {
        opacity: 1;
        transition: all 0.25s ease;
        border-radius: 8px;
        padding: 4px 8px;
        background: rgba(105, 108, 255, 0.08);
        color: #696cff;
    }
    .mark-btn:hover {
        background: #696cff;
        color: #fff;
    }

    .sticky-col {
        position: sticky;
        left: 0;
        z-index: 2;
        background: #fff;
        border-right: 2px solid #eaecef;
    }
    .sticky-col::after {
        content: '';
        position: absolute;
        top: 0;
        right: -2px;
        width: 2px;
        height: 100%;
        background: #eaecef;
    }
    .table-responsive {
        border-radius: 16px;
    }
    .status-column {
        min-width: 48px;
        background: #fafbfc;
    }
</style>
@endsection

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
        <i class="bx bx-check-circle me-2" style="font-size: 20px;"></i>
        <span>{{ session('success') }}</span>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
        <i class="bx bx-x-circle me-2" style="font-size: 20px;"></i>
        <span>{{ session('error') }}</span>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('info'))
    <div class="alert alert-info alert-dismissible fade show d-flex align-items-center" role="alert">
        <i class="bx bx-info-circle me-2" style="font-size: 20px;"></i>
        <span>{{ session('info') }}</span>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1" style="font-size: 24px;">Attendance Overview</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0" style="font-size: 14px;">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Attendance</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary rounded-pill px-4" onclick="openMarkModal({{ auth()->id() }}, '{{ auth()->user()->full_name }}')">
                <i class="bx bx-edit me-1"></i> Mark Attendance
            </button>
            <form method="POST" action="{{ route('admin.attendance.check-in') }}" style="display:inline">
                @csrf
                <input type="hidden" name="date" value="{{ date('Y-m-d') }}">
                <button type="submit" class="btn btn-success rounded-pill px-4"><i class="bx bx-log-in me-1"></i> Check In</button>
            </form>
            <form method="POST" action="{{ route('admin.attendance.check-out') }}" style="display:inline">
                @csrf
                <input type="hidden" name="date" value="{{ date('Y-m-d') }}">
                <button type="submit" class="btn btn-warning text-white rounded-pill px-4"><i class="bx bx-log-out me-1"></i> Check Out</button>
            </form>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3 col-6">
            <div class="stat-card card-green p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-box" style="background: rgba(113,221,55,0.12); color: #71dd37;">
                        <i class="bx bx-check-circle"></i>
                    </div>
                    <div>
                        <div class="stat-label text-success">Present</div>
                        <div class="stat-value text-success">{{ $todayStats['present'] ?? 0 }}</div>
                        <small class="text-muted">today</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card card-red p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-box" style="background: rgba(255,62,29,0.12); color: #ff3e1d;">
                        <i class="bx bx-x-circle"></i>
                    </div>
                    <div>
                        <div class="stat-label text-danger">Absent</div>
                        <div class="stat-value text-danger">{{ $todayStats['absent'] ?? 0 }}</div>
                        <small class="text-muted">today</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card card-orange p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-box" style="background: rgba(255,171,0,0.12); color: #ffab00;">
                        <i class="bx bx-time"></i>
                    </div>
                    <div>
                        <div class="stat-label text-warning">Half Day / Leave</div>
                        <div class="stat-value text-warning">{{ ($todayStats['half-day'] ?? 0) + ($todayStats['leave'] ?? 0) }}</div>
                        <small class="text-muted">today</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card card-blue p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-box" style="background: rgba(105,108,255,0.12); color: #696cff;">
                        <i class="bx bx-calendar"></i>
                    </div>
                    <div>
                        <div class="stat-label text-primary">Period</div>
                        <div class="stat-value text-primary">{{ Carbon\Carbon::create()->month($month)->format('M') }}</div>
                        <small class="text-muted">{{ $year }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card filter-card mb-4">
        <div class="card-body px-4 py-3">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-muted small mb-1">Month</label>
                    <select name="month" class="form-select form-select-sm">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ Carbon\Carbon::create()->month($m)->format('F') }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold text-muted small mb-1">Year</label>
                    <select name="year" class="form-select form-select-sm">
                        @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                @if(auth()->user()->isSuperAdmin())
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-muted small mb-1">Employee</label>
                    <select name="user_id" class="form-select form-select-sm">
                        <option value="">All Employees</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ $employeeId == $u->id ? 'selected' : '' }}>{{ $u->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 btn-sm"><i class="bx bx-filter-alt me-1"></i> Apply</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.attendance.index') }}" class="btn btn-outline-secondary w-100 btn-sm">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="d-flex gap-4 mb-3 flex-wrap align-items-center">
        <span class="legend-item d-flex align-items-center"><span class="legend-dot att-present"></span> Present</span>
        <span class="legend-item d-flex align-items-center"><span class="legend-dot att-absent"></span> Absent</span>
        <span class="legend-item d-flex align-items-center"><span class="legend-dot att-half-day"></span> Half Day</span>
        <span class="legend-item d-flex align-items-center"><span class="legend-dot att-leave"></span> Leave</span>
        <span class="legend-item d-flex align-items-center"><span class="legend-dot att-sunday"></span> Sunday</span>
    </div>

    <div class="card shadow-sm" style="border: none; border-radius: 16px;">
        <div class="table-responsive" style="border-radius: 16px;">
            <table class="table table-bordered attendance-grid mb-0" style="border-color: #eaecef;">
                <thead>
                    <tr>
                        <th class="emp-head" style="min-width: 200px;">Employee</th>
                        <th style="min-width: 80px;">Company</th>
                        @for($d = 1; $d <= $daysInMonth; $d++)
                            @php
                                $dateObj = Carbon\Carbon::create(intval($year), intval($month), $d);
                                $isSunday = $dateObj->isSunday();
                            @endphp
                            <th class="{{ $isSunday ? 'bg-light' : '' }}" style="min-width: 36px;">
                                <div style="font-size: 12px;">{{ $d }}</div>
                                <div class="day-header {{ $isSunday ? 'sun' : '' }}">{{ $dateObj->format('D') }}</div>
                            </th>
                        @endfor
                        <th class="status-column" style="min-width: 44px;"><i class="bx bx-check text-success" style="font-size: 18px;"></i></th>
                        <th class="status-column" style="min-width: 44px;"><i class="bx bx-x text-danger" style="font-size: 18px;"></i></th>
                        <th class="status-column" style="min-width: 44px;"><i class="bx bx-bookmark text-primary" style="font-size: 18px;"></i></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        @php
                            $userAtt = $attendances->get($user->id, collect())->keyBy(function($a) { return (int) substr($a->date, 8, 2); });
                            $presentCount = 0;
                            $absentCount = 0;
                            $leaveCount = 0;
                        @endphp
                        <tr>
                            <td class="emp-cell">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <span class="fw-bold d-block text-dark">{{ $user->full_name }}</span>
                                        <small class="text-muted" style="font-size: 11px;">{{ $user->roles->first()?->name ?? 'N/A' }}</small>
                                    </div>
                                    <button type="button" class="btn mark-btn btn-sm" title="Mark Attendance"
                                        onclick="openMarkModal({{ $user->id }}, '{{ $user->full_name }}')">
                                        <i class="bx bx-edit" style="font-size: 14px;"></i>
                                    </button>
                                </div>
                            </td>
                            <td><span style="font-size: 12px; color: #5d6778;">{{ $user->company?->name ?? '-' }}</span></td>
                            @for($d = 1; $d <= $daysInMonth; $d++)
                                @php
                                    $dateObj = Carbon\Carbon::create(intval($year), intval($month), $d);
                                    $isSunday = $dateObj->isSunday();
                                    $att = $userAtt->get($d);
                                    $status = $att?->status ?? ($isSunday ? 'sunday' : 'unmarked');
                                    $cssClass = match($status) {
                                        'present' => 'att-present',
                                        'absent' => 'att-absent',
                                        'half-day' => 'att-half-day',
                                        'leave' => 'att-leave',
                                        'unmarked' => '',
                                        default => 'att-sunday',
                                    };
                                    $label = match($status) {
                                        'present' => 'P',
                                        'absent' => 'A',
                                        'half-day' => 'HD',
                                        'leave' => 'L',
                                        'unmarked' => '',
                                        default => '-',
                                    };

                                    if ($att && $att->status === 'present') {
                                        $presentCount++;
                                    } elseif ($att && $att->status === 'half-day') {
                                        $presentCount += 0.5;
                                        $absentCount += 0.5;
                                    } elseif (!$isSunday && (!$att || $att->status === 'absent')) {
                                        $absentCount++;
                                    } elseif ($att && $att->status === 'leave') {
                                        $leaveCount++;
                                    }
                                @endphp
                                <td>
                                    <span class="att-badge {{ $cssClass }}" title="{{ $dateObj->format('d M Y') }}">{{ $label }}</span>
                                </td>
                            @endfor
                            <td class="status-column"><span class="summary-num text-success">{{ $presentCount }}</span></td>
                            <td class="status-column"><span class="summary-num text-danger">{{ $absentCount }}</span></td>
                            <td class="status-column"><span class="summary-num text-primary">{{ $leaveCount }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ $daysInMonth + 6 }}" class="text-center py-5 text-muted">No employees found for the selected period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="markAttendanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow-lg border-0" style="border-radius: 16px;">
            <div class="modal-header border-bottom-0 pb-0" style="border: none;">
                <div>
                    <h5 class="modal-title fw-bold text-dark">Mark Attendance</h5>
                    <p class="text-muted small mb-0" id="markModalEmployee" style="font-size: 13px;">-</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <hr class="my-2 mx-3">
            <div class="modal-body pt-0">
                <form method="POST" action="{{ route('admin.attendance.mark') }}">
                    @csrf
                    <input type="hidden" name="user_id" id="mark_user_id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Date</label>
                        <input type="date" max="9999-12-31" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Status</label>
                        <div class="d-flex gap-2" id="statusOptions">
                            <label class="btn btn-outline-success btn-sm rounded-pill px-3" data-value="present">
                                <input type="radio" name="status" value="present" class="d-none"> Present
                            </label>
                            <label class="btn btn-outline-danger btn-sm rounded-pill px-3" data-value="absent">
                                <input type="radio" name="status" value="absent" class="d-none"> Absent
                            </label>
                            <label class="btn btn-outline-warning btn-sm rounded-pill px-3" data-value="half-day">
                                <input type="radio" name="status" value="half-day" class="d-none"> Half Day
                            </label>
                            <label class="btn btn-outline-primary btn-sm rounded-pill px-3" data-value="leave">
                                <input type="radio" name="status" value="leave" class="d-none"> Leave
                            </label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Remarks</label>
                        <input type="text" name="remarks" class="form-control form-control-sm" placeholder="Optional">
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4"><i class="bx bx-save me-1"></i> Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
function openMarkModal(userId, userName) {
    document.getElementById('mark_user_id').value = userId;
    document.getElementById('markModalEmployee').innerText = userName;
    new bootstrap.Modal(document.getElementById('markAttendanceModal')).show();
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('#statusOptions .btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('#statusOptions .btn').forEach(function(b) {
                b.classList.remove('active');
            });
            this.classList.add('active');
            var radio = this.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
        });
    });
});
</script>
@endsection