@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <div>
            <h5 class="fw-bold mb-1">Recycle Bin - Spare Parts</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Maintenance</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.maintenance.spare-part.index') }}">Spare Parts</a></li>
                    <li class="breadcrumb-item active">Recycle Bin</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.maintenance.spare-part.index') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i> Back</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Part Name</th>
                        <th>Part No.</th>
                        <th>Vehicle</th>
                        <th class="text-end">Stock</th>
                        <th>Supplier</th>
                        <th>Deleted At</th>
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
                        <td class="text-end">{{ $part->quantity }}</td>
                        <td>{{ $part->supplier?->name ?? '-' }}</td>
                        <td>{{ $part->deleted_at->format('d-m-Y h:i A') }}</td>
                        <td class="text-center text-nowrap">
                            <form method="POST" action="{{ route('admin.maintenance.spare-part.restore', $part->id) }}" class="d-inline" onsubmit="return confirm('Restore this part?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-icon btn-outline-success" title="Restore"><i class="bx bx-revision"></i></button>
                            </form>
                            <form method="POST" action="{{ route('admin.maintenance.spare-part.force-delete', $part->id) }}" class="d-inline" onsubmit="return confirm('Permanently delete this part? This cannot be undone.')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Permanently Delete"><i class="bx bx-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">Recycle bin is empty</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($parts, 'links'))
        <div class="card-footer">
            {{ $parts->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
