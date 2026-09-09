@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Durations</h4>
            <small class="text-muted">Manage your durations</small>
        </div>
        @can('create service durations')
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDurationModal">
            <i class="bx bx-plus me-1"></i> Add Duration
        </button>
        @endcan
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive text-nowrap">
                <table class="table table-hover" id="durationsTable">
                    <thead>
                        <tr>
                            @if(auth()->user()->can('edit service durations') || auth()->user()->can('delete service durations'))
                            <th class="text-nowrap" style="width: 80px;">Actions</th>
                            @endif
                            <th>Name</th>
                            <th>Days</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($durations as $duration)
                        <tr>
                            @if(auth()->user()->can('edit service durations') || auth()->user()->can('delete service durations'))
                            <td>
                                <div class="dropdown">
                                    <button class="btn p-0 dropdown-toggle hide-arrow" type="button" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        @can('edit service durations')
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editDurationModal"
                                               data-id="{{ $duration->id }}"
                                               data-name="{{ $duration->name }}"
                                               data-days="{{ $duration->days }}"
                                               data-status="{{ $duration->status }}">
                                                <i class="bx bx-edit-alt me-1"></i> Edit
                                            </a>
                                        </li>
                                        @endcan
                                        @can('delete service durations')
                                        <li>
                                            <a class="dropdown-item text-danger delete-duration" href="javascript:void(0);" data-id="{{ $duration->id }}">
                                                <i class="bx bx-trash me-1"></i> Delete
                                            </a>
                                        </li>
                                        @endcan
                                    </ul>
                                </div>
                            </td>
                            @endif
                            <td class="fw-semibold">{{ $duration->name }}</td>
                            <td>{{ $duration->days ?? '-' }}</td>
                            <td>
                                @can('edit service durations')
                                <button class="btn btn-sm toggle-status {{ $duration->status ? 'btn-success' : 'btn-secondary' }}" data-url="{{ route('admin.durations.toggle-status', $duration->id) }}" data-status="{{ $duration->status }}">
                                    {{ $duration->status ? 'Active' : 'Inactive' }}
                                </button>
                                @else
                                <span class="badge {{ $duration->status ? 'bg-label-success' : 'bg-label-secondary' }}">
                                    {{ $duration->status ? 'Active' : 'Inactive' }}
                                </span>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $durations->links() }}</div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addDurationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Duration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addDurationForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" placeholder="e.g. 3 Days" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Days</label>
                        <input type="number" class="form-control" name="days" placeholder="e.g. 1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveDurationBtn">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editDurationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Duration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editDurationForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" id="edit_duration_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="edit_duration_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Days</label>
                        <input type="number" class="form-control" name="days" id="edit_duration_days">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="updateDurationBtn">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('#durationsTable').DataTable({
            paging: false,
            info: false,
            order: [[0, 'asc']]
        });

        $('#editDurationModal').on('show.bs.modal', function(e) {
            const btn = $(e.relatedTarget);
            $('#edit_duration_id').val(btn.data('id'));
            $('#edit_duration_name').val(btn.data('name'));
            $('#edit_duration_days').val(btn.data('days'));
        });

        $('#addDurationForm').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#saveDurationBtn');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
            $.ajax({
                url: '{{ route("admin.durations.store") }}',
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    if (res.success) {
                        $('#addDurationModal').modal('hide');
                        showSuccessToast(res.message || 'Duration created successfully.');
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

        $('#editDurationForm').on('submit', function(e) {
            e.preventDefault();
            const id = $('#edit_duration_id').val();
            const btn = $('#updateDurationBtn');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Updating...');
            $.ajax({
                url: '{{ route("admin.durations.update", "") }}/' + id,
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    if (res.success) {
                        $('#editDurationModal').modal('hide');
                        showSuccessToast(res.message || 'Duration updated successfully.');
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
                text: "You want to " + (status ? 'deactivate' : 'activate') + " this duration?",
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

        $('.delete-duration').on('click', function() {
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
                        url: '{{ route("admin.durations.destroy", "") }}/' + id,
                        method: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(res) {
                            if (res.success) {
                                showSuccessToast(res.message || 'Duration deleted successfully.');
                                setTimeout(() => location.reload(), 1000);
                            }
                        },
                        error: function() {
                            showErrorToast('Failed to delete duration.');
                        }
                    });
                }
            });
        });
    });
</script>
@endsection
