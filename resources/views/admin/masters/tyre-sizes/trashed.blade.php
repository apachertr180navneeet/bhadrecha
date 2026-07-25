@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <div>
            <h5 class="fw-bold mb-1">Recycle Bin - Tyre Sizes</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.masters.tyre-sizes.index') }}">Tyre Sizes</a></li>
                    <li class="breadcrumb-item active">Recycle Bin</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.masters.tyre-sizes.index') }}" class="btn btn-secondary"><i class="bx bx-arrow-back me-1"></i> Back to List</a>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Tyre Size</th>
                        <th>Brand</th>
                        <th>Model</th>
                        <th>Deleted At</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sizes as $key => $size)
                    <tr>
                        <td>{{ ($sizes->currentPage() - 1) * $sizes->perPage() + $key + 1 }}</td>
                        <td class="fw-semibold">{{ $size->name }}</td>
                        <td>{{ $size->brand->name ?? '-' }}</td>
                        <td>{{ $size->model->name ?? '-' }}</td>
                        <td>{{ $size->deleted_at->format('d-m-Y H:i A') }}</td>
                        <td class="text-center text-nowrap">
                            <form action="{{ route('admin.masters.tyre-sizes.restore', $size->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-sm btn-icon btn-outline-success" title="Restore"><i class="bx bx-undo"></i></button>
                            </form>
                            <form action="{{ route('admin.masters.tyre-sizes.force-delete', $size->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to permanently delete this size?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Permanently Delete"><i class="bx bx-trash-alt"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <p class="text-muted mb-0">No deleted tyre sizes found.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($sizes->hasPages())
        <div class="card-footer bg-transparent border-top py-3">
            {{ $sizes->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
