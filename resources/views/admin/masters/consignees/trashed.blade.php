@extends('admin.layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Consignees /</span> Recycle Bin </h4>
        <a href="{{ route('admin.masters.consignees.index') }}" class="btn btn-secondary"><i class="bx bx-arrow-back"></i> Back to List</a>
    </div>

    <div class="card">
        <h5 class="card-header">Recycle Bin</h5>
        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>GSTIN</th>
                        <th>City</th>
                        @if(auth()->user()->isSuperAdmin())<th>Company</th><th>Branch</th>@endif
                        <th>Deleted At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($consignees as $index => $consignee)
                    <tr>
                        <td>{{ $consignees->firstItem() + $index }}</td>
                        <td><strong>{{ $consignee->name }}</strong></td>
                        <td>{{ $consignee->phone }}</td>
                        <td>{{ $consignee->email ?? '-' }}</td>
                        <td>{{ $consignee->gstin ?? '-' }}</td>
                        <td>{{ $consignee->city ?? '-' }}</td>
                        @if(auth()->user()->isSuperAdmin())
                        <td>{{ $consignee->company->name ?? '-' }}</td>
                        <td>{{ $consignee->branch->name ?? '-' }}</td>
                        @endif
                        <td>{{ $consignee->deleted_at->format('d M Y, h:i A') }}</td>
                        <td class="text-center text-nowrap">
                            <button type="button" class="btn btn-sm btn-icon btn-outline-success" onclick="handleRestore({{ $consignee->id }}, '{{ $consignee->name }}')" title="Restore"><i class="bx bx-revision"></i></button>
                            <button type="button" class="btn btn-sm btn-icon btn-outline-danger" onclick="handleForceDelete({{ $consignee->id }}, '{{ $consignee->name }}')" title="Permanently Delete"><i class="bx bx-trash"></i></button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="{{ auth()->user()->isSuperAdmin() ? 11 : 9 }}" class="text-center">No consignees in recycle bin</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $consignees->links() }}
        </div>
    </div>
</div>

<form id="force-delete-form" method="POST" style="display: none;">
    @csrf @method('DELETE')
</form>
<form id="restore-form" method="POST" style="display: none;">
    @csrf @method('PUT')
</form>

@endsection

@section('script')
<script>
    function handleRestore(id, name) {
        Swal.fire({
            title: 'Restore Consignee?',
            text: "Are you sure you want to restore '" + name + "'?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#696cff',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, restore it!'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('restore-form');
                form.action = "{{ url('admin/masters/consignees') }}/" + id + "/restore";
                form.submit();
            }
        })
    }

    function handleForceDelete(id, name) {
        Swal.fire({
            title: 'Permanently Delete?',
            text: "This will permanently delete '" + name + "'!",
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, permanently delete!'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('force-delete-form');
                form.action = "{{ url('admin/masters/consignees') }}/" + id + "/force-delete";
                form.submit();
            }
        })
    }
</script>
@endsection
