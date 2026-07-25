@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1">
                <span class="text-muted fw-light">Document Management /</span> Document Categories
            </h4>
            <p class="text-muted mb-0">Master categories for organizing company, HR, transport, vehicle, and legal documents.</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
            <i class="bx bx-plus me-1"></i> Add New Category
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
                        <th>Category Name</th>
                        <th>Parent Category</th>
                        <th>Description</th>
                        <th>Total Documents</th>
                        <th>Sort Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $index => $cat)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <strong class="text-primary"><i class="bx bx-folder me-1"></i> {{ $cat->name }}</strong>
                        </td>
                        <td>{{ $cat->parent ? $cat->parent->name : '-' }}</td>
                        <td><small>{{ Str::limit($cat->description, 40) }}</small></td>
                        <td><span class="badge bg-label-info fs-7">{{ $cat->documents_count }} Documents</span></td>
                        <td>{{ $cat->sort_order }}</td>
                        <td>
                            @if($cat->status === 'active')
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-icon btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editCategoryModal{{ $cat->id }}">
                                <i class="bx bx-edit-alt"></i>
                            </button>
                            <form action="{{ route('admin.documents.categories.destroy', $cat->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-icon btn-sm btn-outline-danger">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editCategoryModal{{ $cat->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('admin.documents.categories.update', $cat->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title fw-bold">Edit Category: {{ $cat->name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label required">Category Name</label>
                                            <input type="text" name="name" class="form-control" value="{{ $cat->name }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Parent Category</label>
                                            <select name="parent_id" class="form-select">
                                                <option value="">None (Top Level Category)</option>
                                                @foreach($parentCategories as $parent)
                                                    @if($parent->id != $cat->id)
                                                    <option value="{{ $parent->id }}" {{ $cat->parent_id == $parent->id ? 'selected' : '' }}>
                                                        {{ $parent->name }}
                                                    </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Description</label>
                                            <textarea name="description" class="form-control" rows="2">{{ $cat->description }}</textarea>
                                        </div>
                                        <div class="row">
                                            <div class="col-6 mb-3">
                                                <label class="form-label">Sort Order</label>
                                                <input type="number" name="sort_order" class="form-control" value="{{ $cat->sort_order }}">
                                            </div>
                                            <div class="col-6 mb-3">
                                                <label class="form-label">Status</label>
                                                <select name="status" class="form-select">
                                                    <option value="active" {{ $cat->status === 'active' ? 'selected' : '' }}>Active</option>
                                                    <option value="inactive" {{ $cat->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Update Category</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No categories created yet. Click "Add New Category" to create one.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Category Modal -->
<div class="modal fade" id="createCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.documents.categories.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Create Document Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Category Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. HR Documents, Legal, GST, Insurance" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Parent Category</label>
                        <select name="parent_id" class="form-select">
                            <option value="">None (Top Level Category)</option>
                            @foreach($parentCategories as $parent)
                            <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Brief details about files under this category..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="0">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
