@extends('admin.layouts.app') 

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h5 class="card-title mb-0">Item Master</h5>
                        <small class="text-muted">Manage item master catalog</small>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        @can('create items')
                        <button type="button" class="btn btn-primary" id="btnCreateItem">
                            <i class="bx bx-plus me-1"></i> Add Item
                        </button>
                        @endcan
                    </div>
                </div>

                <!-- Filters -->
                <div class="card-body border-bottom">
                    <form method="GET" action="{{ route('admin.items.index') }}" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Search by item name..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-6 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-search me-1"></i> Search
                            </button>
                            <a href="{{ route('admin.items.index') }}" class="btn btn-outline-secondary">
                                <i class="bx bx-reset me-1"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>

                <div class="card-body">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover" id="itemsTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    @if(auth()->user()->can('edit items') || auth()->user()->can('delete items'))
                                    <th class="text-nowrap" style="width: 80px;">Actions</th>
                                    @endif
                                    <th>Name</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $item)
                                <tr>
                                    <td>{{ $loop->iteration + ($items->currentPage() - 1) * $items->perPage() }}</td>
                                    @if(auth()->user()->can('edit items') || auth()->user()->can('delete items'))
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn p-0 dropdown-toggle hide-arrow" type="button" data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                @can('edit items')
                                                <li>
                                                    <a class="dropdown-item btn-edit-item" href="javascript:void(0);" data-id="{{ $item->id }}">
                                                        <i class="bx bx-edit-alt me-1"></i> Edit
                                                    </a>
                                                </li>
                                                @endcan
                                                @can('delete items')
                                                <li>
                                                    <a class="dropdown-item text-danger btn-delete-item" href="javascript:void(0);" data-id="{{ $item->id }}" data-name="{{ $item->name }}">
                                                        <i class="bx bx-trash me-1"></i> Delete
                                                    </a>
                                                </li>
                                                @endcan
                                            </ul>
                                        </div>
                                    </td>
                                    @endif
                                    <td>
                                        <span class="fw-bold text-dark">{{ $item->name }}</span>
                                    </td>
                                    <td>
                                        @if(auth()->user()->can('edit items'))
                                        <div class="form-check form-switch">
                                            <input class="form-check-input toggle-status" type="checkbox" data-id="{{ $item->id }}" {{ $item->status == 1 ? 'checked' : '' }}>
                                            <label class="form-check-label small">{{ $item->status == 1 ? 'Active' : 'Inactive' }}</label>
                                        </div>
                                        @else
                                        <span class="badge bg-label-{{ $item->status == 1 ? 'success' : 'danger' }}">
                                            {{ $item->status == 1 ? 'Active' : 'Inactive' }}
                                        </span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <div class="text-muted">No items found.</div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 d-flex justify-content-end">
                        {{ $items->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Item Modal -->
<div class="modal fade" id="itemModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="itemModalTitle">Add New Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="itemForm">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="id" id="itemId">

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="itemName">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="itemName" placeholder="Enter item name" required>
                        <div class="invalid-feedback" id="error-name"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="itemStatus">Status <span class="text-danger">*</span></label>
                        <select class="form-select" name="status" id="itemStatus" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                        <div class="invalid-feedback" id="error-status"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveItem">
                        <span class="spinner-border spinner-border-sm d-none me-1" id="saveSpinner" role="status"></span>
                        Save Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection @section('script')
<script>
$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function resetForm() {
        $('#itemForm')[0].reset();
        $('#itemId').val('');
        $('#formMethod').val('POST');
        $('#itemModalTitle').text('Add New Item');
        $('.form-control, .form-select').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    }

    $('#btnCreateItem').on('click', function() {
        resetForm();
        $('#itemModal').modal('show');
    });

    $(document).on('click', '.btn-edit-item', function() {
        resetForm();
        const id = $(this).data('id');
        $('#itemModalTitle').text('Edit Item');
        $('#itemId').val(id);
        $('#formMethod').val('PUT');

        $.ajax({
            url: `{{ url('admin/items') }}/${id}/edit`,
            type: 'GET',
            success: function(data) {
                $('#itemName').val(data.name);
                $('#itemStatus').val(data.status);
                $('#itemModal').modal('show');
            },
            error: function(xhr) {
                Swal.fire('Error', 'Failed to fetch item details', 'error');
            }
        });
    });

    $('#itemForm').on('submit', function(e) {
        e.preventDefault();
        $('.form-control, .form-select').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        const id = $('#itemId').val();
        const method = $('#formMethod').val();
        const url = method === 'PUT' ? `{{ url('admin/items') }}/${id}` : `{{ route('admin.items.store') }}`;

        $('#btnSaveItem').prop('disabled', true);
        $('#saveSpinner').removeClass('d-none');

        $.ajax({
            url: url,
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#btnSaveItem').prop('disabled', false);
                $('#saveSpinner').addClass('d-none');
                $('#itemModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                $('#btnSaveItem').prop('disabled', false);
                $('#saveSpinner').addClass('d-none');
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    $.each(errors, function(field, messages) {
                        const input = $(`[name="${field}"]`);
                        input.addClass('is-invalid');
                        $(`#error-${field}`).text(messages[0]);
                    });
                } else {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Something went wrong', 'error');
                }
            }
        });
    });

    $(document).on('change', '.toggle-status', function() {
        const id = $(this).data('id');
        const checkbox = $(this);
        const isChecked = checkbox.is(':checked');

        $.ajax({
            url: `{{ url('admin/items') }}/${id}/toggle-status`,
            type: 'POST',
            success: function(response) {
                checkbox.next('label').text(response.status == 1 ? 'Active' : 'Inactive');
                toastr?.success ? toastr.success(response.message) : null;
            },
            error: function() {
                checkbox.prop('checked', !isChecked);
                Swal.fire('Error', 'Could not update status', 'error');
            }
        });
    });

    $(document).on('click', '.btn-delete-item', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');

        Swal.fire({
            title: 'Are you sure?',
            text: `Do you want to delete item "${name}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `{{ url('admin/items') }}/${id}`,
                    type: 'DELETE',
                    success: function(response) {
                        Swal.fire('Deleted!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Failed to delete item', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endsection
