@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Services</h4>
            <small class="text-muted">Manage all salon services</small>
        </div>
        @can('create services')
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
            <i class="bx bx-plus me-1"></i> Add Service
        </button>
        @endcan
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive text-nowrap">
<table class="table table-hover" id="servicesTable">
                    <thead>
                        <tr>
                            @if(auth()->user()->can('edit services') || auth()->user()->can('delete services'))
                            <th class="text-center text-nowrap" style="width: 80px;">Actions</th>
                            @endif
                            <th>Service Name</th>
                            <th>Category</th>
                            <th>Duration (mins)</th>
                            <th>Price</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($services as $service)
                        <tr>
                            @if(auth()->user()->can('edit services') || auth()->user()->can('delete services'))
                            <td class="text-center text-nowrap">
                                <div class="dropdown">
                                    <button class="btn p-0 dropdown-toggle hide-arrow" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        @can('edit services')
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editServiceModal"
                                               data-id="{{ $service->id }}"
                                               data-name="{{ $service->service_name }}"
                                               data-category_id="{{ $service->category_id }}"
                                               data-duration="{{ $service->duration }}"
                                                data-price="{{ $service->price }}">
                                                <i class="bx bx-edit-alt me-1"></i> Edit
                                            </a>
                                        </li>
                                        @endcan
                                        @can('delete services')
                                        <li>
                                            <a class="dropdown-item text-danger delete-service" href="javascript:void(0);" data-id="{{ $service->id }}">
                                                <i class="bx bx-trash me-1"></i> Delete
                                            </a>
                                        </li>
                                        @endcan
                                    </ul>
                                </div>
                            </td>
                            @endif
                            <td class="fw-semibold">{{ $service->service_name }}</td>
                            <td>{{ $service->category->name ?? 'N/A' }}</td>
                            <td>{{ $service->duration }}</td>
                            <td>₹{{ number_format($service->price, 2) }}</td>
                            <td>
                                @can('edit services')
                                <button class="btn btn-sm toggle-status {{ $service->status ? 'btn-success' : 'btn-secondary' }}" data-url="{{ route('admin.services.toggle-status', $service->id) }}" data-status="{{ $service->status }}">
                                    {{ $service->status ? 'Active' : 'Inactive' }}
                                </button>
                                @else
                                <span class="badge {{ $service->status ? 'bg-label-success' : 'bg-label-secondary' }}">
                                    {{ $service->status ? 'Active' : 'Inactive' }}
                                </span>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $services->links() }}</div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addServiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addServiceForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Service Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="service_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select" name="category_id" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Duration (mins) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="duration" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Price <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" name="price" min="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveServiceBtn">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editServiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editServiceForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" id="edit_service_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Service Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="service_name" id="edit_service_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select" name="category_id" id="edit_service_category_id" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Duration (mins) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="duration" id="edit_service_duration" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Price <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" name="price" id="edit_service_price" min="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="updateServiceBtn">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        function clearErrors(formId) {
            $(formId + ' .is-invalid').removeClass('is-invalid');
            $(formId + ' .invalid-feedback').remove();
        }

        $('#servicesTable').DataTable({
            paging: false,
            info: false,
            order: [[0, 'asc']]
        });

        $('#addServiceModal, #editServiceModal').on('hidden.bs.modal', function() {
            clearErrors('#' + $(this).find('form').attr('id'));
        });

        $('#editServiceModal').on('show.bs.modal', function(e) {
            const btn = $(e.relatedTarget);
            $('#edit_service_id').val(btn.data('id'));
            $('#edit_service_name').val(btn.data('name'));
            $('#edit_service_category_id').val(btn.data('category_id'));
            $('#edit_service_duration').val(btn.data('duration'));
            $('#edit_service_price').val(btn.data('price'));
        });

        $('#addServiceForm').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#saveServiceBtn');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
            $.ajax({
                url: '{{ route("admin.services.store") }}',
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    if (res.success) {
                        $('#addServiceModal').modal('hide');
                        showSuccessToast(res.message || 'Service created successfully.');
                        setTimeout(() => location.reload(), 1000);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        clearErrors('#addServiceForm');
                        $.each(xhr.responseJSON.errors, function(field, messages) {
                            const input = $('#addServiceForm').find('[name="' + field + '"]');
                            input.addClass('is-invalid');
                            input.after('<div class="invalid-feedback">' + messages[0] + '</div>');
                        });
                    } else {
                        showErrorToast(xhr.responseJSON?.message || 'Something went wrong.');
                    }
                },
                complete: function() {
                    btn.prop('disabled', false).text('Save');
                }
            });
        });

        $('#editServiceForm').on('submit', function(e) {
            e.preventDefault();
            clearErrors('#editServiceForm');
            const id = $('#edit_service_id').val();
            const btn = $('#updateServiceBtn');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Updating...');
            $.ajax({
                url: '{{ route("admin.services.update", "") }}/' + id,
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    if (res.success) {
                        $('#editServiceModal').modal('hide');
                        showSuccessToast(res.message || 'Service updated successfully.');
                        setTimeout(() => location.reload(), 1000);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        $.each(xhr.responseJSON.errors, function(field, messages) {
                            const input = $('#editServiceForm').find('[name="' + field + '"]');
                            input.addClass('is-invalid');
                            input.after('<div class="invalid-feedback">' + messages[0] + '</div>');
                        });
                    } else {
                        showErrorToast(xhr.responseJSON?.message || 'Something went wrong.');
                    }
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
                text: "You want to " + (status ? 'deactivate' : 'activate') + " this service?",
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

        $('.delete-service').on('click', function() {
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
                        url: '{{ route("admin.services.destroy", "") }}/' + id,
                        method: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(res) {
                            if (res.success) {
                                showSuccessToast(res.message || 'Service deleted successfully.');
                                setTimeout(() => location.reload(), 1000);
                            }
                        },
                        error: function() {
                            showErrorToast('Failed to delete service.');
                        }
                    });
                }
            });
        });
    });
</script>
@endsection
