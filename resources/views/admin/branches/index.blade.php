@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h5 class="mb-0">Branches</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0 mt-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Branches</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.branches.trashed') }}" class="btn btn-outline-danger"><i class="bx bx-trash me-1"></i> Recycle Bin </a>
            <a href="{{ route('admin.branches.create') }}" class="btn btn-primary"><i class="bx bx-plus me-1"></i> Add Branch</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body border-bottom py-3">
            <form method="GET" class="row g-2">
                <div class="col-12 col-md"><input type="text" name="search" class="form-control" placeholder="Search by name, email, phone..." value="{{ request('search') }}"></div>
                <div class="col-12 col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-12 col-md-auto"><button type="submit" class="btn btn-outline-secondary w-100"><i class="bx bx-search me-1"></i> Search</button></div>
                @if(request()->hasAny(['search','status']))
                <div class="col-12 col-md-auto"><a href="{{ route('admin.branches.index') }}" class="btn btn-outline-danger w-100"><i class="bx bx-x me-1"></i> Clear</a></div>
                @endif
            </form>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr><th>#</th><th>Name</th><th>Company</th><th>Email</th><th>Phone</th><th>City</th><th class="text-center">Users</th><th class="text-nowrap">Status</th><th class="text-nowrap">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($branches as $index => $branch)
                    <tr>
                        <td>{{ $branches->firstItem() + $index }}</td>
                        <td><strong>{{ $branch->name }}</strong></td>
                        <td>{{ $branch->company->name ?? '-' }}</td>
                        <td>{{ $branch->email ?? '-' }}</td>
                        <td>{{ $branch->phone ?? '-' }}</td>
                        <td>{{ $branch->city ?? '-' }}</td>
                        <td class="text-center">{{ $branch->users_count }}</td>
                        <td><span class="badge bg-label-{{ $branch->status == 'active' ? 'success' : 'danger' }}">{{ ucfirst($branch->status) }}</span></td>
                        <td class="text-center text-nowrap">
                            <a href="{{ route('admin.branches.show', $branch->id) }}" class="btn btn-sm btn-icon btn-outline-info" title="View"><i class="bx bx-show"></i></a>
                            <a href="{{ route('admin.branches.edit', $branch->id) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Edit"><i class="bx bx-edit"></i></a>
                            <form action="{{ route('admin.branches.toggle-status', $branch->id) }}" method="POST" class="d-inline">@csrf
                                <button type="submit" class="btn btn-sm btn-icon btn-outline-{{ $branch->status == 'active' ? 'warning' : 'success' }}" title="{{ $branch->status == 'active' ? 'Deactivate' : 'Activate' }}"><i class="bx bx-{{ $branch->status == 'active' ? 'pause' : 'play' }}"></i></button>
                            </form>
                            @if($branch->users_count == 0)
                            <button type="button" class="btn btn-sm btn-icon btn-outline-danger" onclick="handleDelete({{ $branch->id }}, '{{ $branch->name }}')" title="Delete"><i class="bx bx-trash"></i></button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center py-4 text-muted">No branches found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $branches->withQueryString()->links() }}</div>
    </div>
</div>

<form id="delete-form" method="POST" style="display: none;">@csrf @method('DELETE')</form>
@endsection

@section('script')
<script>
    function handleDelete(id, name) {
        Swal.fire({ title: 'Delete Branch?', text: "This will delete branch '" + name + "'!", icon: 'error', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6', confirmButtonText: 'Yes, delete it!' }).then((result) => {
            if (result.isConfirmed) { const form = document.getElementById('delete-form'); form.action = "{{ url('admin/branches') }}/" + id; form.submit(); }
        })
    }
</script>
@endsection
