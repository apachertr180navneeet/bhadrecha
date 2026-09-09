@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h5 class="card-title mb-0">Sales Report</h5>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <a href="{{ route('admin.reports.export.sales.pdf', array_merge(['date_from' => $dateFrom, 'date_to' => $dateTo], request()->all())) }}" class="btn btn-sm btn-danger me-1"><i class="bx bxs-file-pdf me-1"></i>Export PDF</a>
                        <a href="{{ route('admin.reports.export.sales.excel', array_merge(['date_from' => $dateFrom, 'date_to' => $dateTo], request()->all())) }}" class="btn btn-sm btn-success"><i class="bx bx-spreadsheet me-1"></i>Export Excel</a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.reports.sales') }}" class="row g-3 align-items-end">
                        <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                            <label class="form-label text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600;">Date From</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from', $dateFrom) }}">
                        </div>
                        <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                            <label class="form-label text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600;">Date To</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to', $dateTo) }}">
                        </div>
                        <div class="col-12 col-sm-6 col-md-4 col-xl-3">
                            <label class="form-label text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600;">Store</label>
                            <select name="store_id" id="reportStoreSelect" class="form-select" {{ (auth()->user()->store_id && !($canViewAllStores ?? (auth()->user()->hasRole('Admin') || auth()->user()->can('view stores')))) ? 'disabled' : '' }}>
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
                            <select name="branch_id" id="reportBranchSelect" class="form-select" {{ (auth()->user()->branch_id && !($canViewAllBranches ?? (auth()->user()->hasRole('Admin') || auth()->user()->can('view branches')))) ? 'disabled' : '' }}>
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
                            <a href="{{ route('admin.reports.sales') }}" class="btn btn-outline-secondary flex-grow-1"><i class="bx bx-reset me-1"></i>Reset</a>
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
                            <span class="d-block text-muted mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px; text-transform: uppercase;">Total Sales (Filtered)</span>
                            <h2 class="mb-0 text-primary fw-bold">₹{{ number_format($totalSales, 2) }}</h2>
                        </div>
                        <div class="stat-card-icon bg-label-primary rounded-circle" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                            <i class="bx bx-wallet fs-3 text-primary"></i>
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
                            <span class="d-block text-muted mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px; text-transform: uppercase;">Appointments</span>
                            <h2 class="mb-0 text-info fw-bold">{{ $totalAppointmentsCount }}</h2>
                        </div>
                        <div class="stat-card-icon bg-label-info rounded-circle" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                            <i class="bx bx-calendar-check fs-3 text-info"></i>
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
                            <span class="d-block text-muted mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px; text-transform: uppercase;">Avg. Sale Value</span>
                            <h2 class="mb-0 text-success fw-bold">₹{{ $totalAppointmentsCount > 0 ? number_format($totalSales / $totalAppointmentsCount, 2) : '0.00' }}</h2>
                        </div>
                        <div class="stat-card-icon bg-label-success rounded-circle" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                            <i class="bx bx-trending-up fs-3 text-success"></i>
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
                    <h5 class="card-title mb-0 fw-bold" style="color: #4b5563;"><i class="bx bx-list-ul me-2 text-primary"></i>Appointment Sales</h5>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover align-middle mb-0" id="appointmentsTable">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb;">Date</th>
                                <th class="text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb;">Customer</th>
                                <th class="text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb;">Service</th>
                                <th class="text-uppercase text-muted text-end" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb;">Amount</th>
                                <th class="text-uppercase text-muted text-end" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb;">Discount</th>
                                <th class="text-uppercase text-muted text-end" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb;">Final Amount</th>
                                <th class="text-uppercase text-muted text-center" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb;">Status</th>
                                <th class="text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb;">Store</th>
                                <th class="text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb;">Branch</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($appointments as $appointment)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-3 bg-label-secondary rounded d-flex align-items-center justify-content-center">
                                                <i class="bx bx-calendar-event fs-6"></i>
                                            </div>
                                            <div class="d-flex flex-column">
                                                <span class="fw-semibold text-body">{{ $appointment->appointment_date ? $appointment->appointment_date->format('d M, Y') : $appointment->created_at->format('d M, Y') }}</span>
                                                <small class="text-muted">{{ $appointment->appointment_date ? $appointment->appointment_date->format('h:i A') : $appointment->created_at->format('h:i A') }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ $appointment->customer->name ?? $appointment->customer_name ?? 'N/A' }}</span>
                                    </td>
                                     <td>
                                         @if($appointment->appointmentServices && $appointment->appointmentServices->count() > 0)
                                             @foreach($appointment->appointmentServices as $appSvc)
                                                 @php
                                                     $mNames = $appSvc->staffMembers()->pluck('full_name')->filter()->implode(', ');
                                                     if (empty($mNames) && $appSvc->staff) {
                                                         $mNames = $appSvc->staff->full_name;
                                                     }
                                                 @endphp
                                                 <div class="mb-1">
                                                     <span class="badge bg-label-info">{{ $appSvc->service->service_name ?? 'N/A' }}</span>
                                                     @if(!empty($mNames))
                                                         <small class="text-muted fw-semibold">({{ $mNames }})</small>
                                                     @endif
                                                 </div>
                                             @endforeach
                                         @else
                                             @php
                                                 $stf = $appointment->staff->full_name ?? $appointment->staff->name ?? null;
                                             @endphp
                                             <span class="badge bg-label-info">{{ $appointment->service->service_name ?? $appointment->service_name ?? 'N/A' }}</span>
                                             @if(!empty($stf))
                                                 <small class="text-muted fw-semibold">({{ $stf }})</small>
                                             @endif
                                         @endif
                                     </td>
                                    <td class="text-end text-muted">₹{{ number_format($appointment->total_amount ?? $appointment->final_amount ?? 0, 2) }}</td>
                                    <td class="text-end text-danger">-₹{{ number_format($appointment->discount ?? 0, 2) }}</td>
                                    <td class="text-end fw-bold text-success">₹{{ number_format($appointment->final_amount, 2) }}</td>
                                    <td class="text-center">
                                        @if($appointment->payment_status == 'paid')
                                            <span class="badge bg-label-success px-2 py-1"><i class="bx bx-check-circle me-1"></i>Paid</span>
                                        @elseif($appointment->payment_status == 'pending')
                                            <span class="badge bg-label-warning px-2 py-1"><i class="bx bx-time-five me-1"></i>Pending</span>
                                        @else
                                            <span class="badge bg-label-danger px-2 py-1">{{ ucfirst($appointment->payment_status) }}</span>
                                        @endif
                                    </td>
                                    <td><span class="text-muted"><i class="bx bx-store-alt me-1"></i>{{ $appointment->store->store_name ?? $appointment->store_name ?? 'N/A' }}</span></td>
                                    <td><span class="text-muted"><i class="bx bx-git-branch me-1"></i>{{ $appointment->branch->name ?? 'N/A' }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-5">
                                        <i class="bx bx-receipt fs-1 text-light mb-3"></i>
                                        <p class="mb-0">No sales records found for this period.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($appointments->hasPages())
                <div class="card-footer border-top bg-transparent">
                    {{ $appointments->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-header border-bottom bg-transparent py-3">
                    <h5 class="card-title mb-0 fw-bold" style="color: #4b5563;"><i class="bx bx-pie-chart-alt-2 me-2 text-primary"></i>Service-wise Sales Breakdown</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover align-middle mb-0" id="servicesTable">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb; padding-left: 1.5rem;">Service Name</th>
                                    <th class="text-uppercase text-muted text-center" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb;">Total Appointments</th>
                                    <th class="text-uppercase text-muted text-end" style="font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb; padding-right: 1.5rem;">Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($serviceWiseSales as $sale)
                                    <tr>
                                        <td style="padding-left: 1.5rem;">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-3 bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center">
                                                    <i class="bx bx-cut fs-6"></i>
                                                </div>
                                                <span class="fw-semibold text-body">{{ $sale->service_name ?? 'N/A' }}</span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-label-secondary px-3 py-2 rounded-pill">{{ $sale->total_count ?? 0 }}</span>
                                        </td>
                                        <td class="text-end fw-bold text-success" style="padding-right: 1.5rem;">₹{{ number_format($sale->total_sales ?? 0, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        // Initialize DataTables only on the service breakdown table, not the paginated appointments table
        $('#servicesTable').DataTable({
            pageLength: 10,
            ordering: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search services...",
                emptyTable: "No service data available for this period."
            }
        });

        if (window.setupDependentBranchSelect) {
            window.setupDependentBranchSelect('#reportStoreSelect', '#reportBranchSelect');
        }
    });
</script>
@endsection
