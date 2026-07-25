@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <div>
            <h5 class="fw-bold mb-1">Recycle Bin - Tyre Brands</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.masters.tyre-brands.index') }}">Tyre Brands</a></li>
                    <li class="breadcrumb-item active">Recycle Bin</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.masters.tyre-brands.index') }}" class="btn btn-secondary"><i class="bx bx-arrow-back me-1"></i> Back to List</a>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Brand Name</th>
                        <th>Code</th>
                        <th>Deleted At</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($brands as $key => $brand)
                    <tr>
                        <td>{{ ($brands->currentPage() - 1) * $brands->perPage() + $key + 1 }}</td>
                        <td class="fw-semibold">{{ $brand->name }}</td>
                        <td>{{ $brand->code ?? '-' }}</td>
                        <td>{{ $brand->deleted_at->format('d-m-Y H:i A') }}</td>
                        <td class="text-center text-nowrap">
                            <form action="{{ route('admin.masters.tyre-brands.restore', $brand->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-sm btn-icon btn-outline-success" title="Restore"><i class="bx bx-undo"></i></button>
                            </form>
                            <form action="{{ route('admin.masters.tyre-brands.force-delete', $brand->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to permanently delete this brand?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Permanently Delete"><i class="bx bx-trash-alt"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <p class="text-muted mb-0">No deleted tyre brands found.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($brands->hasPages())
        <div class="card-footer bg-transparent border-top py-3">
            {{ $brands->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
