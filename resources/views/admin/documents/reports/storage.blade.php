@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1">
                <span class="text-muted fw-light">Document Reports /</span> Storage Usage Analysis
            </h4>
            <p class="text-muted mb-0">Detailed breakdown of disk usage, largest files, and trash space usage.</p>
        </div>
    </div>

    <!-- Storage Metrics Row -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <span class="d-block text-muted small fw-semibold">Active Storage Used</span>
                    <h2 class="fw-bold text-primary mb-1">{{ $metrics['formatted_total_size'] }}</h2>
                    <small class="text-muted">{{ number_format($metrics['total_documents']) }} Total Files</small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <span class="d-block text-muted small fw-semibold">Trash Storage Consumption</span>
                    <h2 class="fw-bold text-secondary mb-1">{{ $metrics['formatted_trashed_size'] }}</h2>
                    <small class="text-muted">{{ $metrics['trashed_count'] }} Files in Trash Bin</small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <span class="d-block text-muted small fw-semibold">Uploads Today</span>
                    <h2 class="fw-bold text-success mb-1">{{ $metrics['uploaded_today'] }}</h2>
                    <small class="text-muted">Files uploaded today</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Largest Files Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light border-bottom">
            <h5 class="card-title mb-0 fw-bold"><i class="bx bx-file text-warning me-2"></i> Top 10 Largest Files</h5>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Doc Number</th>
                        <th>Document Name</th>
                        <th>Format</th>
                        <th>Size</th>
                        <th>Uploaded By</th>
                        <th>Upload Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($metrics['largest_files'] as $index => $doc)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><strong class="text-primary">{{ $doc->document_number }}</strong></td>
                        <td>{{ $doc->name }}</td>
                        <td><span class="badge bg-label-dark">{{ strtoupper($doc->file_extension) }}</span></td>
                        <td><strong class="text-dark">{{ $doc->formatted_file_size }}</strong></td>
                        <td>{{ $doc->uploader?->full_name }}</td>
                        <td>{{ $doc->created_at->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route('admin.documents.show', $doc->id) }}" class="btn btn-icon btn-sm btn-outline-primary">
                                <i class="bx bx-show"></i>
                            </a>
                            <a href="{{ route('admin.documents.download', $doc->id) }}" class="btn btn-icon btn-sm btn-outline-success">
                                <i class="bx bx-download"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No storage records available.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
