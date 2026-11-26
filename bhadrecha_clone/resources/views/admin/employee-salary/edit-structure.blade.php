@extends('admin.layouts.app')

@section('style')
<style>
    /* Premium visual styles for Edit Salary Structure */
    .edit-card-header {
        border-bottom: 1px solid #f1f1f4;
        background-color: transparent;
    }
    .live-summary-card {
        border: none;
        border-radius: 16px;
        background: #f8f9fa;
        border: 2px dashed #696cff;
        position: sticky;
        top: 24px;
    }
    .calculator-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #696cff;
    }
    .calc-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #eef0f2;
    }
    .calc-row:last-child {
        border-bottom: none;
    }
    .net-payout-box {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(105, 108, 255, 0.08);
        padding: 15px;
        margin-top: 15px;
        text-align: center;
        border: 1px solid rgba(105, 108, 255, 0.15);
    }
    .input-group-text-custom {
        background-color: rgba(105, 108, 255, 0.05);
        color: #696cff;
        font-weight: 600;
    }
</style>
@endsection

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <!-- Breadcrumbs and Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1">Edit Salary Structure</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.employee-salary.employees-list') }}">Employee Salary</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.employee-salary.details', $employee->id) }}">{{ $employee->full_name }}</a></li>
                    <li class="breadcrumb-item active">Edit Structure</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.employee-salary.details', $employee->id) }}" class="btn btn-outline-secondary">
                <i class="bx bx-x me-1"></i> Cancel
            </a>
        </div>
    </div>

    <!-- Error Alerts -->
    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="bx bx-error-circle me-2" style="font-size: 20px;"></i>
            <span class="fw-semibold">Please check the fields below:</span>
        </div>
        <ul class="mt-2 mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.employee-salary.update-structure', $employee->id) }}" id="salaryForm">
        @csrf
        <div class="row">
            <!-- Left Side: Structure Input Form -->
            <div class="col-lg-8">
                <!-- Employee Brief -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-initial bg-label-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 45px; height: 45px;">
                                {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold text-dark">{{ $employee->full_name }}</h6>
                                <small class="text-muted">{{ $role ? $role->name : 'Staff Member' }} • EMP-{{ str_pad($employee->id, 4, '0', STR_PAD_LEFT) }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Earnings and Allowances Panel -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header edit-card-header py-3">
                        <h5 class="mb-0 fw-bold text-success"><i class="bx bx-plus-circle me-2"></i>1. Earnings & Base Structure</h5>
                    </div>
                    <div class="card-body pt-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark" for="base_salary">Base Monthly Salary (₹) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text input-group-text-custom">₹</span>
                                <input type="number" id="base_salary" name="base_salary" class="form-control form-control-lg fw-bold calc-input" placeholder="e.g. 50000" value="{{ old('base_salary', $employee->salary?->base_salary ?? 0) }}" min="0" required>
                            </div>
                            <span class="form-text text-muted">The core monthly base pay before any allowances or deductions.</span>
                        </div>

                        <hr class="my-4">
                        <h6 class="fw-bold mb-3 text-secondary">Monthly Allowances</h6>

                        <div class="row g-3">
                            <!-- HRA -->
                            <div class="col-md-6">
                                <label class="form-label" for="allowance_hra">House Rent Allowance (HRA) (₹)</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" id="allowance_hra" name="hra" class="form-control calc-input text-success fw-medium" placeholder="0" value="{{ old('hra', $employee->salary?->hra ?? 0) }}" min="0">
                                </div>
                            </div>
                            <!-- DA -->
                            <div class="col-md-6">
                                <label class="form-label" for="allowance_da">Dearness Allowance (DA) (₹)</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" id="allowance_da" name="da" class="form-control calc-input text-success fw-medium" placeholder="0" value="{{ old('da', $employee->salary?->da ?? 0) }}" min="0">
                                </div>
                            </div>
                            <!-- Special Allowance -->
                            <div class="col-md-12">
                                <label class="form-label" for="allowance_special">Special / Conveyance Allowance (₹)</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" id="allowance_special" name="special_allowance" class="form-control calc-input text-success fw-medium" placeholder="0" value="{{ old('special_allowance', $employee->salary?->special_allowance ?? 0) }}" min="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Deductions and Holdings Panel -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header edit-card-header py-3">
                        <h5 class="mb-0 fw-bold text-danger"><i class="bx bx-minus-circle me-2"></i>2. Deductions & Statutory Holdings</h5>
                    </div>
                    <div class="card-body pt-4">
                        <div class="row g-3">
                            <!-- PF -->
                            <div class="col-md-6">
                                <label class="form-label" for="deduction_pf">Provident Fund (PF) Contribution (₹)</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" id="deduction_pf" name="pf" class="form-control calc-input text-danger fw-medium" placeholder="0" value="{{ old('pf', $employee->salary?->pf ?? 0) }}" min="0">
                                </div>
                            </div>
                            <!-- ESI -->
                            <div class="col-md-6">
                                <label class="form-label" for="deduction_esi">Employee State Insurance (ESI) (₹)</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" id="deduction_esi" name="esi" class="form-control calc-input text-danger fw-medium" placeholder="0" value="{{ old('esi', $employee->salary?->esi ?? 0) }}" min="0">
                                </div>
                            </div>
                            <!-- Professional Tax -->
                            <div class="col-md-6">
                                <label class="form-label" for="deduction_pt">Professional Tax (PT) (₹)</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" id="deduction_pt" name="professional_tax" class="form-control calc-input text-danger fw-medium" placeholder="0" value="{{ old('professional_tax', $employee->salary?->professional_tax ?? 0) }}" min="0">
                                </div>
                            </div>
                            <!-- TDS -->
                            <div class="col-md-6">
                                <label class="form-label" for="deduction_tds">Tax Deducted at Source (TDS / Income Tax) (₹)</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" id="deduction_tds" name="tds" class="form-control calc-input text-danger fw-medium" placeholder="0" value="{{ old('tds', $employee->salary?->tds ?? 0) }}" min="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Live Calculation Widget -->
            <div class="col-lg-4">
                <div class="card live-summary-card shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <i class="bx bx-calculator text-primary" style="font-size: 24px;"></i>
                            <h5 class="mb-0 calculator-title">Live Summary Calculator</h5>
                        </div>
                        <p class="text-muted small">Real-time calculations based on inputs.</p>

                        <div class="calc-row mt-4">
                            <span class="text-muted">Core Base Pay:</span>
                            <span class="fw-semibold text-dark" id="live_base">₹0.00</span>
                        </div>
                        <div class="calc-row">
                            <span class="text-muted">Total Allowances:</span>
                            <span class="fw-semibold text-success" id="live_allowances">+ ₹0.00</span>
                        </div>
                        <div class="calc-row">
                            <span class="text-muted">Total Deductions:</span>
                            <span class="fw-semibold text-danger" id="live_deductions">- ₹0.00</span>
                        </div>

                        <div class="net-payout-box mt-4">
                            <span class="text-muted small fw-medium d-block mb-1">Estimated Net Payout</span>
                            <h3 class="fw-extrabold text-primary mb-0" id="live_net">₹0.00</h3>
                        </div>

                        <div class="mt-4 pt-2">
                            <button type="submit" class="btn btn-primary w-100 py-2.5 mb-2">
                                <i class="bx bx-save me-1"></i> Save Salary Structure
                            </button>
                            <a href="{{ route('admin.employee-salary.details', $employee->id) }}" class="btn btn-outline-secondary w-100 py-2.5">
                                Cancel
                            </a>
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
    document.addEventListener('DOMContentLoaded', function() {
        // Collect all form input fields
        const baseInput = document.getElementById('base_salary');
        const hraInput = document.getElementById('allowance_hra');
        const daInput = document.getElementById('allowance_da');
        const specialInput = document.getElementById('allowance_special');
        
        const pfInput = document.getElementById('deduction_pf');
        const esiInput = document.getElementById('deduction_esi');
        const ptInput = document.getElementById('deduction_pt');
        const tdsInput = document.getElementById('deduction_tds');
        
        // Grab widget display labels
        const liveBase = document.getElementById('live_base');
        const liveAllowances = document.getElementById('live_allowances');
        const liveDeductions = document.getElementById('live_deductions');
        const liveNet = document.getElementById('live_net');

        // Formats values to currency string
        function formatINR(value) {
            return '₹' + value.toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        // Live calculation callback
        function updateCalculator() {
            const base = parseFloat(baseInput.value) || 0;
            
            const hra = parseFloat(hraInput.value) || 0;
            const da = parseFloat(daInput.value) || 0;
            const special = parseFloat(specialInput.value) || 0;
            const totalAllowances = hra + da + special;

            const pf = parseFloat(pfInput.value) || 0;
            const esi = parseFloat(esiInput.value) || 0;
            const pt = parseFloat(ptInput.value) || 0;
            const tds = parseFloat(tdsInput.value) || 0;
            const totalDeductions = pf + esi + pt + tds;

            const totalNet = base + totalAllowances - totalDeductions;

            // Render beautifully
            liveBase.innerText = formatINR(base);
            liveAllowances.innerText = '+ ' + formatINR(totalAllowances);
            liveDeductions.innerText = '- ' + formatINR(totalDeductions);
            liveNet.innerText = formatINR(totalNet);
        }

        // Bind events on inputs
        const allInputs = document.querySelectorAll('.calc-input');
        allInputs.forEach(input => {
            input.addEventListener('input', updateCalculator);
        });

        // Run once on load
        updateCalculator();
    });
</script>
@endsection
