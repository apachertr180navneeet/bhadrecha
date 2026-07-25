@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h5 class="mb-0">Drivers</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0 mt-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item">Masters</li>
                    <li class="breadcrumb-item active">Recycle Bin</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.masters.drivers.index') }}" class="btn btn-secondary"><i class="bx bx-arrow-back me-1"></i> Back to List</a>
            <a href="{{ route('admin.masters.drivers.create') }}" class="btn btn-primary"><i class="bx bx-plus me-1"></i> Add Driver</a>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr><th>#</th><th>Driver ID</th><th>Name</th><th>Phone</th><th>License Number</th><th>License Expiry</th><th>City</th><th class="text-nowrap">Deleted At</th><th class="text-nowrap">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($drivers as $index => $driver)
                    <tr>
                        <td>{{ $drivers->firstItem() + $index }}</td>
                        <td>{{ $driver->driver_id ?? '-' }}</td>
                        <td><strong>{{ $driver->name }}</strong></td>
                        <td>{{ $driver->phone }}</td>
                        <td>{{ $driver->license_number }}</td>
                        <td>{{ $driver->license_expiry ? \Carbon\Carbon::parse($driver->license_expiry)->format('d-m-Y') : '-' }}</td>
                        <td>{{ $driver->city ?? '-' }}</td>
                        <td>{{ $driver->deleted_at->format('d M Y, h:i A') }}</td>
                        <td class="text-center text-nowrap">
                            <button type="button" class="btn btn-sm btn-icon btn-outline-success" onclick="handleRestore({{ $driver->id }}, '{{ $driver->name }}')" title="Restore"><i class="bx bx-revision"></i></button>
                            <button type="button" class="btn btn-sm btn-icon btn-outline-danger" onclick="handleForceDelete({{ $driver->id }}, '{{ $driver->name }}')" title="Permanently Delete"><i class="bx bx-trash"></i></button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center py-4 text-muted">No drivers in recycle bin</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $drivers->links() }}</div>
    </div>
</div>

<form id="force-delete-form" method="POST" style="display: none;">@csrf @method('DELETE')</form>
<form id="restore-form" method="POST" style="display: none;">@csrf @method('PUT')</form>
@endsection

@section('script')
<script>
    function handleRestore(id, name) {
        Swal.fire({ title: 'Restore Driver?', text: "Are you sure you want to restore '" + name + "'?", icon: 'question', showCancelButton: true, confirmButtonColor: '#696cff', cancelButtonColor: '#3085d6', confirmButtonText: 'Yes, restore it!' }).then((result) => {
            if (result.isConfirmed) { const form = document.getElementById('restore-form'); form.action = "{{ url('admin/masters/drivers') }}/" + id + "/restore"; form.submit(); }
        })
    }

    function handleForceDelete(id, name) {
        Swal.fire({ title: 'Permanently Delete?', text: "This will permanently delete '" + name + "'!", icon: 'error', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6', confirmButtonText: 'Yes, permanently delete!' }).then((result) => {
            if (result.isConfirmed) { const form = document.getElementById('force-delete-form'); form.action = "{{ url('admin/masters/drivers') }}/" + id + "/force-delete"; form.submit(); }
        })
    }
</script>
@endsection
