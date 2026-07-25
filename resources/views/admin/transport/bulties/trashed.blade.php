@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h5 class="mb-0">Bilties (Lorry Receipts)</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0 mt-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item">Transport</li>
                    <li class="breadcrumb-item active">Recycle Bin</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.transport.bulties.index') }}" class="btn btn-secondary"><i class="bx bx-arrow-back me-1"></i> Back to List</a>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>LR No</th>
                        <th>Consignor</th>
                        <th>Consignee</th>
                        <th>From → To</th>
                        <th>Amount</th>
                        <th class="text-nowrap">Deleted At</th>
                        <th class="text-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bulties as $index => $bulty)
                    <tr>
                        <td class="text-nowrap">{{ $bulties->firstItem() + $index }}</td>
                        <td class="fw-semibold">{{ $bulty->lr_no }}</td>
                        <td>{{ $bulty->consignor->name ?? '-' }}</td>
                        <td>{{ $bulty->consignee->name ?? '-' }}</td>
                        <td>{{ $bulty->originCity->name ?? '-' }} <i class="bx bx-chevron-right mx-1 text-muted"></i> {{ $bulty->destinationCity->name ?? '-' }}</td>
                        <td>₹{{ number_format($bulty->total_amount, 2) }}</td>
                        <td class="text-nowrap">{{ $bulty->deleted_at->format('d M Y, h:i A') }}</td>
                        <td class="text-nowrap">
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>
                                <div class="dropdown-menu">
                                    <button type="button" class="dropdown-item" onclick="handleRestore({{ $bulty->id }}, '{{ $bulty->lr_no }}')"><i class="bx bx-reset me-1"></i> Restore</button>
                                    <button type="button" class="dropdown-item text-danger" onclick="handleForceDelete({{ $bulty->id }}, '{{ $bulty->lr_no }}')"><i class="bx bx-trash me-1"></i> Permanently Delete</button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-4 text-muted">No bilties in recycle bin</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $bulties->links() }}</div>
    </div>
</div>

<form id="restore-form" method="POST" style="display: none;">@csrf @method('PUT')</form>
<form id="force-delete-form" method="POST" style="display: none;">@csrf @method('DELETE')</form>
@endsection

@section('script')
<script>
    function handleRestore(id, lrNo) {
        Swal.fire({
            title: 'Restore Bilty?',
            text: "Are you sure you want to restore Bilty '" + lrNo + "'?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#696cff',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, restore it!',
            cancelButtonText: 'Cancel',
            customClass: { confirmButton: 'btn btn-primary me-3', cancelButton: 'btn btn-secondary' },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('restore-form');
                form.action = "{{ url('admin/transport/bulties') }}/" + id + "/restore";
                form.submit();
            }
        })
    }

    function handleForceDelete(id, lrNo) {
        Swal.fire({
            title: 'Permanently Delete?',
            text: "This will permanently delete Bilty '" + lrNo + "'!",
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, permanently delete!',
            cancelButtonText: 'Cancel',
            customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-secondary' },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('force-delete-form');
                form.action = "{{ url('admin/transport/bulties') }}/" + id + "/force-delete";
                form.submit();
            }
        })
    }
</script>
@endsection