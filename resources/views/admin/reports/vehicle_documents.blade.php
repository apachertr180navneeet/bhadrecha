@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <div>
            <h5 class="fw-bold mb-1">Vehicle Document Expiry Report</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Reports</a></li>
                    <li class="breadcrumb-item active">Vehicle Documents</li>
                </ol>
            </nav>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.reports.vehicle-documents') }}" class="mb-4">
        <div class="card">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Expiring Within (Days)</label>
                        <input type="number" name="threshold_days" class="form-control" value="{{ $thresholdDays }}" min="1">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Document Type</label>
                        <select name="document_type" class="form-select">
                            <option value="">All Documents</option>
                            @foreach($documentFields as $field => $label)
                                <option value="{{ $field }}" {{ request('document_type') == $field ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Vehicle</label>
                        <select name="vehicle_id" class="form-select">
                            <option value="">All Vehicles</option>
                            @foreach(\App\Models\Vehicle::where('status', 'active')
                                ->orderBy('vehicle_number')->get() as $vehicle)
                                <option value="{{ $vehicle->id }}" {{ request('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->vehicle_number }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1"><i class="bx bx-filter-alt me-1"></i>Filter</button>
                        <a href="{{ route('admin.reports.vehicle-documents') }}" class="btn btn-outline-secondary flex-grow-1"><i class="bx bx-reset me-1"></i>Reset</a>
                        @if($documents->count() > 0)
                        <a href="{{ route('admin.reports.vehicle-documents.export', 'excel') . '?' . http_build_query(request()->except('page')) }}" class="btn btn-success btn-sm"><i class="bx bx-spreadsheet me-1"></i> Excel</a>
                        <a href="{{ route('admin.reports.vehicle-documents.export', 'pdf') . '?' . http_build_query(request()->except('page')) }}" class="btn btn-danger btn-sm"><i class="bx bxs-file-pdf me-1"></i> PDF</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>

    @if($documents->count() > 0)
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border border-danger h-100">
                <div class="card-body text-center">
                    <h3 class="mb-1 text-danger fw-bold">{{ $totalExpired }}</h3>
                    <span class="text-muted">Expired</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border border-warning h-100">
                <div class="card-body text-center">
                    <h3 class="mb-1 text-warning fw-bold">{{ $totalWarning }}</h3>
                    <span class="text-muted">Expiring within 7 days</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border border-info h-100">
                <div class="card-body text-center">
                    <h3 class="mb-1 text-info fw-bold">{{ $totalUpcoming }}</h3>
                    <span class="text-muted">Expiring within {{ $thresholdDays }} days</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="vehDocTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Vehicle</th>
                            <th>Company</th>
                            <th>Document</th>
                            <th>Expiry Date</th>
                            <th>Days Left</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documents as $doc)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $doc['vehicle_number'] }}</td>
                            <td>{{ $doc['company_name'] }}</td>
                            <td>{{ $doc['document'] }}</td>
                            <td>{{ \Carbon\Carbon::parse($doc['expiry_date'])->format('d-m-Y') }}</td>
                            <td>
                                @php $dl = $doc['days_left']; @endphp
                                <span class="badge bg-label-{{ $dl <= 0 ? 'danger' : ($dl <= 7 ? 'warning' : 'info') }} badge-sm">
                                    {{ $dl <= 0 ? 'Expired' : $dl . ' days' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bx bx-check-circle text-success" style="font-size:3rem;"></i>
            <h6 class="mt-3 mb-1">No expiring documents found</h6>
            <p class="text-muted mb-0">All vehicle documents are valid for the next {{ $thresholdDays }} days.</p>
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#vehDocTable').DataTable({
        order: [[5, 'asc']],
        pageLength: 25,
        language: { searchPlaceholder: 'Search...' }
    });
});
</script>
@endpush
