@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">

    <!-- Header & Company Switcher -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1">
                <span class="text-muted fw-light">Administration /</span> Document Management Dashboard
            </h4>
            <p class="text-muted mb-0">Overview of document storage, uploads, expiring documents, and category breakdown.</p>
        </div>

        @if(auth()->user()->isSuperAdmin() && $companies->count() > 0)
        <div class="d-flex align-items-center">
            <label class="form-label mb-0 me-2 fw-semibold">Company:</label>
            <form action="{{ route('admin.documents.dashboard') }}" method="GET" id="companySelectForm">
                <select name="company_id" class="form-select form-select-sm" onchange="document.getElementById('companySelectForm').submit();">
                    <option value="">All Companies</option>
                    @foreach($companies as $comp)
                        <option value="{{ $comp->id }}" {{ $companyId == $comp->id ? 'selected' : '' }}>
                            {{ $comp->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
        @endif
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar me-3 bg-label-primary p-2 rounded">
                            <i class="bx bx-file text-primary fs-3"></i>
                        </div>
                        <div>
                            <span class="d-block text-muted small">Total Documents</span>
                            <h3 class="mb-0 fw-bold">{{ number_format($metrics['total_documents']) }}</h3>
                        </div>
                    </div>
                    <span class="badge bg-label-info small">+{{ $metrics['uploaded_today'] }} Uploaded Today</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar me-3 bg-label-success p-2 rounded">
                            <i class="bx bx-hard-drive text-success fs-3"></i>
                        </div>
                        <div>
                            <span class="d-block text-muted small">Used Storage</span>
                            <h3 class="mb-0 fw-bold">{{ $metrics['formatted_total_size'] }}</h3>
                        </div>
                    </div>
                    <span class="badge bg-label-secondary small">{{ $metrics['formatted_trashed_size'] }} In Trash</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar me-3 bg-label-warning p-2 rounded">
                            <i class="bx bx-time text-warning fs-3"></i>
                        </div>
                        <div>
                            <span class="d-block text-muted small">Expiring Soon (30d)</span>
                            <h3 class="mb-0 fw-bold text-warning">{{ $metrics['expiring_30_days'] }}</h3>
                        </div>
                    </div>
                    <a href="{{ route('admin.documents.reports.expiry') }}" class="small text-warning fw-semibold">View Expiry Report &rarr;</a>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar me-3 bg-label-danger p-2 rounded">
                            <i class="bx bx-error-circle text-danger fs-3"></i>
                        </div>
                        <div>
                            <span class="d-block text-muted small">Expiring Today</span>
                            <h3 class="mb-0 fw-bold text-danger">{{ $metrics['expiring_today'] }}</h3>
                        </div>
                    </div>
                    <span class="badge bg-label-danger small">{{ $metrics['expiring_7_days'] }} Expiring in 7 Days</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action Bar -->
    <div class="card mb-4 border-0 shadow-sm bg-gradient text-white" style="background: linear-gradient(135deg, #696cff 0%, #393b99 100%);">
        <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <h5 class="text-white fw-bold mb-1"><i class="bx bx-cloud-upload me-2"></i> Centralized Document Vault</h5>
                <p class="mb-0 text-white-50">Upload, organize into nested folders, set expiry reminders, and track document versions securely.</p>
            </div>
            <div class="mt-3 mt-md-0 d-flex gap-2">
                <a href="{{ route('admin.documents.create') }}" class="btn btn-light text-primary fw-bold">
                    <i class="bx bx-plus me-1"></i> Upload Document
                </a>
                <a href="{{ route('admin.documents.index') }}" class="btn btn-outline-light">
                    <i class="bx bx-folder-open me-1"></i> Document Explorer
                </a>
            </div>
        </div>
    </div>

    <!-- Expiring Today Alerts Banner -->
    @if($expiringTodayDocs->count() > 0)
    <div class="alert alert-danger d-flex align-items-center mb-4 shadow-sm" role="alert">
        <i class="bx bx-error-alt fs-3 me-3"></i>
        <div class="flex-grow-1">
            <h6 class="alert-heading fw-bold mb-1">Attention Required: {{ $expiringTodayDocs->count() }} Documents Expire Today!</h6>
            <p class="mb-0 small">Documents including: 
                @foreach($expiringTodayDocs as $expDoc)
                    <span class="badge bg-danger ms-1">{{ $expDoc->name }} ({{ $expDoc->document_number }})</span>
                @endforeach
            </p>
        </div>
        <a href="{{ route('admin.documents.reports.expiry', ['timeframe' => 'today']) }}" class="btn btn-sm btn-danger ms-3">Review Now</a>
    </div>
    @endif

    <!-- Recent Uploads & Most Downloaded -->
    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold"><i class="bx bx-history text-primary me-2"></i> Recently Uploaded Documents</h5>
                    <a href="{{ route('admin.documents.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Doc Number</th>
                                <th>Document Name</th>
                                <th>Category</th>
                                <th>Uploaded By</th>
                                <th>Uploaded At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentUploads as $recent)
                            <tr>
                                <td><span class="fw-semibold text-primary">{{ $recent->document_number }}</span></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bx bx-file-blank text-secondary fs-4 me-2"></i>
                                        <span>{{ Str::limit($recent->name, 25) }}</span>
                                    </div>
                                </td>
                                <td><span class="badge bg-label-info">{{ $recent->category?->name ?? 'N/A' }}</span></td>
                                <td><small>{{ $recent->uploader?->full_name ?? 'System' }}</small></td>
                                <td><small>{{ $recent->created_at->diffForHumans() }}</small></td>
                                <td>
                                    <a href="{{ route('admin.documents.show', $recent->id) }}" class="btn btn-icon btn-sm btn-outline-primary"><i class="bx bx-right-arrow-alt"></i></a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No recent document uploads found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold"><i class="bx bx-download text-success me-2"></i> Most Downloaded</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @forelse($mostDownloaded as $popular)
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div class="d-flex align-items-center me-3">
                                <div class="avatar me-3 bg-label-success p-2 rounded">
                                    <i class="bx bx-file text-success"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-semibold">{{ Str::limit($popular->name, 28) }}</h6>
                                    <small class="text-muted">{{ $popular->category?->name }} &bull; v{{ $popular->version }}</small>
                                </div>
                            </div>
                            <span class="badge bg-success rounded-pill">{{ $popular->downloads_count }} Downloads</span>
                        </div>
                        @empty
                        <p class="text-muted text-center py-4">No download history available yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Breakdown Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold"><i class="bx bx-category text-warning me-2"></i> Category Distribution</h5>
            <a href="{{ route('admin.documents.categories.index') }}" class="btn btn-sm btn-outline-warning">Manage Categories</a>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @foreach($categoriesDistribution as $cat)
                <div class="col-md-3 col-sm-6">
                    <div class="p-3 border rounded bg-light d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-1 fw-bold text-dark">{{ $cat->name }}</h6>
                            <span class="badge bg-label-primary">{{ $cat->documents_count }} Files</span>
                        </div>
                        <i class="bx bx-folder fs-2 text-primary opacity-50"></i>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

</div>
@endsection
