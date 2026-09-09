@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Staff Members</h4>
            <small class="text-muted">Manage all staff members</small>
        </div>
        @can('create staffs')
        <a href="{{ route('admin.staffs.create') }}" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i> Add Staff
        </a>
        @endcan
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive text-nowrap">
<table class="table table-hover" id="staffsTable">
                    <thead>
                        <tr>
                            @if(auth()->user()->can('edit staffs') || auth()->user()->can('delete staffs'))
                            <th class="text-center text-nowrap" style="width: 80px;">Actions</th>
                            @endif
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Position</th>
                            <th>Shift Time</th>
                            <th>Working Hours</th>
                            <th>Salary</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($staffs as $staff)
                        <tr>
                            @if(auth()->user()->can('edit staffs') || auth()->user()->can('delete staffs'))
                            <td class="text-center text-nowrap">
                                <div class="dropdown">
                                    <button class="btn p-0 dropdown-toggle hide-arrow" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        @can('edit staffs')
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.staffs.edit', $staff->id) }}">
                                                <i class="bx bx-edit-alt me-1"></i> Edit
                                            </a>
                                        </li>
                                        @endcan
                                        @can('delete staffs')
                                        <li>
                                            <a class="dropdown-item text-danger delete-staff" href="javascript:void(0);" data-url="{{ route('admin.staffs.destroy', $staff->id) }}">
                                                <i class="bx bx-trash me-1"></i> Delete
                                            </a>
                                        </li>
                                        @endcan
                                    </ul>
                                </div>
                            </td>
                            @endif
                            <td class="fw-semibold">{{ $staff->first_name }} {{ $staff->last_name }}</td>
                            <td>{{ $staff->email }}</td>
                            <td>{{ $staff->phone ?? 'N/A' }}</td>
                            <td>{{ $staff->position ?? 'N/A' }}</td>
                            <td>{{ $staff->shift_time ?? 'N/A' }}</td>
                            <td>{{ $staff->working_hours ?? 'N/A' }}</td>
                            <td>{{ $staff->salary ? '₹' . number_format($staff->salary, 2) : 'N/A' }}</td>
                            <td>
                                @can('edit staffs')
                                <button class="btn btn-sm toggle-status {{ $staff->status ? 'btn-success' : 'btn-secondary' }}" data-url="{{ route('admin.staffs.toggle-status', $staff->id) }}" data-status="{{ $staff->status }}">
                                    {{ $staff->status ? 'Active' : 'Inactive' }}
                                </button>
                                @else
                                <span class="badge {{ $staff->status ? 'bg-label-success' : 'bg-label-secondary' }}">
                                    {{ $staff->status ? 'Active' : 'Inactive' }}
                                </span>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $staffs->links() }}</div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('#staffsTable').DataTable({
            paging: false,
            info: false,
            order: [[0, 'asc']]
        });

        $(document).on('click', '.toggle-status', function() {
            var btn = $(this);
            var url = btn.data('url');
            var status = btn.data('status');
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to " + (status ? 'deactivate' : 'activate') + " this staff member?",
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
                                setFlesh('success', res.message);
                            }
                        },
                        error: function() {
                            setFlesh('error', 'Something went wrong');
                        }
                    });
                }
            });
        });

        $(document).on('click', '.delete-staff', function() {
            var url = $(this).data('url');
            Swal.fire({
                title: 'Are you sure?',
                text: 'This staff member will be permanently removed.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
                        success: function(res) {
                            if (res.success) {
                                setFlesh('success', res.message || 'Staff deleted successfully.');
                                location.reload();
                            }
                        },
                        error: function() {
                            setFlesh('error', 'Failed to delete staff member.');
                        }
                    });
                }
            });
        });
    });
</script>
@endsection
