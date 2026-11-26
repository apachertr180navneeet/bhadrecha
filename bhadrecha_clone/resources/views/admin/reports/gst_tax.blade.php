@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <div>
            <h5 class="fw-bold mb-1">GST & Tax Report</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Reports</a></li>
                    <li class="breadcrumb-item active">GST & Tax</li>
                </ol>
            </nav>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.reports.gst-tax') }}" class="mb-4">
        <div class="card">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">From Date</label>
                        <input type="date" max="9999-12-31" name="from_date" class="form-control" value="{{ $fromDate }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">To Date</label>
                        <input type="date" max="9999-12-31" name="to_date" class="form-control" value="{{ $toDate }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">GST Rate</label>
                        <select name="gst_master_id" class="form-select">
                            <option value="">All Rates</option>
                            @foreach($gstMasters as $gm)
                                <option value="{{ $gm->id }}" {{ request('gst_master_id') == $gm->id ? 'selected' : '' }}>{{ $gm->gst_rate }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Vehicle</label>
                        <select name="vehicle_id" class="form-select">
                            <option value="">All Vehicles</option>
                            @foreach($vehicles as $v)
                                <option value="{{ $v->id }}" {{ request('vehicle_id') == $v->id ? 'selected' : '' }}>{{ $v->vehicle_number }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1"><i class="bx bx-filter-alt me-1"></i>Filter</button>
                        <a href="{{ route('admin.reports.gst-tax') }}" class="btn btn-outline-secondary flex-grow-1"><i class="bx bx-reset me-1"></i>Reset</a>
                        <a href="{{ route('admin.reports.gst-tax.export', 'excel') . '?' . http_build_query(request()->except('page')) }}" class="btn btn-success btn-sm"><i class="bx bx-spreadsheet me-1"></i> Excel</a>
                        <a href="{{ route('admin.reports.gst-tax.export', 'pdf') . '?' . http_build_query(request()->except('page')) }}" class="btn btn-danger btn-sm"><i class="bx bxs-file-pdf me-1"></i> PDF</a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card bg-label-primary h-100">
                <div class="card-body text-center py-3">
                    <h6 class="mb-1">Total Bills</h6>
                    <h3 class="mb-0">{{ $totalBills }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card bg-label-info h-100">
                <div class="card-body text-center py-3">
                    <h6 class="mb-1">Total Freight</h6>
                    <h4 class="mb-0">₹ {{ number_format($totalFreight, 0) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card bg-label-warning h-100">
                <div class="card-body text-center py-3">
                    <h6 class="mb-1">Total GST</h6>
                    <h4 class="mb-0">₹ {{ number_format($totalGst, 0) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card bg-label-success h-100">
                <div class="card-body text-center py-3">
                    <h6 class="mb-1">Total Amount</h6>
                    <h4 class="mb-0">₹ {{ number_format($totalAmount, 0) }}</h4>
                </div>
            </div>
        </div>
    </div>

    @if($gstBreakdown->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">GST Rate-wise Summary</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>GST Rate</th>
                                <th class="text-center">Bills</th>
                                <th class="text-end">Freight</th>
                                <th class="text-end">GST Amount</th>
                                <th class="text-end">Other Charges</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($gstBreakdown as $rate => $data)
                            <tr>
                                <td><strong>{{ $rate }}</strong></td>
                                <td class="text-center"><span class="badge bg-label-primary">{{ $data['count'] }}</span></td>
                                <td class="text-end">₹ {{ number_format($data['freight'], 0) }}</td>
                                <td class="text-end">₹ {{ number_format($data['gst'], 0) }}</td>
                                <td class="text-end">₹ {{ number_format($data['other'], 0) }}</td>
                                <td class="text-end"><strong>₹ {{ number_format($data['total'], 0) }}</strong></td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th><strong>Total</strong></th>
                                <th class="text-center">{{ $totalBills }}</th>
                                <th class="text-end">₹ {{ number_format($totalFreight, 0) }}</th>
                                <th class="text-end">₹ {{ number_format($totalGst, 0) }}</th>
                                <th class="text-end">₹ {{ number_format($totalOtherCharges, 0) }}</th>
                                <th class="text-end">₹ {{ number_format($totalAmount, 0) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Bill-wise Details</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-sm" id="gstTable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>LR No</th>
                                <th>Date</th>
                                <th>Consignor</th>
                                <th>Consignee</th>
                                <th>From → To</th>
                                <th>Vehicle</th>
                                <th class="text-end">Freight</th>
                                <th class="text-center">GST Rate</th>
                                <th class="text-end">GST</th>
                                <th class="text-end">Other</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bulties as $b)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><strong>{{ $b->lr_no }}</strong></td>
                                <td>{{ $b->lr_date->format('d-m-Y') }}</td>
                                <td>{{ $b->consignor?->name ?? '-' }}</td>
                                <td>{{ $b->consignee?->name ?? '-' }}</td>
                                <td>{{ $b->originCity?->name ?? '-' }} → {{ $b->destinationCity?->name ?? '-' }}</td>
                                <td>{{ $b->vehicle?->vehicle_number ?? '-' }}</td>
                                <td class="text-end">₹ {{ number_format($b->freight_charges, 0) }}</td>
                                <td class="text-center">{{ $b->gstMaster?->gst_rate ?? 'N/A' }}</td>
                                <td class="text-end">₹ {{ number_format($b->gst_amount, 0) }}</td>
                                <td class="text-end">₹ {{ number_format($b->other_charges, 0) }}</td>
                                <td class="text-end"><strong>₹ {{ number_format($b->total_amount, 0) }}</strong></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="12" class="text-center text-muted py-3">No bills found with GST for the selected period.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#gstTable').DataTable({
        order: [[1, 'asc']],
        pageLength: 25,
        language: { searchPlaceholder: 'Search...' }
    });
});
</script>
@endpush