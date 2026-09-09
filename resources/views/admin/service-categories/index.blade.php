@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Service Categories</h4>
            <small class="text-muted">Manage your service categories</small>
        </div>
        @can('create service categories')
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="bx bx-plus me-1"></i> Add Category
        </button>
        @endcan
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive text-nowrap">
<table class="table table-hover" id="categoriesTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Services Count</th>
                            @if(auth()->user()->can('edit service categories') || auth()->user()->can('delete service categories'))
                            <th>Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                        <tr>
                            <td class="fw-semibold">{{ $category->name }}</td>
                            <td>{{ Str::limit($category->description, 50) }}</td>
                            <td>
                                @can('edit service categories')
                                <button class="btn btn-sm toggle-status {{ $category->status ? 'btn-success' : 'btn-secondary' }}" data-url="{{ route('admin.service-categories.toggle-status', $category->id) }}" data-status="{{ $category->status }}">
                                    {{ $category->status ? 'Active' : 'Inactive' }}
                                </button>
                                @else
                                <span class="badge {{ $category->status ? 'bg-label-success' : 'bg-label-secondary' }}">
                                    {{ $category->status ? 'Active' : 'Inactive' }}
                                </span>
                                @endcan
                            </td>
                            <td><span class="badge bg-label-primary">{{ $category->services_count ?? 0 }}</span></td>
                            @if(auth()->user()->can('edit service categories') || auth()->user()->can('delete service categories'))
                            <td>
                                <div class="dropdown">
                                    <button class="btn p-0 dropdown-toggle hide-arrow" type="button" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @can('edit service categories')
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editCategoryModal"
                                               data-id="{{ $category->id }}"
                                               data-name="{{ $category->name }}"
                                               data-description="{{ $category->description }}"
                                               data-status="{{ $category->status }}">
                                                <i class="bx bx-edit-alt me-1"></i> Edit
                                            </a>
                                        </li>
                                        @endcan
                                        @can('delete service categories')
                                        <li>
                                            <a class="dropdown-item text-danger delete-category" href="javascript:void(0);" data-id="{{ $category->id }}">
                                                <i class="bx bx-trash me-1"></i> Delete
                                            </a>
                                        </li>
                                        @endcan
                                    </ul>
                                </div>
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $categories->links() }}</div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Service Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addCategoryForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveCategoryBtn">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Service Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editCategoryForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" id="edit_category_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="edit_category_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="edit_category_description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="updateCategoryBtn">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('#categoriesTable').DataTable({
            paging: false,
            info: false,
            order: [[0, 'asc']]
        });

        $('#editCategoryModal').on('show.bs.modal', function(e) {
            const btn = $(e.relatedTarget);
            $('#edit_category_id').val(btn.data('id'));
            $('#edit_category_name').val(btn.data('name'));
            $('#edit_category_description').val(btn.data('description'));
        });

        $('#addCategoryForm').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#saveCategoryBtn');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
            $.ajax({
                url: '{{ route("admin.service-categories.store") }}',
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    if (res.success) {
                        $('#addCategoryModal').modal('hide');
                        showSuccessToast(res.message || 'Category created successfully.');
                        setTimeout(() => location.reload(), 1000);
                    }
                },
                error: function(xhr) {
                    const err = xhr.responseJSON?.message || 'Something went wrong.';
                    showErrorToast(err);
                },
                complete: function() {
                    btn.prop('disabled', false).text('Save');
                }
            });
        });

        $('#editCategoryForm').on('submit', function(e) {
            e.preventDefault();
            const id = $('#edit_category_id').val();
            const btn = $('#updateCategoryBtn');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Updating...');
            $.ajax({
                url: '{{ route("admin.service-categories.update", "") }}/' + id,
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    if (res.success) {
                        $('#editCategoryModal').modal('hide');
                        showSuccessToast(res.message || 'Category updated successfully.');
                        setTimeout(() => location.reload(), 1000);
                    }
                },
                error: function(xhr) {
                    const err = xhr.responseJSON?.message || 'Something went wrong.';
                    showErrorToast(err);
                },
                complete: function() {
                    btn.prop('disabled', false).text('Update');
                }
            });
        });

        $(document).on('click', '.toggle-status', function() {
            var btn = $(this);
            var url = btn.data('url');
            var status = btn.data('status');
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to " + (status ? 'deactivate' : 'activate') + " this category?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Yes, change it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(res) {
                            if (res.success) {
                                btn.toggleClass('btn-success btn-secondary');
                                btn.text(res.status ? 'Active' : 'Inactive');
                                btn.data('status', res.status);
                                showSuccessToast(res.message);
                                setTimeout(() => location.reload(), 1000);
                            }
                        },
                        error: function() {
                            showErrorToast('Something went wrong');
                        }
                    });
                }
            });
        });

        $('.delete-category').on('click', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("admin.service-categories.destroy", "") }}/' + id,
                        method: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(res) {
                            if (res.success) {
                                showSuccessToast(res.message || 'Category deleted successfully.');
                                setTimeout(() => location.reload(), 1000);
                            }
                        },
                        error: function() {
                            showErrorToast('Failed to delete category.');
                        }
                    });
                }
            });
        });
    });
</script>
@endsection
