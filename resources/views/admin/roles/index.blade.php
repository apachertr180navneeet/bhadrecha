@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h5 class="mb-0">Roles</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0 mt-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Roles</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary"><i class="bx bx-plus me-1"></i> Add Role</a>
    </div>

    <div class="card">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr><th>#</th><th>Name</th><th class="text-center">Permissions</th><th class="text-center">Users</th><th class="text-nowrap">Created</th><th class="text-nowrap">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($roles as $index => $role)
                    <tr>
                        <td>{{ $roles->firstItem() + $index }}</td>
                        <td><strong>{{ $role->name }}</strong></td>
                        <td class="text-center">{{ $role->permissions->count() }}</td>
                        <td class="text-center">{{ $role->users->count() }}</td>
                        <td>{{ $role->created_at->format('d M Y') }}</td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('admin.roles.edit', $role->id) }}"><i class="bx bx-edit me-1"></i> Edit</a>
                                    @if($role->users->count() == 0)
                                    <button type="button" class="dropdown-item text-danger" onclick="handleDelete({{ $role->id }}, '{{ $role->name }}')"><i class="bx bx-trash me-1"></i> Delete</button>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">No roles found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $roles->links() }}</div>
    </div>
</div>

<form id="delete-form" method="POST" style="display: none;">@csrf @method('DELETE')</form>
@endsection

@section('script')
<script>
    function handleDelete(id, name) {
        Swal.fire({ title: 'Delete Role?', text: "This will delete role '" + name + "'!", icon: 'error', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6', confirmButtonText: 'Yes, delete it!' }).then((result) => {
            if (result.isConfirmed) { const form = document.getElementById('delete-form'); form.action = "{{ url('admin/roles') }}/" + id; form.submit(); }
        })
    }
</script>
@endsection
