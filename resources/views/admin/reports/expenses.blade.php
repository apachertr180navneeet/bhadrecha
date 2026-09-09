@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-header border-bottom bg-transparent py-3">
                    <h5 class="card-title mb-0 fw-bold" style="color: #4b5563;"><i class="bx bx-filter-alt me-2 text-primary"></i>Filter Expenses</h5>
                </div>
                <div class="card-body mt-3">
                    <form method="GET" action="{{ route('admin.reports.expenses') }}" class="row g-3 align-items-end">
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
                            <select name="store_id" id="expReportStore" class="form-select" {{ (auth()->user()->store_id && !($canViewAllStores ?? (auth()->user()->hasRole('Admin') || auth()->user()->can('view stores')))) ? 'disabled' : '' }}>
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
                            <select name="branch_id" id="expReportBranch" class="form-select" {{ (auth()->user()->branch_id && !($canViewAllBranches ?? (auth()->user()->hasRole('Admin') || auth()->user()->can('view branches')))) ? 'disabled' : '' }}>
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
                            <a href="{{ route('admin.reports.expenses') }}" class="btn btn-outline-secondary flex-grow-1"><i class="bx bx-reset me-1"></i>Reset</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="d-block text-muted mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px; text-transform: uppercase;">Total Expenses</span>
                            <h2 class="mb-0 text-danger fw-bold">₹{{ number_format($totalExpenses, 2) }}</h2>
                        </div>
                        <div class="stat-card-icon bg-label-danger rounded-circle" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                            <i class="bx bx-credit-card fs-3 text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-header border-bottom bg-transparent py-3">
                    <h5 class="card-title mb-0 fw-bold" style="color: #4b5563;"><i class="bx bx-pie-chart-alt-2 me-2 text-primary"></i>Category-wise Breakdown</h5>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb; padding-left: 1.5rem;">Category</th>
                                <th class="text-uppercase text-muted text-end" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb; padding-right: 1.5rem;">Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categoryWise as $category)
                                <tr>
                                    <td style="padding-left: 1.5rem;">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-3 bg-label-secondary text-secondary rounded-circle d-flex align-items-center justify-content-center">
                                                <i class="bx bx-purchase-tag fs-6"></i>
                                            </div>
                                            <span class="fw-semibold text-body">{{ $category->category->name ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-end fw-bold text-danger" style="padding-right: 1.5rem;">
                                        ₹{{ number_format($category->total ?? 0, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-5">
                                        <i class="bx bx-pie-chart-alt-2 fs-1 text-light mb-3"></i>
                                        <p class="mb-0">No category data available.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-header border-bottom bg-transparent py-3">
                    <h5 class="card-title mb-0 fw-bold" style="color: #4b5563;"><i class="bx bx-list-ul me-2 text-primary"></i>All Expenses</h5>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb; padding-left: 1.5rem;">Date</th>
                                <th class="text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb;">Category</th>
                                <th class="text-uppercase text-muted text-end" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb;">Amount</th>
                                <th class="text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb;">Remarks</th>
                                <th class="text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb;">Store</th>
                                <th class="text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb; padding-right: 1.5rem;">Branch</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expenses as $expense)
                                <tr>
                                    <td style="padding-left: 1.5rem;">
                                        <span class="text-muted"><i class="bx bx-calendar-event me-1"></i>{{ $expense->created_at->format('d M, Y') }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-secondary px-3 py-2 rounded-pill">{{ $expense->category->name ?? 'N/A' }}</span>
                                    </td>
                                    <td class="text-end fw-bold text-danger">
                                        ₹{{ number_format($expense->amount, 2) }}
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $expense->remarks ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <span class="text-muted"><i class="bx bx-store-alt me-1"></i>{{ $expense->store->store_name ?? $expense->store->name ?? 'N/A' }}</span>
                                    </td>
                                    <td style="padding-right: 1.5rem;">
                                        <span class="text-muted"><i class="bx bx-git-branch me-1"></i>{{ $expense->branch->name ?? 'N/A' }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <i class="bx bx-list-ul fs-1 text-light mb-3"></i>
                                        <p class="mb-0">No expense records found.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($expenses->hasPages())
                <div class="card-footer border-top bg-transparent pt-3 pb-2">
                    {{ $expenses->links() }}
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
            window.setupDependentBranchSelect('#expReportStore', '#expReportBranch');
        }
    });
</script>
@endsection
