@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h5 class="mb-0">Permissions</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0 mt-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Permissions</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary"><i class="bx bx-plus me-1"></i> Add Permission</a>
    </div>

    <div class="card">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr><th>#</th><th>Name</th><th>Group</th><th class="text-center">Roles</th><th class="text-nowrap">Created</th><th class="text-nowrap">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($permissions as $index => $permission)
                    <tr>
                        <td>{{ $permissions->firstItem() + $index }}</td>
                        <td><code>{{ $permission->name }}</code></td>
                        <td>{{ $permission->group ?: '-' }}</td>
                        <td class="text-center">{{ $permission->roles->count() }}</td>
                        <td>{{ $permission->created_at->format('d M Y') }}</td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('admin.permissions.edit', $permission->id) }}"><i class="bx bx-edit me-1"></i> Edit</a>
                                    @if($permission->roles->count() == 0)
                                    <button type="button" class="dropdown-item text-danger" onclick="handleDelete({{ $permission->id }}, '{{ $permission->name }}')"><i class="bx bx-trash me-1"></i> Delete</button>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-4 text-muted">No permissions found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $permissions->links() }}</div>
    </div>
</div>

<form id="delete-form" method="POST" style="display: none;">@csrf @method('DELETE')</form>
@endsection

@section('script')
<script>
    function handleDelete(id, name) {
        Swal.fire({ title: 'Delete Permission?', text: "This will delete permission '" + name + "'!", icon: 'error', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6', confirmButtonText: 'Yes, delete it!' }).then((result) => {
            if (result.isConfirmed) { const form = document.getElementById('delete-form'); form.action = "{{ url('admin/permissions') }}/" + id; form.submit(); }
        })
    }
</script>
@endsection
