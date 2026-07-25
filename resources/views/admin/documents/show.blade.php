@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    
    <!-- Top Action Banner -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold py-1 mb-1">
                <span class="text-muted fw-light">Document Management /</span> {{ $document->name }}
            </h4>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-label-primary fs-7">{{ $document->document_number }}</span>
                <span class="badge bg-label-info">v{{ $document->version }}</span>
                @if($document->status === 'active')
                    <span class="badge bg-success">Active</span>
                @elseif($document->status === 'expired' || $document->is_expired)
                    <span class="badge bg-danger">Expired</span>
                @else
                    <span class="badge bg-secondary">{{ ucfirst($document->status) }}</span>
                @endif
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.documents.preview', $document->id) }}" class="btn btn-info" target="_blank">
                <i class="bx bx-show-alt me-1"></i> Browser Preview
            </a>
            <a href="{{ route('admin.documents.download', $document->id) }}" class="btn btn-success">
                <i class="bx bx-download me-1"></i> Download File
            </a>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadVersionModal">
                <i class="bx bx-cloud-upload me-1"></i> Upload New Version
            </button>
            <a href="{{ route('admin.documents.edit', $document->id) }}" class="btn btn-outline-warning">
                <i class="bx bx-edit-alt me-1"></i> Edit
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row g-4">
        <!-- Document Meta Card -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light border-bottom">
                    <h5 class="card-title mb-0 fw-bold"><i class="bx bx-file text-primary me-2"></i> File Overview</h5>
                </div>
                <div class="card-body pt-3">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="text-muted fw-semibold" width="40%">File Name:</td>
                            <td class="fw-bold text-dark text-break">{{ $document->original_file_name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Format:</td>
                            <td><span class="badge bg-label-dark">{{ strtoupper($document->file_extension) }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">File Size:</td>
                            <td>{{ $document->formatted_file_size }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">MIME Type:</td>
                            <td><small class="text-muted">{{ $document->mime_type }}</small></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Category:</td>
                            <td><span class="badge bg-label-info">{{ $document->category?->name }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Folder:</td>
                            <td>{{ $document->folder?->name ?? 'Root' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Company:</td>
                            <td>{{ $document->company?->name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Branch:</td>
                            <td>{{ $document->branch?->name ?? 'All Branches' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Uploaded By:</td>
                            <td>{{ $document->uploader?->full_name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Upload Date:</td>
                            <td>{{ $document->created_at->format('d M Y, h:i A') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Total Downloads:</td>
                            <td><span class="badge bg-success rounded-pill">{{ $document->downloads_count }}</span></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Important Dates Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom">
                    <h5 class="card-title mb-0 fw-bold"><i class="bx bx-calendar text-danger me-2"></i> Dates & Compliance</h5>
                </div>
                <div class="card-body pt-3">
                    <div class="mb-3">
                        <small class="text-muted d-block fw-semibold">Issue Date</small>
                        <span class="fw-bold">{{ $document->issue_date ? $document->issue_date->format('d F Y') : 'N/A' }}</span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block fw-semibold">Effective Date</small>
                        <span class="fw-bold">{{ $document->effective_date ? $document->effective_date->format('d F Y') : 'N/A' }}</span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block fw-semibold">Expiry Date</small>
                        @if($document->expiry_date)
                            @if($document->is_expired)
                                <span class="badge bg-danger fs-6">{{ $document->expiry_date->format('d F Y') }} (EXPIRED)</span>
                            @elseif($document->is_expiring_soon)
                                <span class="badge bg-warning text-dark fs-6">{{ $document->expiry_date->format('d F Y') }} (Expiring Soon)</span>
                            @else
                                <span class="badge bg-success fs-6">{{ $document->expiry_date->format('d F Y') }}</span>
                            @endif
                        @else
                            <span class="text-muted">No Expiry Date Set</span>
                        @endif
                    </div>
                    @if($document->tags)
                    <div>
                        <small class="text-muted d-block fw-semibold mb-1">Tags</small>
                        @foreach($document->tags as $tag)
                            <span class="badge bg-label-secondary me-1 mb-1">#{{ $tag }}</span>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Main Content Area: Nav Tabs -->
        <div class="col-lg-8">
            <div class="nav-align-top mb-4">
                <ul class="nav nav-tabs nav-fill" role="tablist">
                    <li class="nav-item">
                        <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#tab-versions">
                            <i class="bx bx-history me-1"></i> Version History ({{ $document->versions->count() }})
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-downloads">
                            <i class="bx bx-download me-1"></i> Download Logs ({{ $document->downloads->count() }})
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-activities">
                            <i class="bx bx-list-check me-1"></i> Audit Trail ({{ $document->activities->count() }})
                        </button>
                    </li>
                </ul>
                <div class="tab-content border-0 shadow-sm p-4 bg-white rounded-bottom">

                    <!-- Version History Tab -->
                    <div class="tab-pane fade show active" id="tab-versions" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0">Version Changelog & Past Files</h6>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#uploadVersionModal">
                                <i class="bx bx-plus me-1"></i> Upload New Version
                            </button>
                        </div>
                        <div class="table-responsive text-nowrap">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Version</th>
                                        <th>Original File Name</th>
                                        <th>Size</th>
                                        <th>Uploaded By</th>
                                        <th>Changelog</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($document->versions as $ver)
                                    <tr class="{{ $ver->version_number == $document->version ? 'table-primary' : '' }}">
                                        <td>
                                            <span class="badge bg-primary">v{{ $ver->version_number }}</span>
                                            @if($ver->version_number == $document->version)
                                                <span class="badge bg-success ms-1">Latest</span>
                                            @endif
                                        </td>
                                        <td><small class="fw-bold text-dark">{{ $ver->original_file_name }}</small></td>
                                        <td><small>{{ $ver->formatted_file_size }}</small></td>
                                        <td><small>{{ $ver->uploader?->full_name }}</small></td>
                                        <td><small class="text-muted">{{ Str::limit($ver->changelog ?? 'N/A', 30) }}</small></td>
                                        <td><small>{{ $ver->created_at->format('d M Y, h:i A') }}</small></td>
                                        <td>
                                            <a href="{{ route('admin.documents.versions.download', $ver->id) }}" class="btn btn-icon btn-sm btn-outline-success" title="Download Version">
                                                <i class="bx bx-download"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Download Logs Tab -->
                    <div class="tab-pane fade" id="tab-downloads" role="tabpanel">
                        <h6 class="fw-bold mb-3">Download History</h6>
                        <div class="table-responsive text-nowrap">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>User</th>
                                        <th>IP Address</th>
                                        <th>Downloaded At</th>
                                        <th>User Agent</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($document->downloads as $dl)
                                    <tr>
                                        <td><strong class="text-dark">{{ $dl->user?->full_name }}</strong></td>
                                        <td><code>{{ $dl->ip_address }}</code></td>
                                        <td>{{ $dl->downloaded_at->format('d M Y, h:i A') }}</td>
                                        <td><small class="text-muted">{{ Str::limit($dl->user_agent, 40) }}</small></td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">No download activity recorded yet.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Audit Trail Tab -->
                    <div class="tab-pane fade" id="tab-activities" role="tabpanel">
                        <h6 class="fw-bold mb-3">Audit Logs</h6>
                        <div class="timeline">
                            @forelse($document->activities as $act)
                            <div class="d-flex mb-3 pb-2 border-bottom">
                                <div class="avatar me-3 bg-label-info p-2 rounded">
                                    <i class="bx bx-user-check text-info"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-0 fw-bold">{{ ucfirst($act->action) }}</h6>
                                        <small class="text-muted">{{ $act->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-1 text-dark small">{{ $act->description }}</p>
                                    <small class="text-muted">By {{ $act->user?->full_name }} &bull; IP: {{ $act->ip_address }}</small>
                                </div>
                            </div>
                            @empty
                            <p class="text-muted text-center py-3">No activity logs recorded.</p>
                            @endforelse
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>

<!-- Upload New Version Modal -->
<div class="modal fade" id="uploadVersionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.documents.versions.store', $document->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Upload New Version for: {{ $document->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Current active version is <strong>v{{ $document->version }}</strong>. Uploading a new file will preserve previous versions and increment version number.</p>

                    <div class="mb-3">
                        <label class="form-label required">Select Replacement File</label>
                        <input type="file" name="document_file" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Version Changelog / Notes</label>
                        <textarea name="changelog" class="form-control" rows="3" placeholder="Describe changes in this version..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Upload & Increment Version</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
