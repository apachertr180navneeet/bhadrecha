@extends('admin.layouts.app')

@section('style')
<style>
    /* Premium visual styles for Employee View Detail */
    .profile-banner-card {
        border: none;
        border-radius: 16px;
        background: linear-gradient(135deg, #696cff 0%, #4f46e5 100%);
        color: #ffffff;
        overflow: hidden;
        position: relative;
        box-shadow: 0 8px 24px rgba(105, 108, 255, 0.25);
    }
    .profile-banner-card::before {
        content: '';
        position: absolute;
        width: 300px;
        height: 300px;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 50%;
        top: -100px;
        right: -100px;
    }
    .profile-avatar-large {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 32px;
        text-transform: uppercase;
        border: 4px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        background-color: rgba(255, 255, 255, 0.9);
        color: #696cff;
    }
    .salary-summary-card {
        border: none;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease;
    }
    .salary-summary-card:hover {
        transform: translateY(-3px);
    }
    .category-card {
        border: none;
        border-radius: 12px;
        background-color: #ffffff;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.03);
    }
    .list-group-item-custom {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border: none;
        border-bottom: 1px solid #f1f1f4;
    }
    .list-group-item-custom:last-child {
        border-bottom: none;
    }
    .progress-bar-glow {
        box-shadow: 0 0 10px rgba(105, 108, 255, 0.3);
    }
    .net-payable-badge {
        background: rgba(105, 108, 255, 0.08);
        color: #696cff;
        padding: 10px 20px;
        border-radius: 12px;
        font-weight: 700;
        display: inline-block;
        font-size: 1.1rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <!-- Breadcrumbs and Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1">Employee Salary Breakdown</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.employee-salary.employees-list') }}">Employee Salary</a></li>
                    <li class="breadcrumb-item active">{{ $employee->full_name }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            @if(auth()->user()->isSuperAdmin() || auth()->user()->isCompanyAdmin())
            <a href="{{ route('admin.employee-salary.employees-list') }}" class="btn btn-outline-secondary">
                <i class="bx bx-chevron-left me-1"></i> Back to Directory
            </a>
            <a href="{{ route('admin.employee-salary.revisions', $employee->id) }}" class="btn btn-info text-white">
                <i class="bx bx-history me-1"></i> Revisions
            </a>
            <a href="{{ route('admin.employee-salary.edit-structure', $employee->id) }}" class="btn btn-primary">
                <i class="bx bx-edit me-1"></i> Edit Structure
            </a>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#incentiveModal">
                <i class="bx bx-gift me-1"></i> Incentive
            </button>
            @else
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                <i class="bx bx-chevron-left me-1"></i> Back to Dashboard
            </a>
            @endif
        </div>
    </div>

    <!-- Main Grid -->
    <div class="row">
        <!-- Left Column: Employee Profile Details & Overview -->
        <div class="col-lg-4 mb-4">
            <!-- Profile Banner Card -->
            <div class="card profile-banner-card mb-4">
                <div class="card-body py-5 text-center">
                    <div class="d-flex justify-content-center mb-3">
                        <div class="profile-avatar-large">
                            {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                        </div>
                    </div>
                    <h4 class="fw-bold text-white mb-1">{{ $employee->full_name }}</h4>
                    <span class="badge bg-label-light text-white mb-3" style="background-color: rgba(255,255,255,0.15)">
                        {{ $role ? $role->name : 'Staff Member' }}
                    </span>
                    <p class="text-white opacity-75 mb-0 small">
                        <i class="bx bx-hash me-1"></i> EMP-{{ str_pad($employee->id, 4, '0', STR_PAD_LEFT) }}
                    </p>
                </div>
            </div>

            <!-- Profile Info Card -->
            <div class="card category-card shadow-sm mb-4">
                <div class="card-header border-bottom bg-transparent py-3">
                    <h5 class="mb-0 fw-bold"><i class="bx bx-user-circle text-primary me-2"></i>Personal Profile</h5>
                </div>
                <div class="card-body pt-3">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item-custom">
                            <span class="text-muted"><i class="bx bx-envelope me-2"></i>Email</span>
                            <span class="fw-semibold text-dark">{{ $employee->email }}</span>
                        </div>
                        <div class="list-group-item-custom">
                            <span class="text-muted"><i class="bx bx-phone me-2"></i>Phone</span>
                            <span class="fw-semibold text-dark">{{ $employee->phone ?? '-' }}</span>
                        </div>
                        <div class="list-group-item-custom">
                            <span class="text-muted"><i class="bx bx-buildings me-2"></i>Company</span>
                            <span class="badge bg-label-info">{{ $employee->company?->name ?? '-' }}</span>
                        </div>
                        <div class="list-group-item-custom">
                            <span class="text-muted"><i class="bx bx-git-branch me-2"></i>Branch</span>
                            <span class="badge bg-label-secondary">{{ $employee->branch?->name ?? '-' }}</span>
                        </div>
                        <div class="list-group-item-custom">
                            <span class="text-muted"><i class="bx bx-calendar me-2"></i>Joining Date</span>
                            <span class="fw-semibold text-dark">{{ $employee->created_at->format('Y-m-d') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Salary Structure Details -->
        <div class="col-lg-8 mb-4">
            <!-- Salary Summary Card -->
            <div class="card salary-summary-card shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                        <div>
                            <span class="text-muted d-block mb-1">Monthly Net Payout</span>
                            @php
                                $base = floatval($employee->salary?->base_salary ?? 0);
                                $hra = floatval($employee->salary?->hra ?? 0);
                                $da = floatval($employee->salary?->da ?? 0);
                                $special = floatval($employee->salary?->special_allowance ?? 0);
                                $allowances = $hra + $da + $special;
                                $pf = floatval($employee->salary?->pf ?? 0);
                                $esi = floatval($employee->salary?->esi ?? 0);
                                $pt = floatval($employee->salary?->professional_tax ?? 0);
                                $tds = floatval($employee->salary?->tds ?? 0);
                                $deductions = $pf + $esi + $pt + $tds;
                                $net = $base + $allowances - $deductions;
                            @endphp
                            <h2 class="text-primary fw-bold mb-0">₹{{ number_format($net, 2) }}</h2>
                        </div>
                        <div class="text-sm-end">
                            <span class="net-payable-badge">
                                <i class="bx bx-wallet me-1"></i> Net Payable Computed
                            </span>
                        </div>
                    </div>

                    <!-- Progress split visualizer -->
                    <div class="mt-4">
                        <div class="d-flex justify-content-between mb-2 small text-muted">
                            <span>Base & Earnings</span>
                            <span>Deductions</span>
                        </div>
                        <div class="progress" style="height: 12px; border-radius: 30px;">
                            @php
                                $totalComp = ($base + $allowances) ?: 1;
                                $earningsPct = round((($base + $allowances) / ($totalComp + $deductions)) * 100);
                                $deductionsPct = 100 - $earningsPct;
                            @endphp
                            <div class="progress-bar bg-primary progress-bar-glow" role="progressbar" style="width: {{ $earningsPct }}%" aria-valuenow="{{ $earningsPct }}" aria-valuemin="0" aria-valuemax="100"></div>
                            <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $deductionsPct }}%" aria-valuenow="{{ $deductionsPct }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-2 small">
                            <span class="text-primary fw-semibold">{{ $earningsPct }}% (₹{{ number_format($base + $allowances, 2) }})</span>
                            <span class="text-danger fw-semibold">{{ $deductionsPct }}% (₹{{ number_format($deductions, 2) }})</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Breakdown Grids -->
            <div class="row">
                <!-- Allowances / Earnings Panel -->
                <div class="col-md-6 mb-4">
                    <div class="card category-card h-100 shadow-sm">
                        <div class="card-header border-bottom bg-transparent py-3">
                            <h5 class="mb-0 fw-bold text-success"><i class="bx bx-plus-circle me-2"></i>Earnings & Allowances</h5>
                        </div>
                        <div class="card-body pt-3">
                            <div class="list-group list-group-flush">
                                <div class="list-group-item-custom">
                                    <span class="text-muted fw-semibold">Base Salary</span>
                                    <span class="fw-bold text-dark">₹{{ number_format($base, 2) }}</span>
                                </div>
                                <div class="list-group-item-custom">
                                    <span class="text-muted">House Rent Allowance (HRA)</span>
                                    <span class="fw-semibold text-success">+ ₹{{ number_format($hra, 2) }}</span>
                                </div>
                                <div class="list-group-item-custom">
                                    <span class="text-muted">Dearness Allowance (DA)</span>
                                    <span class="fw-semibold text-success">+ ₹{{ number_format($da, 2) }}</span>
                                </div>
                                <div class="list-group-item-custom">
                                    <span class="text-muted">Special / Bonus Allowance</span>
                                    <span class="fw-semibold text-success">+ ₹{{ number_format($special, 2) }}</span>
                                </div>
                                <div class="list-group-item-custom border-top pt-3 mt-2">
                                    <span class="fw-bold text-dark">Gross Earnings</span>
                                    <span class="fw-bold text-success" style="font-size: 16px;">₹{{ number_format($base + $allowances, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Deductions Panel -->
                <div class="col-md-6 mb-4">
                    <div class="card category-card h-100 shadow-sm">
                        <div class="card-header border-bottom bg-transparent py-3">
                            <h5 class="mb-0 fw-bold text-danger"><i class="bx bx-minus-circle me-2"></i>Deductions & Holdings</h5>
                        </div>
                        <div class="card-body pt-3">
                            <div class="list-group list-group-flush">
                                <div class="list-group-item-custom">
                                    <span class="text-muted">Provident Fund (PF)</span>
                                    <span class="fw-semibold text-danger">- ₹{{ number_format($pf, 2) }}</span>
                                </div>
                                <div class="list-group-item-custom">
                                    <span class="text-muted">Employee State Insurance (ESI)</span>
                                    <span class="fw-semibold text-danger">- ₹{{ number_format($esi, 2) }}</span>
                                </div>
                                <div class="list-group-item-custom">
                                    <span class="text-muted">Professional Tax (PT)</span>
                                    <span class="fw-semibold text-danger">- ₹{{ number_format($pt, 2) }}</span>
                                </div>
                                <div class="list-group-item-custom">
                                    <span class="text-muted">Tax Deducted at Source (TDS)</span>
                                    <span class="fw-semibold text-danger">- ₹{{ number_format($tds, 2) }}</span>
                                </div>
                                <div class="list-group-item-custom border-top pt-3 mt-2">
                                    <span class="fw-bold text-dark">Total Deductions</span>
                                    <span class="fw-bold text-danger" style="font-size: 16px;">₹{{ number_format($deductions, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Salary Slips History -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-transparent border-bottom py-3">
        <h5 class="mb-0 fw-bold"><i class="bx bx-receipt text-primary me-2"></i>Salary Slips</h5>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="py-3">Month</th>
                    <th class="py-3 text-center">Working Days</th>
                    <th class="py-3 text-center">Attended</th>
                    <th class="py-3 text-end">Base Salary</th>
                    <th class="py-3 text-end">Per Day Rate</th>
                    <th class="py-3 text-end">Att. Salary</th>
                    <th class="py-3 text-end">Allowances</th>
                    <th class="py-3 text-end">Deductions</th>
                    <th class="py-3 text-end">Incentives</th>
                    <th class="py-3 text-end">Advance Deducted</th>
                    <th class="py-3 text-end">Net Payable</th>
                    <th class="py-3 text-center">Status</th>
                    <th class="py-3">Processed On</th>
                </tr>
            </thead>
            <tbody>
                @forelse($salaryPayments as $sp)
                @php
                    $monthName = Carbon\Carbon::create()->month($sp->month)->format('F');
                @endphp
                <tr>
                    <td class="fw-semibold">{{ $monthName }} {{ $sp->year }}</td>
                    <td class="text-center">{{ $sp->working_days }}</td>
                    <td class="text-center"><span class="{{ $sp->attended_days >= $sp->working_days ? 'text-success' : 'text-warning' }} fw-semibold">{{ $sp->attended_days }}</span></td>
                    <td class="text-end">₹{{ number_format($sp->base_salary, 2) }}</td>
                    <td class="text-end text-muted">₹{{ number_format($sp->per_day_rate, 2) }}</td>
                    <td class="text-end text-primary fw-semibold">₹{{ number_format($sp->attendance_salary, 2) }}</td>
                    <td class="text-end text-success">+ ₹{{ number_format($sp->allowances, 2) }}</td>
                    <td class="text-end text-danger">- ₹{{ number_format($sp->deductions, 2) }}</td>
                    <td class="text-end text-warning">+ ₹{{ number_format($sp->incentives_total, 2) }}</td>
                    <td class="text-end text-info">- ₹{{ number_format($sp->advance_deduction, 2) }}</td>
                    <td class="text-end fw-bold text-dark">₹{{ number_format($sp->net_payable, 2) }}</td>
                    <td class="text-center"><span class="badge bg-success">Paid</span></td>
                    <td><small class="text-muted">{{ $sp->processed_at ? $sp->processed_at->format('d M Y, h:i A') : '-' }}</small></td>
                </tr>
                @empty
                <tr><td colspan="13" class="text-center py-4 text-muted">No salary slips found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if(auth()->user()->isSuperAdmin() || auth()->user()->isCompanyAdmin())
<div class="modal fade" id="incentiveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header border-bottom-0 pb-0">
                <div>
                    <h5 class="modal-title fw-bold text-dark">Add Incentive</h5>
                    <p class="text-muted small mb-0">{{ $employee->full_name }} • EMP-{{ str_pad($employee->id, 4, '0', STR_PAD_LEFT) }}</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <hr class="my-3">
            <div class="modal-body pt-0">
                <form method="POST" action="{{ route('admin.employee-salary.store-incentive', $employee->id) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark" for="incentive_amount">Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" id="incentive_amount" name="amount" class="form-control" placeholder="Enter amount" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark" for="incentive_reason">Reason</label>
                        <input type="text" id="incentive_reason" name="reason" class="form-control" placeholder="e.g. Performance bonus, Festival bonus">
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success"><i class="bx bx-gift me-1"></i> Add Incentive</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
