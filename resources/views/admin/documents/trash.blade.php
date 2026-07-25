@extends('admin.layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1">
                <span class="text-muted fw-light">Document Management /</span> Trash Bin
            </h4>
            <p class="text-muted mb-0">Documents moved to trash can be restored or permanently removed by Super Admin.</p>
        </div>
        <a href="{{ route('admin.documents.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Back to Active Documents
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Doc Number</th>
                        <th>Document Name</th>
                        <th>Category</th>
                        <th>File Size</th>
                        <th>Deleted Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($trashedDocuments as $doc)
                    <tr>
                        <td><span class="fw-bold text-danger">{{ $doc->document_number }}</span></td>
                        <td>{{ $doc->name }}</td>
                        <td><span class="badge bg-label-info">{{ $doc->category?->name }}</span></td>
                        <td>{{ $doc->formatted_file_size }}</td>
                        <td>{{ $doc->deleted_at->format('d M Y, h:i A') }}</td>
                        <td>
                            <form action="{{ route('admin.documents.restore', $doc->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-sm btn-outline-success">
                                    <i class="bx bx-undo me-1"></i> Restore
                                </button>
                            </form>

                            @if(auth()->user()->isSuperAdmin())
                            <form action="{{ route('admin.documents.force-delete', $doc->id) }}" method="POST" class="d-inline" onsubmit="return confirm('PERMANENT DELETE: This file will be destroyed forever. Proceed?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger ms-1">
                                    <i class="bx bx-x-circle me-1"></i> Delete Permanently
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Trash bin is currently empty.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-end">
            {{ $trashedDocuments->links() }}
        </div>
    </div>
</div>
@endsection
