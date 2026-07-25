@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h5 class="mb-0">Companies</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0 mt-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Recycle Bin</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.companies.index') }}" class="btn btn-secondary"><i class="bx bx-arrow-back me-1"></i> Back to List</a>
            <a href="{{ route('admin.companies.create') }}" class="btn btn-primary"><i class="bx bx-plus me-1"></i> Add Company</a>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th class="text-nowrap">Deleted At</th><th class="text-nowrap">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($companies as $index => $company)
                    <tr>
                        <td>{{ $companies->firstItem() + $index }}</td>
                        <td><strong>{{ $company->name }}</strong></td>
                        <td>{{ $company->email ?? '-' }}</td>
                        <td>{{ $company->phone ?? '-' }}</td>
                        <td>{{ $company->deleted_at->format('d M Y, h:i A') }}</td>
                        <td class="text-center text-nowrap">
                            <button type="button" class="btn btn-sm btn-icon btn-outline-success" onclick="handleRestore({{ $company->id }}, '{{ $company->name }}')" title="Restore"><i class="bx bx-revision"></i></button>
                            <button type="button" class="btn btn-sm btn-icon btn-outline-danger" onclick="handleForceDelete({{ $company->id }}, '{{ $company->name }}')" title="Permanently Delete"><i class="bx bx-trash"></i></button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">No companies in recycle bin</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $companies->links() }}</div>
    </div>
</div>

<form id="force-delete-form" method="POST" style="display: none;">@csrf @method('DELETE')</form>
<form id="restore-form" method="POST" style="display: none;">@csrf @method('PUT')</form>
@endsection

@section('script')
<script>
    function handleRestore(id, name) {
        Swal.fire({ title: 'Restore Company?', text: "Are you sure you want to restore '" + name + "'?", icon: 'question', showCancelButton: true, confirmButtonColor: '#696cff', cancelButtonColor: '#3085d6', confirmButtonText: 'Yes, restore it!' }).then((result) => {
            if (result.isConfirmed) { const form = document.getElementById('restore-form'); form.action = "{{ url('admin/companies') }}/" + id + "/restore"; form.submit(); }
        })
    }

    function handleForceDelete(id, name) {
        Swal.fire({ title: 'Permanently Delete?', text: "This will permanently delete '" + name + "' and all its records!", icon: 'error', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6', confirmButtonText: 'Yes, permanently delete!' }).then((result) => {
            if (result.isConfirmed) { const form = document.getElementById('force-delete-form'); form.action = "{{ url('admin/companies') }}/" + id + "/force-delete"; form.submit(); }
        })
    }
</script>
@endsection
