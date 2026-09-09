@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-header border-bottom bg-transparent py-3">
                    <h5 class="card-title mb-0 fw-bold" style="color: #4b5563;"><i class="bx bx-filter-alt me-2 text-primary"></i>Filter Customers</h5>
                </div>
                <div class="card-body mt-3">
                    <form method="GET" action="{{ route('admin.reports.customers') }}" class="row g-3 align-items-end">
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
                            <select name="store_id" id="custReportStore" class="form-select" {{ (auth()->user()->store_id && !($canViewAllStores ?? (auth()->user()->hasRole('Admin') || auth()->user()->can('view stores')))) ? 'disabled' : '' }}>
                                <option value="">All Stores</option>
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}" {{ (string)($selectedStoreId ?? request('store_id')) === (string)$store->id ? 'selected' : '' }}>{{ $store->store_name ?? $store->name }}</option>
                                @endforeach
                            </select>
                            @if(auth()->user()->store_id && !($canViewAllStores ?? (auth()->user()->hasRole('Admin') || auth()->user()->can('view stores'))))
                                <input type="hidden" name="store_id" value="{{ auth()->user()->store_id }}">
                            @endif
                        </div>
                        <div class="col-12 col-sm-6 col-md-4 col-xl-3">
                            <label class="form-label text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600;">Branch</label>
                            <select name="branch_id" id="custReportBranch" class="form-select" {{ (auth()->user()->branch_id && !($canViewAllBranches ?? (auth()->user()->hasRole('Admin') || auth()->user()->can('view branches')))) ? 'disabled' : '' }}>
                                <option value="">All Branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" data-store-id="{{ $branch->store_id }}" {{ (string)($selectedBranchId ?? request('branch_id')) === (string)$branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                            @if(auth()->user()->branch_id && !($canViewAllBranches ?? (auth()->user()->hasRole('Admin') || auth()->user()->can('view branches'))))
                                <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
                            @endif
                        </div>
                        <div class="col-12 col-sm-6 col-md-4 col-xl-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1"><i class="bx bx-filter-alt me-1"></i>Filter</button>
                            <a href="{{ route('admin.reports.customers') }}" class="btn btn-outline-secondary flex-grow-1"><i class="bx bx-reset me-1"></i>Reset</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4 mb-4 mb-md-0">
            <div class="card h-100 shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="d-block text-muted mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px; text-transform: uppercase;">Total Customers</span>
                            <h2 class="mb-0 text-primary fw-bold">{{ $customers->total() ?? 0 }}</h2>
                        </div>
                        <div class="stat-card-icon bg-label-primary rounded-circle" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                            <i class="bx bx-user fs-3 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4 mb-md-0">
            <div class="card h-100 shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="d-block text-muted mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px; text-transform: uppercase;">New Customers</span>
                            <h2 class="mb-0 text-info fw-bold">{{ $newCustomers->count() ?? 0 }}</h2>
                        </div>
                        <div class="stat-card-icon bg-label-info rounded-circle" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                            <i class="bx bx-user-plus fs-3 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="d-block text-muted mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px; text-transform: uppercase;">Repeat Customers</span>
                            <h2 class="mb-0 text-success fw-bold">{{ $repeatCustomers->count() ?? 0 }}</h2>
                        </div>
                        <div class="stat-card-icon bg-label-success rounded-circle" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                            <i class="bx bx-revision fs-3 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-header border-bottom bg-transparent py-3">
                    <h5 class="card-title mb-0 fw-bold" style="color: #4b5563;"><i class="bx bx-group me-2 text-primary"></i>All Customers</h5>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb; padding-left: 1.5rem;">Customer Info</th>
                                <th class="text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb;">Contact</th>
                                <th class="text-uppercase text-muted text-center" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb;">Total Appointments</th>
                                <th class="text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb;">Last Visit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customers as $customer)
                                <tr>
                                    <td style="padding-left: 1.5rem;">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-3">
                                                <span class="avatar-initial rounded-circle bg-label-primary text-primary fw-bold">{{ strtoupper(substr($customer->name, 0, 1)) }}</span>
                                            </div>
                                            <span class="fw-semibold text-body">{{ $customer->name }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            @if($customer->mobile)
                                                <span class="text-muted mb-1"><i class="bx bx-phone me-1" style="font-size: 0.9rem;"></i>{{ $customer->mobile }}</span>
                                            @else
                                                <span class="text-muted mb-1 fst-italic">No Phone</span>
                                            @endif
                                            
                                            @if($customer->email)
                                                <small class="text-primary"><i class="bx bx-envelope me-1" style="font-size: 0.9rem;"></i>{{ $customer->email }}</small>
                                            @else
                                                <small class="text-muted fst-italic">No Email</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-label-secondary px-3 py-2 rounded-pill">{{ $customer->appointments_count ?? 0 }}</span>
                                    </td>
                                    <td>
                                        <span class="text-muted"><i class="bx bx-calendar-event me-1"></i>{{ $customer->last_visit ? \Carbon\Carbon::parse($customer->last_visit)->format('d M, Y') : 'N/A' }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-5">
                                        <i class="bx bx-group fs-1 text-light mb-3"></i>
                                        <p class="mb-0">No customers found.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($customers->hasPages())
                <div class="card-footer border-top bg-transparent pt-3 pb-2">
                    {{ $customers->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        if (window.setupDependentBranchSelect) {
            window.setupDependentBranchSelect('#custReportStore', '#custReportBranch');
        }
    });
</script>
@endsection
