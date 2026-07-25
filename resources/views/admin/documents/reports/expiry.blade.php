@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1">
                <span class="text-muted fw-light">Document Reports /</span> Document Expiry Compliance
            </h4>
            <p class="text-muted mb-0">Track RC, Insurance, GST, Permits, Agreements, Contracts expiring today, next 7, 15, 30 days.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.documents.reports.expiry', ['timeframe' => 'all']) }}" class="btn btn-sm rounded-pill {{ $filter === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">All</a>
            <a href="{{ route('admin.documents.reports.expiry', ['timeframe' => 'today']) }}" class="btn btn-sm rounded-pill {{ $filter === 'today' ? 'btn-danger' : 'btn-outline-danger' }}">Today</a>
            <a href="{{ route('admin.documents.reports.expiry', ['timeframe' => '7_days']) }}" class="btn btn-sm rounded-pill {{ $filter === '7_days' ? 'btn-warning' : 'btn-outline-warning' }}">Next 7 Days</a>
            <a href="{{ route('admin.documents.reports.expiry', ['timeframe' => '15_days']) }}" class="btn btn-sm rounded-pill {{ $filter === '15_days' ? 'btn-warning' : 'btn-outline-warning' }}">Next 15 Days</a>
            <a href="{{ route('admin.documents.reports.expiry', ['timeframe' => '30_days']) }}" class="btn btn-sm rounded-pill {{ $filter === '30_days' ? 'btn-info' : 'btn-outline-info' }}">Next 30 Days</a>
            <a href="{{ route('admin.documents.reports.expiry', ['timeframe' => 'expired']) }}" class="btn btn-sm rounded-pill {{ $filter === 'expired' ? 'btn-dark' : 'btn-outline-dark' }}">Expired</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Document Number</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Company</th>
                        <th>Expiry Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $index => $doc)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><strong class="text-primary">{{ $doc->document_number }}</strong></td>
                        <td>{{ $doc->name }}</td>
                        <td><span class="badge bg-label-info">{{ $doc->category?->name }}</span></td>
                        <td>{{ $doc->company?->name }}</td>
                        <td>
                            @if($doc->is_expired)
                                <span class="badge bg-danger fs-7">{{ $doc->expiry_date->format('d M Y') }} (Expired)</span>
                            @elseif($doc->is_expiring_soon)
                                <span class="badge bg-warning text-dark fs-7">{{ $doc->expiry_date->format('d M Y') }}</span>
                            @else
                                <span class="badge bg-success fs-7">{{ $doc->expiry_date->format('d M Y') }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-label-secondary">{{ ucfirst($doc->status) }}</span>
                        </td>
                        <td>
                            <a href="{{ route('admin.documents.show', $doc->id) }}" class="btn btn-icon btn-sm btn-outline-primary" title="View Document">
                                <i class="bx bx-show"></i>
                            </a>
                            <a href="{{ route('admin.documents.download', $doc->id) }}" class="btn btn-icon btn-sm btn-outline-success" title="Download">
                                <i class="bx bx-download"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No expiring documents found for the selected timeframe.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
