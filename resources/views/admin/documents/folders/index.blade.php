@extends('admin.layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1">
                <span class="text-muted fw-light">Document Management /</span> Nested Folders
            </h4>
            <p class="text-muted mb-0">Create unlimited nested folder hierarchies for structured document organization.</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createFolderModal">
            <i class="bx bx-folder-plus me-1"></i> Create Folder
        </button>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Folder Name & Path</th>
                        <th>Associated Category</th>
                        <th>Parent Folder</th>
                        <th>Branch</th>
                        <th>Total Documents</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($folders as $index => $folder)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <strong class="text-primary"><i class="bx bx-folder-open me-1"></i> {{ $folder->name }}</strong>
                            <div class="small text-muted">{{ $folder->full_path }}</div>
                        </td>
                        <td>{{ $folder->category ? $folder->category->name : 'General' }}</td>
                        <td>{{ $folder->parent ? $folder->parent->name : 'Root Level' }}</td>
                        <td>{{ $folder->branch ? $folder->branch->name : 'All Branches' }}</td>
                        <td><span class="badge bg-label-info">{{ $folder->documents_count }} Documents</span></td>
                        <td>
                            @if($folder->status === 'active')
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-icon btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editFolderModal{{ $folder->id }}">
                                <i class="bx bx-edit-alt"></i>
                            </button>
                            <form action="{{ route('admin.documents.folders.destroy', $folder->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Deleting folder will unassign documents in it. Continue?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-icon btn-sm btn-outline-danger">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editFolderModal{{ $folder->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('admin.documents.folders.update', $folder->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title fw-bold">Edit Folder: {{ $folder->name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label required">Folder Name</label>
                                            <input type="text" name="name" class="form-control" value="{{ $folder->name }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Parent Folder</label>
                                            <select name="parent_id" class="form-select">
                                                <option value="">Root Level</option>
                                                @foreach($allFolders as $f)
                                                    @if($f->id != $folder->id)
                                                    <option value="{{ $f->id }}" {{ $folder->parent_id == $f->id ? 'selected' : '' }}>
                                                        {{ $f->full_path }}
                                                    </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Category</label>
                                            <select name="category_id" class="form-select">
                                                <option value="">Select Category (Optional)</option>
                                                @foreach($categories as $cat)
                                                <option value="{{ $cat->id }}" {{ $folder->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Branch</label>
                                            <select name="branch_id" class="form-select">
                                                <option value="">All Branches</option>
                                                @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}" {{ $folder->branch_id == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <select name="status" class="form-select">
                                                <option value="active" {{ $folder->status === 'active' ? 'selected' : '' }}>Active</option>
                                                <option value="inactive" {{ $folder->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Update Folder</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No folders created yet. Click "Create Folder" to build custom folder structures.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Folder Modal -->
<div class="modal fade" id="createFolderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.documents.folders.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Create New Folder</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Folder Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Accounts, HR, Employees, RC, Insurance" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Parent Folder (for Nested Folders)</label>
                        <select name="parent_id" class="form-select">
                            <option value="">Root Level (Top Level Folder)</option>
                            @foreach($allFolders as $f)
                            <option value="{{ $f->id }}">{{ $f->full_path }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Associated Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">Select Category (Optional)</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Branch</label>
                        <select name="branch_id" class="form-select">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Folder description..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Folder</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
