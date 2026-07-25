@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <div>
            <h5 class="fw-bold mb-1">Spare Parts</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Maintenance</a></li>
                    <li class="breadcrumb-item active">Spare Parts</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.maintenance.spare-part.trashed') }}" class="btn btn-outline-danger"><i class="bx bx-trash me-1"></i> Recycle Bin</a>
            <a href="{{ route('admin.maintenance.spare-part.create') }}" class="btn btn-primary"><i class="bx bx-plus me-1"></i> New Part</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Part name or number..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Vehicle</label>
                    <select name="vehicle_id" class="form-select">
                        <option value="">All Vehicles</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" {{ request('vehicle_id') == $vehicle->id ? 'selected' : '' }}>{{ $vehicle->vehicle_number }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Stock Status</label>
                    <select name="stock" class="form-select">
                        <option value="">All</option>
                        <option value="low" {{ request('stock') == 'low' ? 'selected' : '' }}>Low Stock</option>
                        <option value="in" {{ request('stock') == 'in' ? 'selected' : '' }}>In Stock</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Part Name</th>
                        <th>Part No.</th>
                        <th>Vehicle</th>
                        <th class="text-end">Quantity</th>
                        <th class="text-end">Price (₹)</th>
                        <th class="text-end">Amount (₹)</th>
                        <th>Part Change Date</th>
                        <th>Supplier</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($parts as $part)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $part->name }}</strong></td>
                        <td>{{ $part->part_number ?? '-' }}</td>
                        <td>{{ $part->vehicle?->vehicle_number ?? '-' }}</td>
                        <td class="text-end">
                            <span class="badge bg-success">{{ $part->quantity }}</span>
                        </td>
                        <td class="text-end">{{ number_format($part->unit_price, 2) }}</td>
                        <td class="text-end">{{ number_format($part->amount ?? ($part->quantity * $part->unit_price), 2) }}</td>
                        <td>{{ $part->part_change_date?->format('d-m-Y') ?? '-' }}</td>
                        <td>{{ $part->supplier?->name ?? '-' }}</td>
                        <td class="text-center text-nowrap">
                            <a href="{{ route('admin.maintenance.spare-part.show', $part) }}" class="btn btn-sm btn-icon btn-outline-info" title="View"><i class="bx bx-show"></i></a>
                            <a href="{{ route('admin.maintenance.spare-part.edit', $part) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Edit"><i class="bx bx-edit"></i></a>
                            <form method="POST" action="{{ route('admin.maintenance.spare-part.destroy', $part) }}" class="d-inline" onsubmit="return confirm('Delete this part?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Delete"><i class="bx bx-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted">No spare parts found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($parts, 'links'))
        <div class="card-footer">
            {{ $parts->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
