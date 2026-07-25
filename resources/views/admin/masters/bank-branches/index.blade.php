@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <div>
            <h5 class="fw-bold mb-1">Bank Branch Master</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Bank Branch Master</li>
                </ol>
            </nav>
        </div>
        <div>
            <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#importModal"><i class="bx bx-import me-1"></i> Import</button>
            <a href="{{ route('admin.masters.bank-branches.download-template') }}" class="btn btn-outline-secondary"><i class="bx bx-download me-1"></i> Template</a>
            <a href="{{ route('admin.masters.bank-branches.trashed') }}" class="btn btn-outline-danger"><i class="bx bx-trash me-1"></i> Recycle Bin</a>
            <a href="{{ route('admin.masters.bank-branches.create') }}" class="btn btn-primary"><i class="bx bx-plus me-1"></i> Add New Branch</a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.masters.bank-branches.index') }}" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search branch or IFSC..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="bank_id" class="form-select">
                        <option value="">All Banks</option>
                        @foreach($banks as $bank)
                            <option value="{{ $bank->id }}" {{ request('bank_id') == $bank->id ? 'selected' : '' }}>{{ $bank->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </div>
                @if(request()->anyFilled(['search', 'bank_id', 'status']))
                <div class="col-md-2">
                    <a href="{{ route('admin.masters.bank-branches.index') }}" class="btn btn-outline-secondary w-100">Clear</a>
                </div>
                @endif
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Bank</th>
                        <th>Branch Name</th>
                        <th>IFSC Code</th>
                        <th>Address</th>
                        <th>Status</th>
                        <th>Created Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($branches as $key => $branch)
                        <tr>
                            <td>{{ ($branches->currentPage() - 1) * $branches->perPage() + $key + 1 }}</td>
                            <td><span class="badge bg-label-info">{{ $branch->bank?->name ?? '-' }}</span></td>
                            <td><strong>{{ $branch->branch_name }}</strong></td>
                            <td><span class="badge bg-label-primary">{{ $branch->ifsc }}</span></td>
                            <td style="max-width:200px;">{{ $branch->address ?? '-' }}</td>
                            <td>
                                @if($branch->status === 'active')
                                    <span class="badge bg-label-success">Active</span>
                                @else
                                    <span class="badge bg-label-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>{{ $branch->created_at->format('d M Y') }}</td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('admin.masters.bank-branches.edit', $branch->id) }}" class="btn btn-sm btn-icon btn-outline-warning" title="Edit">
                                        <i class="bx bx-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.masters.bank-branches.toggle-status', $branch->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-icon btn-outline-{{ $branch->status == 'active' ? 'secondary' : 'success' }}" title="{{ $branch->status == 'active' ? 'Deactivate' : 'Activate' }}">
                                            <i class="bx bx-{{ $branch->status == 'active' ? 'pause' : 'play' }}"></i>
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-icon btn-outline-danger" onclick="handleDelete({{ $branch->id }})" title="Delete">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <p class="text-muted mb-0">No bank branches found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($branches->hasPages())
            <div class="card-footer bg-transparent border-top py-3">
                {{ $branches->links() }}
            </div>
        @endif
    </div>
</div>

<form id="delete-form" method="POST" style="display: none;">@csrf @method('DELETE')</form>

<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.masters.bank-branches.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Import Bank Branches</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <p>Download the template first, fill it in, then upload here.</p>
                    <div class="mb-3"><label class="form-label">Choose Excel File (xlsx, xls, csv) *</label><input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary"><i class="bx bx-upload me-1"></i> Import</button> <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button></div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    function handleDelete(id) {
        Swal.fire({
            title: 'Delete Bank Branch?',
            text: 'Are you sure? This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, Delete it',
            cancelButtonText: 'Cancel',
            customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-secondary' },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('delete-form');
                form.action = "{{ url('admin/masters/bank-branches') }}/" + id;
                form.submit();
            }
        })
    }
</script>
@endsection
