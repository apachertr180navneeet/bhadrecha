@extends('admin.layouts.app')

@section('style')
<style>
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    .timeline::before {
        content: '';
        position: absolute;
        left: 10px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }
    .timeline-item {
        position: relative;
        padding-bottom: 24px;
    }
    .timeline-item:last-child {
        padding-bottom: 0;
    }
    .timeline-dot {
        position: absolute;
        left: -24px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        border: 2px solid #fff;
        top: 4px;
    }
    .timeline-dot.increment { background: #71dd37; }
    .timeline-dot.decrement { background: #ff3e1d; }
    .amount-change {
        font-size: 14px;
        font-weight: 700;
    }
</style>
@endsection

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
            <i class="bx bx-check-circle me-2" style="font-size: 20px;"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
            <i class="bx bx-error-circle me-2" style="font-size: 20px;"></i>
            <span>{{ session('error') }}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1">Salary Revision History</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.employee-salary.employees-list') }}">Employee Salary</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.employee-salary.details', $employee->id) }}">{{ $employee->full_name }}</a></li>
                    <li class="breadcrumb-item active">Revisions</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.employee-salary.details', $employee->id) }}" class="btn btn-outline-secondary">
                <i class="bx bx-chevron-left me-1"></i> Back
            </a>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#applyRevisionModal">
                <i class="bx bx-trending-up me-1"></i> Apply Revision
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <div class="avatar-initial bg-label-primary rounded-circle d-flex align-items-center justify-content-center fw-bold mx-auto mb-3" style="width: 64px; height: 64px; font-size: 24px;">
                        {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                    </div>
                    <h5 class="fw-bold mb-1">{{ $employee->full_name }}</h5>
                    <span class="badge bg-label-secondary mb-3">{{ $role ? $role->name : 'Staff Member' }}</span>
                    <div class="d-flex justify-content-around mt-3">
                        <div>
                            <small class="text-muted d-block">Current Base</small>
                            <span class="fw-bold text-primary">₹{{ number_format($employee->salary?->base_salary ?? 0, 2) }}</span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Total Revisions</small>
                            <span class="fw-bold">{{ $revisions->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-transparent py-3">
                    <h5 class="mb-0 fw-bold"><i class="bx bx-history text-primary me-2"></i>Revision Timeline</h5>
                </div>
                <div class="card-body">
                    @forelse($revisions as $revision)
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-dot {{ $revision->change_type }}"></div>
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong class="text-dark">₹{{ number_format($revision->previous_base_salary, 2) }}</strong>
                                    <i class="bx bx-chevrons-right text-muted mx-2"></i>
                                    <strong class="text-dark">₹{{ number_format($revision->new_base_salary, 2) }}</strong>
                                    <span class="amount-change {{ $revision->change_type === 'increment' ? 'text-success' : 'text-danger' }} ms-2">
                                        {{ $revision->change_type === 'increment' ? '+' : '' }}{{ number_format($revision->change_amount, 2) }}
                                    </span>
                                    <span class="badge {{ $revision->change_type === 'increment' ? 'bg-label-success' : 'bg-label-danger' }} ms-2">
                                        {{ ucfirst($revision->change_type) }}
                                    </span>
                                    <p class="text-muted small mb-0 mt-1">{{ $revision->reason }}</p>
                                </div>
                                <div class="text-end small">
                                    <span class="text-muted d-block">{{ $revision->effective_date->format('d M Y') }}</span>
                                    <span class="text-muted">by {{ $revision->creator?->full_name ?? 'System' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5">
                        <i class="bx bx-history text-muted" style="font-size: 48px;"></i>
                        <h6 class="text-muted mt-2">No revisions recorded yet.</h6>
                        <p class="text-muted small">Click "Apply Revision" to record the first salary change.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="applyRevisionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header border-bottom-0 pb-0">
                <div>
                    <h5 class="modal-title fw-bold text-dark">Apply Salary Revision</h5>
                    <p class="text-muted small mb-0">{{ $employee->full_name }} • EMP-{{ str_pad($employee->id, 4, '0', STR_PAD_LEFT) }}</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <hr class="my-3">
            <div class="modal-body pt-0">
                <form method="POST" action="{{ route('admin.employee-salary.apply-revision', $employee->id) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted">Current Base Salary</label>
                        <p class="form-control-plaintext fw-bold text-dark py-0">₹{{ number_format($employee->salary?->base_salary ?? 0, 2) }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark" for="new_base_salary">New Base Salary (₹) <span class="text-danger">*</span></label>
                        <input type="number" id="new_base_salary" name="new_base_salary" class="form-control form-control-lg" placeholder="Enter new salary" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark" for="effective_date">Effective Date <span class="text-danger">*</span></label>
                        <input type="date" max="9999-12-31" id="effective_date" name="effective_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark" for="reason">Reason <span class="text-danger">*</span></label>
                        <select id="reason" name="reason" class="form-select" required>
                            <option value="">Select reason</option>
                            <option value="Annual Increment">Annual Increment</option>
                            <option value="Promotion">Promotion</option>
                            <option value="Appraisal">Appraisal</option>
                            <option value="Salary Correction">Salary Correction</option>
                            <option value="Role Change">Role Change</option>
                        </select>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Apply Revision</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
