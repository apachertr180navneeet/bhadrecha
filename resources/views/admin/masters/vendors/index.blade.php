@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h5 class="mb-0">Vendors</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0 mt-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item">Masters</li>
                    <li class="breadcrumb-item active">Vendors</li>
                </ol>
            </nav>
        </div>
        <div>
            @canany(['create vendors', 'import vendors'])
            <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#importModal"><i class="bx bx-import me-1"></i> Import</button>
            <a href="{{ route('admin.masters.vendors.download-template') }}" class="btn btn-outline-secondary"><i class="bx bx-download me-1"></i> Template</a>
            @endcanany

            @can('delete vendors')
            <a href="{{ route('admin.masters.vendors.trashed') }}" class="btn btn-outline-danger"><i class="bx bx-trash me-1"></i> Recycle Bin</a>
            @endcan

            @can('create vendors')
            <a href="{{ route('admin.masters.vendors.create') }}" class="btn btn-primary"><i class="bx bx-plus me-1"></i> Add Vendor</a>
            @endcan
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible" role="alert">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card">
        <div class="card-body border-bottom py-3">
            <form method="GET" class="row g-2">
                <div class="col-12 col-md"><input type="text" name="search" class="form-control" placeholder="Search by name, vendor code, phone, GSTIN..." value="{{ request('search') }}"></div>
                <div class="col-12 col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-12 col-md-auto"><button type="submit" class="btn btn-outline-secondary w-100"><i class="bx bx-search me-1"></i> Search</button></div>
                @if(request()->hasAny(['search','status']))
                <div class="col-12 col-md-auto"><a href="{{ route('admin.masters.vendors.index') }}" class="btn btn-outline-danger w-100"><i class="bx bx-x me-1"></i> Clear</a></div>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Vendor Code</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>GSTIN</th>
                        <th>Contact Person</th>
                        <th>City</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vendors as $index => $vendor)
                    <tr>
                        <td>{{ $vendors->firstItem() + $index }}</td>
                        <td>{{ $vendor->vendor_code ?? '-' }}</td>
                        <td><strong>{{ $vendor->name }}</strong></td>
                        <td>{{ $vendor->phone ?? '-' }}</td>
                        <td>{{ $vendor->email ?? '-' }}</td>
                        <td>{{ $vendor->gstin ?? '-' }}</td>
                        <td>{{ $vendor->contact_person ?? '-' }}</td>
                        <td>{{ $vendor->city ?? '-' }}</td>
                        <td><span class="badge bg-label-{{ $vendor->status == 'active' ? 'success' : 'danger' }}">{{ ucfirst($vendor->status) }}</span></td>
                        <td class="text-center text-nowrap">
                            @can('edit vendors')
                            <a href="{{ route('admin.masters.vendors.edit', $vendor->id) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Edit"><i class="bx bx-edit"></i></a>
                            <form action="{{ route('admin.masters.vendors.toggle-status', $vendor->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-icon btn-outline-{{ $vendor->status == 'active' ? 'warning' : 'success' }}" title="{{ $vendor->status == 'active' ? 'Deactivate' : 'Activate' }}">
                                    <i class="bx bx-{{ $vendor->status == 'active' ? 'pause' : 'play' }}"></i>
                                </button>
                            </form>
                            @endcan
                            @can('delete vendors')
                            <button type="button" class="btn btn-sm btn-icon btn-outline-danger" title="Delete" onclick="handleDelete({{ $vendor->id }}, '{{ $vendor->name }}')"><i class="bx bx-trash"></i></button>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center py-4 text-muted">No vendors found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $vendors->withQueryString()->links() }}</div>
    </div>
</div>

<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.masters.vendors.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Import Vendors</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <p>Download the template first, fill it in, then upload here.</p>
                    <div class="mb-3"><label class="form-label">Choose Excel File (xlsx, xls, csv) *</label><input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary"><i class="bx bx-upload me-1"></i> Import</button> <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button></div>
            </form>
        </div>
    </div>
</div>

<form id="delete-form" method="POST" style="display: none;">@csrf @method('DELETE')</form>
@endsection

@section('script')
<script>
    function handleDelete(id, name) {
        Swal.fire({
            title: 'Delete Vendor?',
            text: "This will delete '" + name + "'!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('delete-form');
                form.action = "{{ url('admin/masters/vendors') }}/" + id;
                form.submit();
            }
        });
    }
</script>
@endsection
