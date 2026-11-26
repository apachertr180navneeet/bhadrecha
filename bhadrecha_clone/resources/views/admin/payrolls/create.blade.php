@extends('admin.layouts.app')

@section('style')
<style>
    .stat-badge {
        padding: 8px 12px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 0.85rem;
        font-weight: 600;
    }
    .badge-present { background-color: rgba(40, 167, 69, 0.12); color: #28a745; border: 1px solid rgba(40, 167, 69, 0.25); }
    .badge-absent { background-color: rgba(220, 53, 69, 0.12); color: #dc3545; border: 1px solid rgba(220, 53, 69, 0.25); }
    .badge-leave { background-color: rgba(255, 193, 7, 0.15); color: #b8860b; border: 1px solid rgba(255, 193, 7, 0.3); }
    .badge-halfday { background-color: rgba(111, 66, 193, 0.12); color: #6f42c1; border: 1px solid rgba(111, 66, 193, 0.25); }
    .badge-late { background-color: rgba(253, 126, 20, 0.12); color: #fd7e14; border: 1px solid rgba(253, 126, 20, 0.25); }
    .badge-holiday { background-color: rgba(13, 202, 240, 0.12); color: #0dcaf0; border: 1px solid rgba(13, 202, 240, 0.25); }

    .card-body.has-open-dropdown,
    .card.has-open-dropdown,
    .row.has-open-dropdown {
        position: relative !important;
        z-index: 9999 !important;
        overflow: visible !important;
    }
    .custom-ajax-wrapper.has-open-dropdown {
        position: relative !important;
        z-index: 9999 !important;
    }
    .custom-ajax-wrapper { position: relative; }
    .custom-ajax-menu {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 99999 !important;
        background: #ffffff !important;
        border: 1px solid rgba(197, 160, 89, 0.4);
        border-radius: 12px;
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.25) !important;
        max-height: 230px;
        overflow-y: auto;
        display: none;
    }
    .custom-ajax-menu .dropdown-item {
        padding: 8px 14px;
        cursor: pointer;
        border-bottom: 1px solid #f8f9fa;
        white-space: normal;
        background: #ffffff !important;
    }
    .custom-ajax-menu .dropdown-item:hover {
        background-color: rgba(197, 160, 89, 0.15) !important;
        color: #9e7d3b;
    }
</style>
@endsection

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bx bx-calculator me-2 text-primary"></i>Create Payroll</h4>
            <p class="text-muted mb-0">Select staff, month, and year to auto-calculate salary, attendance, and advances</p>
        </div>
        <a href="{{ route('admin.payrolls.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Back to List
        </a>
    </div>

    <form method="POST" action="{{ route('admin.payrolls.store') }}" id="payrollForm">
        @csrf
        <div class="row">
            <!-- Left Column: Payroll Form Inputs -->
            <div class="col-lg-7 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-light py-3 border-bottom">
                        <h6 class="mb-0 fw-bold"><i class="bx bx-user me-1 text-primary"></i> Payroll Information</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Employee / Staff <span class="text-danger">*</span></label>
                                @php
                                    $oldStaff = old('staff_id') ? $staffs->firstWhere('id', old('staff_id')) : null;
                                    $oldStaffText = $oldStaff ? ($oldStaff->full_name . ' (' . ($oldStaff->position ?? 'Staff') . ')') : '';
                                @endphp
                                <div class="custom-ajax-wrapper" id="staffPickerWrapper">
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="bx bx-user text-muted"></i></span>
                                        <input type="text" class="form-control @error('staff_id') is-invalid @enderror" id="staff_search_input" placeholder="Type name or phone to search employee..." value="{{ $oldStaffText }}" autocomplete="off" required>
                                        <button type="button" class="btn btn-outline-secondary" id="clear_staff_btn" style="display: {{ old('staff_id') ? 'inline-block' : 'none' }};"><i class="bx bx-x"></i></button>
                                    </div>
                                    <input type="hidden" name="staff_id" id="staff_id" value="{{ old('staff_id') }}" required>
                                    <div class="custom-ajax-menu" id="staff_results_menu"></div>
                                </div>
                                @error('staff_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Payroll Month <span class="text-danger">*</span></label>
                                <select name="month" id="month" class="form-select @error('month') is-invalid @enderror" required>
                                    @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ old('month', date('n')) == $m ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('month') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Payroll Year <span class="text-danger">*</span></label>
                                <select name="year" id="year" class="form-select @error('year') is-invalid @enderror" required>
                                    @foreach(range(date('Y') + 1, date('Y') - 3) as $y)
                                    <option value="{{ $y }}" {{ old('year', date('Y')) == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('year') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <hr class="my-2">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Basic Monthly Salary (₹) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" step="0.01" min="0" name="basic_salary" id="basic_salary" class="form-control @error('basic_salary') is-invalid @enderror" value="{{ old('basic_salary', '0.00') }}" required>
                                </div>
                                <small class="text-muted" id="perDaySalaryHint">Per day rate: ₹0.00</small>
                                @error('basic_salary') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-danger">Attendance Deductions (₹)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-danger">₹</span>
                                    <input type="number" step="0.01" min="0" name="deductions" id="deductions" class="form-control text-danger @error('deductions') is-invalid @enderror" value="{{ old('deductions', '0.00') }}">
                                </div>
                                <small class="text-muted">Calculated based on absent/leave days</small>
                                @error('deductions') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-warning">Salary Advance Deduction (₹)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-warning">₹</span>
                                    <input type="number" step="0.01" min="0" name="advance_amount" id="advance_amount" class="form-control text-warning @error('advance_amount') is-invalid @enderror" value="{{ old('advance_amount', '0.00') }}">
                                </div>
                                <small class="text-muted">Advances to deduct in this month</small>
                                @error('advance_amount') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-success">Net Payable Salary (₹)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-success text-white">₹</span>
                                    <input type="number" step="0.01" min="0" id="net_amount" class="form-control fw-bold fs-5 text-success bg-light" value="0.00" readonly>
                                </div>
                                <small class="text-muted">Basic - Attendance Deductions - Advances</small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('admin.payrolls.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4"><i class="bx bx-check me-1"></i> Create Payroll</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Live Attendance & Advance Breakdown -->
            <div class="col-lg-5 mb-4">
                <!-- Attendance Summary Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center border-bottom">
                        <h6 class="mb-0 fw-bold"><i class="bx bx-calendar-check me-1 text-success"></i> Attendance Breakdown</h6>
                        <span class="badge bg-primary" id="monthYearBadge">Current Month</span>
                    </div>
                    <div class="card-body p-3">
                        <div id="attendanceLoading" class="text-center py-4 d-none">
                            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                            <span class="ms-2 text-muted small">Loading attendance...</span>
                        </div>

                        <div id="attendanceEmptyState" class="text-center py-4 text-muted small">
                            <i class="bx bx-user-check fs-2 mb-2 d-block opacity-50"></i>
                            Select an employee to view attendance summary.
                        </div>

                        <div id="attendanceDetails" class="d-none">
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <div class="stat-badge badge-present">
                                        <span><i class="bx bx-check-circle me-1"></i> Present (P)</span>
                                        <span id="countPresent" class="fs-6">0</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-badge badge-absent">
                                        <span><i class="bx bx-x-circle me-1"></i> Absent (A)</span>
                                        <span id="countAbsent" class="fs-6">0</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-badge badge-leave">
                                        <span><i class="bx bx-calendar-x me-1"></i> Leave (L)</span>
                                        <span id="countLeave" class="fs-6">0</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-badge badge-halfday">
                                        <span><i class="bx bx-adjust me-1"></i> Half Day (HD)</span>
                                        <span id="countHalfDay" class="fs-6">0</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-badge badge-late">
                                        <span><i class="bx bx-time-five me-1"></i> Late</span>
                                        <span id="countLate" class="fs-6">0</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-badge badge-holiday">
                                        <span><i class="bx bx-gift me-1"></i> Holiday (H)</span>
                                        <span id="countHoliday" class="fs-6">0</span>
                                    </div>
                                </div>
                            </div>
                            <div class="p-3 bg-light rounded border text-muted small">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Days in Month:</span>
                                    <strong class="text-dark" id="daysInMonthDisplay">0</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Total Payable Working Days:</span>
                                    <strong class="text-success" id="payableDaysDisplay">0 days</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Advances Breakdown Card -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center border-bottom">
                        <h6 class="mb-0 fw-bold"><i class="bx bx-wallet me-1 text-warning"></i> Salary Advances Due</h6>
                        <span class="badge bg-warning text-dark" id="advanceTotalBadge">₹0.00</span>
                    </div>
                    <div class="card-body p-3">
                        <div id="advanceLoading" class="text-center py-4 d-none">
                            <div class="spinner-border spinner-border-sm text-warning" role="status"></div>
                            <span class="ms-2 text-muted small">Checking advances...</span>
                        </div>

                        <div id="advanceEmptyState" class="text-center py-4 text-muted small">
                            <i class="bx bx-money fs-2 mb-2 d-block opacity-50"></i>
                            Select an employee to check pending advances.
                        </div>

                        <div id="advanceListContainer" class="d-none">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered align-middle mb-0 small">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Amount (₹)</th>
                                            <th>Reason / Notes</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="advancesTableBody">
                                        <!-- Dynamic advance rows -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div id="noAdvancesMessage" class="alert alert-success d-none py-2 px-3 small mb-0 mt-2">
                            <i class="bx bx-check-circle me-1"></i> No pending salary advance for this month.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function () {
        const staffHiddenInput = $('#staff_id');
        const staffSearchInput = $('#staff_search_input');
        const staffResultsMenu = $('#staff_results_menu');
        const clearStaffBtn = $('#clear_staff_btn');
        const monthSelect = $('#month');
        const yearSelect = $('#year');
        const basicSalaryInput = $('#basic_salary');
        const deductionsInput = $('#deductions');
        const advanceInput = $('#advance_amount');
        const netAmountInput = $('#net_amount');

        const staffsMap = {
            @foreach($staffs as $s)
                {{ $s->id }}: {
                    name: "{{ addslashes($s->full_name) }}",
                    position: "{{ addslashes($s->position ?? 'Staff') }}",
                    phone: "{{ addslashes($s->phone ?? '') }}",
                    salary: {{ (float)($s->salary ?? 0) }}
                },
            @endforeach
        };

        function showCustomMenu(menu) {
            if (!menu || typeof menu.show !== 'function') return;
            $('.custom-ajax-menu').not(menu).hide();
            $('.has-open-dropdown').removeClass('has-open-dropdown');
            
            menu.show();
            menu.parents('.custom-ajax-wrapper, .table-responsive, .card-body, .card, .row').addClass('has-open-dropdown');
        }

        function hideCustomMenu() {
            $('.custom-ajax-menu').hide();
            $('.has-open-dropdown').removeClass('has-open-dropdown');
        }

        $(document).on('click', function(e) {
            if (!$(e.target).closest('.custom-ajax-wrapper').length) {
                hideCustomMenu();
            }
        });

        $(document).on('click', '.custom-ajax-menu', function(e) {
            e.stopPropagation();
        });

        // Staff Picker Setup
        let staffSearchTimer;
        function searchStaff(q) {
            $.ajax({
                url: '{{ route("admin.advances.search-staffs") }}',
                method: 'GET',
                data: { q: q },
                success: function(data) {
                    staffResultsMenu.empty();
                    const list = Array.isArray(data) ? data : (data.results || []);
                    if (list.length === 0) {
                        staffResultsMenu.append('<div class="p-3 text-muted text-center small">No employees found</div>');
                    } else {
                        list.forEach(function(st) {
                            const pos = st.position ? `<small class="text-muted d-block"><i class="bx bx-briefcase me-1"></i>${st.position}</small>` : '';
                            const ph = st.phone ? `<small class="text-muted d-block"><i class="bx bx-phone me-1"></i>${st.phone}</small>` : '';
                            staffResultsMenu.append(`
                                <div class="dropdown-item select-staff-item" data-id="${st.id}" data-name="${st.name}" data-position="${st.position || ''}" data-salary="${st.salary || 0}">
                                    <div class="fw-bold">${st.name}</div>
                                    ${pos}
                                    ${ph}
                                </div>
                            `);
                        });
                    }
                    showCustomMenu(staffResultsMenu);
                }
            });
        }

        staffSearchInput.on('focus input', function(e) {
            e.stopPropagation();
            clearTimeout(staffSearchTimer);
            const q = $(this).val();
            staffSearchTimer = setTimeout(function() {
                searchStaff(q);
            }, 150);
        });

        staffResultsMenu.on('click', '.select-staff-item', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const id = $(this).data('id');
            const name = $(this).data('name');
            const pos = $(this).data('position');
            const salary = parseFloat($(this).data('salary')) || (staffsMap[id] ? staffsMap[id].salary : 0) || 0;
            const displayText = pos ? `${name} (${pos})` : name;

            staffHiddenInput.val(id);
            staffSearchInput.val(displayText);
            clearStaffBtn.show();
            hideCustomMenu();

            basicSalaryInput.val(salary.toFixed(2));
            deductionsInput.removeData('user-modified');
            advanceInput.removeData('user-modified');
            calculatePayroll(true);
        });

        clearStaffBtn.on('click', function(e) {
            e.preventDefault();
            staffHiddenInput.val('');
            staffSearchInput.val('');
            $(this).hide();
            hideCustomMenu();
            calculatePayroll(true);
        });

        function updateNetAmount() {
            const basic = parseFloat(basicSalaryInput.val()) || 0;
            const deductions = parseFloat(deductionsInput.val()) || 0;
            const advance = parseFloat(advanceInput.val()) || 0;
            const net = Math.max(0, basic - deductions - advance);
            netAmountInput.val(net.toFixed(2));
        }

        let calcTimer;
        function calculatePayroll(isStaffChange = false) {
            const staffId = staffHiddenInput.val();
            const month = monthSelect.val();
            const year = yearSelect.val();

            const monthName = monthSelect.find('option:selected').text().trim();
            $('#monthYearBadge').text(`${monthName} ${year}`);

            if (!staffId) {
                $('#attendanceEmptyState').removeClass('d-none');
                $('#attendanceDetails').addClass('d-none');
                $('#advanceEmptyState').removeClass('d-none');
                $('#advanceListContainer').addClass('d-none');
                $('#noAdvancesMessage').addClass('d-none');
                $('#advanceTotalBadge').text('₹0.00');
                $('#perDaySalaryHint').text('Per day rate: ₹0.00');
                if (isStaffChange) {
                    basicSalaryInput.val('0.00');
                    deductionsInput.val('0.00');
                    advanceInput.val('0.00');
                    updateNetAmount();
                }
                return;
            }

            const basicSalary = parseFloat(basicSalaryInput.val()) || 0;

            $('#attendanceEmptyState').addClass('d-none');
            $('#attendanceLoading').removeClass('d-none');
            $('#advanceEmptyState').addClass('d-none');
            $('#advanceLoading').removeClass('d-none');

            clearTimeout(calcTimer);
            calcTimer = setTimeout(function () {
                $.ajax({
                    url: '{{ route("admin.payrolls.calculate") }}',
                    method: 'GET',
                    data: {
                        staff_id: staffId,
                        month: month,
                        year: year,
                        basic_salary: basicSalary
                    },
                    success: function (data) {
                        $('#attendanceLoading').addClass('d-none');
                        $('#advanceLoading').addClass('d-none');

                        // Attendance Summary
                        if (data.attendance_summary) {
                            $('#attendanceDetails').removeClass('d-none');
                            $('#countPresent').text(data.attendance_summary.present);
                            $('#countAbsent').text(data.attendance_summary.absent);
                            $('#countLeave').text(data.attendance_summary.leave);
                            $('#countHalfDay').text(data.attendance_summary.half_day);
                            $('#countLate').text(data.attendance_summary.late);
                            $('#countHoliday').text(data.attendance_summary.holiday);
                            $('#daysInMonthDisplay').text(data.attendance_summary.days_in_month);
                            $('#payableDaysDisplay').text(`${data.attendance_summary.total_payable_days} days`);
                        }

                        // Per Day Rate
                        if (data.per_day_salary) {
                            $('#perDaySalaryHint').text(`Per day rate: ₹${parseFloat(data.per_day_salary).toFixed(2)}`);
                        }

                        // Advances Summary & List
                        const advances = data.advances || [];
                        const advanceTotal = parseFloat(data.advance_amount) || 0;
                        $('#advanceTotalBadge').text(`₹${advanceTotal.toFixed(2)}`);

                        if (advances.length > 0) {
                            $('#noAdvancesMessage').addClass('d-none');
                            $('#advanceListContainer').removeClass('d-none');
                            const tbody = $('#advancesTableBody');
                            tbody.empty();
                            advances.forEach(function (adv) {
                                tbody.append(`
                                    <tr>
                                        <td>${adv.date}</td>
                                        <td class="fw-bold text-danger">₹${adv.amount.toFixed(2)}</td>
                                        <td>${adv.reason}</td>
                                        <td><span class="badge bg-label-warning">${adv.status}</span></td>
                                    </tr>
                                `);
                            });
                        } else {
                            $('#advanceListContainer').addClass('d-none');
                            $('#noAdvancesMessage').removeClass('d-none');
                        }

                        // Update Inputs
                        if (isStaffChange || !deductionsInput.data('user-modified')) {
                            deductionsInput.val(parseFloat(data.deductions || 0).toFixed(2));
                        }
                        if (isStaffChange || !advanceInput.data('user-modified')) {
                            advanceInput.val(advanceTotal.toFixed(2));
                        }

                        updateNetAmount();
                    },
                    error: function () {
                        $('#attendanceLoading').addClass('d-none');
                        $('#advanceLoading').addClass('d-none');
                    }
                });
            }, 150);
        }

        monthSelect.on('change', function () { calculatePayroll(false); });
        yearSelect.on('change', function () { calculatePayroll(false); });

        basicSalaryInput.on('input change', function () {
            calculatePayroll(false);
        });

        deductionsInput.on('input change', function () {
            $(this).data('user-modified', true);
            updateNetAmount();
        });

        advanceInput.on('input change', function () {
            $(this).data('user-modified', true);
            updateNetAmount();
        });

        // Trigger on initial page load if staff is pre-selected
        if (staffHiddenInput.val()) {
            calculatePayroll(false);
        }
    });
</script>
@endsection
