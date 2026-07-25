@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <div>
            <h5 class="fw-bold mb-1">Recycle Bin - Tyre Models</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.masters.tyre-models.index') }}">Tyre Models</a></li>
                    <li class="breadcrumb-item active">Recycle Bin</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.masters.tyre-models.index') }}" class="btn btn-secondary"><i class="bx bx-arrow-back me-1"></i> Back to List</a>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Model Name</th>
                        <th>Brand</th>
                        <th>Code</th>
                        <th>Deleted At</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($models as $key => $model)
                    <tr>
                        <td>{{ ($models->currentPage() - 1) * $models->perPage() + $key + 1 }}</td>
                        <td class="fw-semibold">{{ $model->name }}</td>
                        <td>{{ $model->brand->name ?? '-' }}</td>
                        <td>{{ $model->code ?? '-' }}</td>
                        <td>{{ $model->deleted_at->format('d-m-Y H:i A') }}</td>
                        <td class="text-center text-nowrap">
                            <form action="{{ route('admin.masters.tyre-models.restore', $model->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-sm btn-icon btn-outline-success" title="Restore"><i class="bx bx-undo"></i></button>
                            </form>
                            <form action="{{ route('admin.masters.tyre-models.force-delete', $model->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to permanently delete this model?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Permanently Delete"><i class="bx bx-trash-alt"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <p class="text-muted mb-0">No deleted tyre models found.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($models->hasPages())
        <div class="card-footer bg-transparent border-top py-3">
            {{ $models->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
