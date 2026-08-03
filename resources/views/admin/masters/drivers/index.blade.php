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
                    <li class="breadcrumb-item active">Drivers</li>
                </ol>
            </nav>
        </div>
        <div>
            @canany(['create drivers', 'import drivers'])
            <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#importModal"><i class="bx bx-import me-1"></i> Import</button>
            <a href="{{ route('admin.masters.drivers.download-template') }}" class="btn btn-outline-secondary"><i class="bx bx-download me-1"></i> Template</a>
            @endcanany

            @canany(['view drivers', 'export drivers'])
            <a href="{{ route('admin.masters.drivers.export') }}" class="btn btn-outline-success"><i class="bx bx-export me-1"></i> Export</a>
            @endcanany

            @can('delete drivers')
            <a href="{{ route('admin.masters.drivers.trashed') }}" class="btn btn-outline-danger"><i class="bx bx-trash me-1"></i> Recycle Bin </a>
            @endcan

            @can('create drivers')
            <a href="{{ route('admin.masters.drivers.create') }}" class="btn btn-primary"><i class="bx bx-plus me-1"></i> Add Driver</a>
            @endcan
        </div>
    </div>

    <div class="card">
        <div class="card-body border-bottom py-3">
            <form method="GET" class="row g-2">
                <div class="col-12 col-md"><input type="text" name="search" class="form-control" placeholder="Search by name, phone, license..." value="{{ request('search') }}"></div>
                <div class="col-12 col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-12 col-md-auto"><button type="submit" class="btn btn-outline-secondary w-100"><i class="bx bx-search me-1"></i> Search</button></div>
                @if(request()->hasAny(['search','status']))
                <div class="col-12 col-md-auto"><a href="{{ route('admin.masters.drivers.index') }}" class="btn btn-outline-danger w-100"><i class="bx bx-x me-1"></i> Clear</a></div>
                @endif
            </form>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr><th>#</th><th>Driver ID</th><th>Name</th><th>Phone</th><th>License Number</th><th>License Expiry</th><th>City</th><th>Status</th><th>Docs</th><th>Actions</th></tr>
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
                        <td>
                            <span class="badge bg-label-{{ $driver->status == 'active' ? 'success' : 'danger' }}">{{ ucfirst($driver->status) }}</span>
                        </td>
                        <td class="text-nowrap">
                            @php $docs = [$driver->license_front, $driver->license_back, $driver->aadhar_front, $driver->aadhar_back, $driver->pan_front, $driver->pan_back]; $uploaded = count(array_filter($docs)); @endphp
                            <span class="small" title="{{ $uploaded }}/6 documents uploaded">
                                <i class="bx bx-file {{ $uploaded > 0 ? 'text-success' : 'text-muted' }}"></i>
                                {{ $uploaded }}/6
                            </span>
                        </td>
                        <td class="text-center text-nowrap">
                            @can('edit drivers')
                            <a href="{{ route('admin.masters.drivers.edit', $driver->id) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Edit"><i class="bx bx-edit"></i></a>
                            <form action="{{ route('admin.masters.drivers.toggle-status', $driver->id) }}" method="POST" class="d-inline">@csrf
                                <button type="submit" class="btn btn-sm btn-icon btn-outline-{{ $driver->status == 'active' ? 'warning' : 'success' }}" title="{{ $driver->status == 'active' ? 'Deactivate' : 'Activate' }}"><i class="bx bx-{{ $driver->status == 'active' ? 'pause' : 'play' }}"></i></button>
                            </form>
                            @endcan
                            @can('delete drivers')
                            <button type="button" class="btn btn-sm btn-icon btn-outline-danger" onclick="handleDelete({{ $driver->id }}, '{{ $driver->name }}')" title="Delete"><i class="bx bx-trash"></i></button>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center py-4 text-muted">No drivers found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $drivers->withQueryString()->links() }}</div>
    </div>
</div>

<form id="delete-form" method="POST" style="display: none;">@csrf @method('DELETE')</form>

<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.masters.drivers.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Import Drivers</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
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
    function handleDelete(id, name) {
        Swal.fire({ title: 'Delete Driver?', text: "This will delete '" + name + "'!", icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6', confirmButtonText: 'Yes, delete it!' }).then((result) => {
            if (result.isConfirmed) { const form = document.getElementById('delete-form'); form.action = "{{ url('admin/masters/drivers') }}/" + id; form.submit(); }
        })
    }
</script>
@endsection
