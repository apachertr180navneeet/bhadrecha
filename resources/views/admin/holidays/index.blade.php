@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">Holiday Master</h5>
                        <small class="text-muted">Manage company holidays</small>
                    </div>
                    @can('create holidays')
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#holidayModal">
                        <i class="bx bx-plus me-1"></i> Add Holiday
                    </button>
                    @endcan
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover" id="holidaysTable">
                        <thead>
                            <tr>
                                @if(auth()->user()->can('edit holidays') || auth()->user()->can('delete holidays'))
                                <th class="text-nowrap" style="width: 80px;">Actions</th>
                                @endif
                                <th>Name</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($holidays as $holiday)
                            <tr id="holiday-row-{{ $holiday->id }}">
                                @if(auth()->user()->can('edit holidays') || auth()->user()->can('delete holidays'))
                                <td>
                                    <div class="dropdown">
                                        <button class="btn p-0 dropdown-toggle hide-arrow" type="button" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            @can('edit holidays')
                                            <li>
                                                <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#holidayModal"
                                                   data-id="{{ $holiday->id }}"
                                                   data-name="{{ $holiday->name }}"
                                                   data-date="{{ $holiday->date }}"
                                                   data-status="{{ $holiday->status ? 'active' : 'inactive' }}">
                                                    <i class="bx bx-edit-alt me-1"></i> Edit
                                                </a>
                                            </li>
                                            @endcan
                                            @can('delete holidays')
                                            <li>
                                                <a class="dropdown-item text-danger delete-holiday" href="javascript:void(0);" data-id="{{ $holiday->id }}">
                                                    <i class="bx bx-trash me-1"></i> Delete
                                                </a>
                                            </li>
                                            @endcan
                                        </ul>
                                    </div>
                                </td>
                                @endif
                                <td class="fw-semibold">{{ $holiday->name }}</td>
                                <td>{{ \Carbon\Carbon::parse($holiday->date)->format('M d, Y') }}</td>
                                <td>
                                    @if($holiday->status)
                                        <span class="badge bg-label-success">Active</span>
                                    @else
                                        <span class="badge bg-label-danger">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No holidays found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer d-flex justify-content-end">
                    {{ $holidays->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="holidayModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="holidayModalTitle">Add Holiday</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="holidayForm">
                @csrf
                <input type="hidden" name="id" id="holidayId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="holidayName">Holiday Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="holidayName" placeholder="E.g. Christmas Day" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="holidayDate">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="date" id="holidayDate" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="holidayStatus">Status <span class="text-danger">*</span></label>
                        <select class="form-select" name="status" id="holidayStatus" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="holidaySaveBtn">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    $('#holidayModal').on('show.bs.modal', function(e) {
        let btn = $(e.relatedTarget);
        let id = btn.data('id');
        let modal = $(this);

        $('#holidayForm')[0].reset();
        $('#holidayForm').removeClass('was-validated');
        $('.invalid-feedback').text('');
        $('#holidayForm').find('.is-invalid').removeClass('is-invalid');

        if (id) {
            modal.find('.modal-title').text('Edit Holiday');
            modal.find('#holidayId').val(id);
            modal.find('#holidayName').val(btn.data('name'));
            modal.find('#holidayDate').val(btn.data('date'));
            modal.find('#holidayStatus').val(btn.data('status'));
            modal.find('#holidaySaveBtn').text('Update');
        } else {
            modal.find('.modal-title').text('Add Holiday');
            modal.find('#holidayId').val('');
            modal.find('#holidaySaveBtn').text('Save');
        }
    });

    $('#holidayForm').on('submit', function(e) {
        e.preventDefault();
        let form = $(this);
        let id = $('#holidayId').val();
        let url = id ? '{{ route("admin.holidays.update", ":id") }}'.replace(':id', id) : '{{ route("admin.holidays.store") }}';
        let method = id ? 'PUT' : 'POST';

        let btn = form.find('button[type="submit"]');
        let originalText = btn.text();
        btn.text('Saving...').prop('disabled', true);

        $.ajax({
            url: url,
            method: method,
            data: form.serialize(),
            success: function(res) {
                if (res.success) {
                    $('#holidayModal').modal('hide');
                    showSuccessToast(res.message);
                    setTimeout(() => location.reload(), 1000);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, msg) {
                        let input = form.find('[name="' + key + '"]');
                        input.addClass('is-invalid');
                        input.siblings('.invalid-feedback').text(msg[0]);
                    });
                } else if (xhr.responseJSON && xhr.responseJSON.error) {
                    showErrorToast(xhr.responseJSON.error);
                } else {
                    showErrorToast('Failed to save holiday');
                }
                btn.text(originalText).prop('disabled', false);
            }
        });
    });

    $(document).on('click', '.delete-holiday', function() {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.holidays.destroy", ":id") }}'.replace(':id', id),
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        if (res.success) {
                            showSuccessToast(res.message);
                            $('#holiday-row-' + id).fadeOut();
                        }
                    },
                    error: function() {
                        showErrorToast('Failed to delete holiday');
                    }
                });
            }
        });
    });
});
</script>
@endsection
