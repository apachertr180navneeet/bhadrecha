@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Stores</h5>
                    @can('create stores')
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#storeModal">Add Store</button>
                    @endcan
                </div>
                <div class="card-body">
                    <div class="table-responsive text-nowrap">
                    <table class="table table-hover" id="storesTable">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Store Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Status</th>
                                @if(auth()->user()->can('edit stores') || auth()->user()->can('delete stores'))
                                <th>Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stores as $store)
                            <tr data-id="{{ $store->id }}">
                                <td></td>
                                <td>{{ $store->store_name }}</td>
                                <td>{{ $store->phone }}</td>
                                <td>{{ $store->email }}</td>
                                <td>
                                    @can('edit stores')
                                    <button class="btn btn-sm toggle-status {{ $store->status ? 'btn-success' : 'btn-secondary' }}" data-status="{{ $store->status }}">
                                        {{ $store->status ? 'Active' : 'Inactive' }}
                                    </button>
                                    @else
                                    <span class="badge {{ $store->status ? 'bg-label-success' : 'bg-label-secondary' }}">
                                        {{ $store->status ? 'Active' : 'Inactive' }}
                                    </span>
                                    @endcan
                                </td>
                                @if(auth()->user()->can('edit stores') || auth()->user()->can('delete stores'))
                                <td>
                                    <div class="dropdown">
                                        <button class="btn p-0 dropdown-toggle hide-arrow" type="button" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            @can('edit stores')
                                            <li>
                                                <a class="dropdown-item edit-store" href="javascript:void(0);">
                                                    <i class="bx bx-edit-alt me-1"></i> Edit
                                                </a>
                                            </li>
                                            @endcan
                                            @can('delete stores')
                                            <li>
                                                <a class="dropdown-item text-danger delete-store" href="javascript:void(0);">
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
                    <div class="mt-3">
                        {{ $stores->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="storeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="storeForm" method="POST">
                @csrf
                <input type="hidden" name="id" id="storeId">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Store</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Store Name <span class="text-danger">*</span></label>
                        <input type="text" name="store_name" id="store_name" class="form-control">
                        <span class="text-danger small error-store_name"></span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" id="phone" class="form-control">
                        <span class="text-danger small error-phone"></span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control">
                        <span class="text-danger small error-email"></span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" id="address" class="form-control" rows="2"></textarea>
                        <span class="text-danger small error-address"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    var table = $('#storesTable').DataTable({
        paging: false,
        info: false,
        lengthChange: false,
        order: [[1, 'asc']],
        columnDefs: [{ targets: 0, orderable: false }],
        drawCallback: function() {
            var api = this.api();
            api.column(0, { search: 'applied', order: 'applied' }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1;
            });
        }
    });

    $('#storeForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        var id = $('#storeId').val();
        var url = id ? '{{ route('admin.stores.update', ':id') }}'.replace(':id', id) : '{{ route('admin.stores.store') }}';
        var type = id ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            type: 'POST',
            data: formData + '&_method=' + type,
            success: function(res) {
                $('#storeModal').modal('hide');
                $('#storeForm')[0].reset();
                setFlesh('success', res.message || 'Store saved successfully');
                location.reload();
            },
            error: function(xhr) {
                $('#storeForm .text-danger.small').empty();
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    var errors = xhr.responseJSON.errors;
                    $.each(errors, function(field, messages) {
                        $('.error-' + field).text(messages[0]);
                    });
                } else {
                    var err = xhr.responseJSON?.message || 'Something went wrong';
                    setFlesh('error', err);
                }
            }
        });
    });

    $('#storesTable tbody').on('click', '.edit-store', function() {
        var id = $(this).closest('tr').attr('data-id');
        var url = '{{ route('admin.stores.edit', ':id') }}'.replace(':id', id);
        $.ajax({ url: url, dataType: 'json', success: function(data) {
            $('#modalTitle').text('Edit Store');
            $('#storeId').val(data.id);
            $('#store_name').val(data.store_name);
            $('#phone').val(data.phone);
            $('#email').val(data.email);
            $('#status').val(data.status == 1 ? 'active' : 'inactive');
            $('#address').val(data.address);
            $('#storeModal').modal('show');
        } });
    });

    $('#storesTable tbody').on('click', '.delete-store', function() {
        var btn = $(this);
        var id = btn.closest('tr').attr('data-id');
        var url = '{{ route('admin.stores.destroy', ':id') }}'.replace(':id', id);
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
                    success: function(res) {
                        btn.closest('tr').remove();
                        setFlesh('success', res.message || 'Store deleted');
                    }
                });
            }
        });
    });

    $('#storesTable tbody').on('click', '.toggle-status', function() {
        var btn = $(this);
        var id = btn.closest('tr').attr('data-id');
        var url = '{{ route('admin.stores.toggle-status', ':id') }}'.replace(':id', id);
        var status = $(this).attr('data-status');
        Swal.fire({
            title: 'Are you sure?',
            text: "You want to " + (status == 1 ? 'deactivate' : 'activate') + " this store?",
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
                            btn.attr('data-status', res.status);
                            setFlesh('success', res.message);
                        }
                    },
                    error: function(xhr) {
                        setFlesh('error', 'Something went wrong');
                    }
                });
            }
        });
    });

    $('#storeModal').on('hidden.bs.modal', function() {
        $('#modalTitle').text('Add Store');
        $('#storeForm')[0].reset();
        $('#storeId').val('');
        $('#status').val('active');
        $('#storeForm .text-danger.small').empty();
    });
});
</script>
@endsection
