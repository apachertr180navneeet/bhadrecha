@extends('admin.layouts.app')

@section('content')

<div class="container-fluid flex-grow-1 container-p-y">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">

        <div>

            <h5 class="mb-0">Vehicles</h5>

            <nav aria-label="breadcrumb">

                <ol class="breadcrumb breadcrumb-style1 mb-0 mt-1">

                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>

                    <li class="breadcrumb-item">Masters</li>

                    <li class="breadcrumb-item active">Vehicles</li>

                </ol>

            </nav>

        </div>

        <div>
            @canany(['create vehicles', 'import vehicles'])
            <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#importModal"><i class="bx bx-import me-1"></i> Import</button>
            <a href="{{ route('admin.masters.vehicles.download-template') }}" class="btn btn-outline-secondary"><i class="bx bx-download me-1"></i> Template</a>
            @endcanany

            @canany(['view vehicles', 'export vehicles'])
            <a href="{{ route('admin.masters.vehicles.export') }}" class="btn btn-outline-success"><i class="bx bx-export me-1"></i> Export</a>
            @endcanany

            @can('delete vehicles')
            <a href="{{ route('admin.masters.vehicles.trashed') }}" class="btn btn-outline-danger"><i class="bx bx-trash me-1"></i> Recycle Bin </a>
            @endcan

            @can('create vehicles')
            <a href="{{ route('admin.masters.vehicles.create') }}" class="btn btn-primary"><i class="bx bx-plus me-1"></i> Add Vehicle</a>
            @endcan
        </div>

    </div>

    <div class="card">

        <div class="card-body border-bottom py-3">

            <form method="GET" class="row g-2">

                <div class="col-12 col-md"><input type="text" name="search" class="form-control" placeholder="Search by vehicle no, owner..." value="{{ request('search') }}"></div>

                <div class="col-12 col-md-3">

                    <select name="status" class="form-select">

                        <option value="">All Status</option>

                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>

                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>

                    </select>

                </div>

                <div class="col-12 col-md-auto"><button type="submit" class="btn btn-outline-secondary w-100"><i class="bx bx-search me-1"></i> Search</button></div>

                @if(request()->hasAny(['search','status']))

                <div class="col-12 col-md-auto"><a href="{{ route('admin.masters.vehicles.index') }}" class="btn btn-outline-danger w-100"><i class="bx bx-x me-1"></i> Clear</a></div>

                @endif

            </form>

        </div>

        <div class="table-responsive text-nowrap">

            <table class="table table-hover">

                <thead class="table-light"><tr><th>#</th><th>Vehicle No</th><th>Type</th><th>Capacity</th><th>Owner</th><th>Insurance</th><th>Status</th><th>Docs</th><th>Actions</th></tr></thead>

                <tbody>

                    @forelse($vehicles as $index => $vehicle)

                    <tr>

                        <td>{{ $vehicles->firstItem() + $index }}</td>

                        <td><strong>{{ $vehicle->vehicle_number }}</strong></td>

                        <td>{{ $vehicle->vehicle_type ?? '-' }}</td>

                        <td>{{ $vehicle->capacity_tons }} Tons</td>

                        <td>{{ $vehicle->owner_name ?? '-' }}</td>

                        <td>{{ $vehicle->insurance_expiry ? date('d M Y', strtotime($vehicle->insurance_expiry)) : '-' }}</td>

                        <td><span class="badge bg-label-{{ $vehicle->status == 'active' ? 'success' : 'danger' }}">{{ ucfirst($vehicle->status) }}</span></td>

                        <td class="text-nowrap">

                            @php $docs = [$vehicle->registration_cert, $vehicle->insurance_doc, $vehicle->fitness_doc, $vehicle->permit_doc, $vehicle->pollution_cert]; $uploaded = count(array_filter($docs)); @endphp

                            <span class="small" title="{{ $uploaded }}/5 documents uploaded">

                                <i class="bx bx-file {{ $uploaded > 0 ? 'text-success' : 'text-muted' }}"></i>

                                {{ $uploaded }}/5

                            </span>

                        </td>

                        <td class="text-center text-nowrap">

                            @can('edit vehicles')
                            <a href="{{ route('admin.masters.vehicles.edit', $vehicle->id) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Edit"><i class="bx bx-edit"></i></a>

                            <form action="{{ route('admin.masters.vehicles.toggle-status', $vehicle->id) }}" method="POST" class="d-inline">@csrf

                                <button type="submit" class="btn btn-sm btn-icon btn-outline-{{ $vehicle->status == 'active' ? 'warning' : 'success' }}" title="{{ $vehicle->status == 'active' ? 'Deactivate' : 'Activate' }}"><i class="bx bx-{{ $vehicle->status == 'active' ? 'pause' : 'play' }}"></i></button>

                            </form>
                            @endcan

                            @can('delete vehicles')
                            <button type="button" class="btn btn-sm btn-icon btn-outline-danger" onclick="handleDelete({{ $vehicle->id }}, '{{ $vehicle->vehicle_number }}')" title="Delete"><i class="bx bx-trash"></i></button>
                            @endcan

                        </td>

                    </tr>

                    @empty

                    <tr><td colspan="9" class="text-center py-4 text-muted">No vehicles found</td></tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="card-footer">{{ $vehicles->withQueryString()->links() }}</div>

    </div>

</div>

<form id="delete-form" method="POST" style="display: none;">@csrf @method('DELETE')</form>

<div class="modal fade" id="importModal" tabindex="-1">

    <div class="modal-dialog">

        <div class="modal-content">

            <form method="POST" action="{{ route('admin.masters.vehicles.import') }}" enctype="multipart/form-data">

                @csrf

                <div class="modal-header"><h5 class="modal-title">Import Vehicles</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>

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

        Swal.fire({ title: 'Delete Vehicle?', text: "This will delete '" + name + "'!", icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6', confirmButtonText: 'Yes, delete it!' }).then((result) => {

            if (result.isConfirmed) { const form = document.getElementById('delete-form'); form.action = "{{ url('admin/masters/vehicles') }}/" + id; form.submit(); }

        })

    }

</script>

@endsection
