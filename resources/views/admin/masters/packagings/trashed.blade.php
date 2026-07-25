@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h5 class="mb-0">Packagings</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0 mt-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item">Masters</li>
                    <li class="breadcrumb-item active">Recycle Bin</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.masters.packagings.index') }}" class="btn btn-secondary"><i class="bx bx-arrow-back me-1"></i> Back to List</a>
            <a href="{{ route('admin.masters.packagings.create') }}" class="btn btn-primary"><i class="bx bx-plus me-1"></i> Add Packaging</a>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr><th>#</th><th>Name</th><th>Description</th><th class="text-nowrap">Deleted At</th><th class="text-nowrap">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($packagings as $index => $packaging)
                    <tr>
                        <td class="text-nowrap">{{ $packagings->firstItem() + $index }}</td>
                        <td class="text-nowrap"><strong>{{ $packaging->name }}</strong></td>
                        <td style="max-width: 200px">{{ $packaging->description ?? '-' }}</td>
                        <td class="text-nowrap">{{ $packaging->deleted_at->format('d M Y, h:i A') }}</td>
                        <td class="text-center text-nowrap">
                            <button type="button" class="btn btn-sm btn-icon btn-outline-success" onclick="handleRestore({{ $packaging->id }}, '{{ $packaging->name }}')" title="Restore"><i class="bx bx-revision"></i></button>
                            <button type="button" class="btn btn-sm btn-icon btn-outline-danger" onclick="handleForceDelete({{ $packaging->id }}, '{{ $packaging->name }}')" title="Permanently Delete"><i class="bx bx-trash"></i></button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-4 text-muted">No packagings in recycle bin</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $packagings->links() }}</div>
    </div>
</div>

<form id="force-delete-form" method="POST" style="display: none;">@csrf @method('DELETE')</form>
<form id="restore-form" method="POST" style="display: none;">@csrf @method('PUT')</form>
@endsection

@section('script')
<script>
    function handleRestore(id, name) {
        Swal.fire({ title: 'Restore Packaging?', text: "Are you sure you want to restore '" + name + "'?", icon: 'question', showCancelButton: true, confirmButtonColor: '#696cff', cancelButtonColor: '#3085d6', confirmButtonText: 'Yes, restore it!' }).then((result) => {
            if (result.isConfirmed) { const form = document.getElementById('restore-form'); form.action = "{{ url('admin/masters/packagings') }}/" + id + "/restore"; form.submit(); }
        })
    }

    function handleForceDelete(id, name) {
        Swal.fire({ title: 'Permanently Delete?', text: "This will permanently delete '" + name + "'!", icon: 'error', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6', confirmButtonText: 'Yes, permanently delete!' }).then((result) => {
            if (result.isConfirmed) { const form = document.getElementById('force-delete-form'); form.action = "{{ url('admin/masters/packagings') }}/" + id + "/force-delete"; form.submit(); }
        })
    }
</script>
@endsection
