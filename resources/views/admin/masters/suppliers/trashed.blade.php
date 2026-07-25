@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h5 class="mb-0">Recycle Bin - Suppliers</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0 mt-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.masters.suppliers.index') }}">Suppliers</a></li>
                    <li class="breadcrumb-item active">Recycle Bin</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.masters.suppliers.index') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i> Back</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>GSTIN</th>
                        <th>Deleted At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $index => $supplier)
                    <tr>
                        <td>{{ $suppliers->firstItem() + $index }}</td>
                        <td><strong>{{ $supplier->name }}</strong></td>
                        <td>{{ $supplier->phone ?? '-' }}</td>
                        <td>{{ $supplier->gstin ?? '-' }}</td>
                        <td>{{ $supplier->deleted_at->format('d-m-Y h:i A') }}</td>
                        <td class="text-nowrap">
                            <form method="POST" action="{{ route('admin.masters.suppliers.restore', $supplier->id) }}" class="d-inline" onsubmit="return confirm('Restore this supplier?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-icon btn-outline-success" title="Restore"><i class="bx bx-revision"></i></button>
                            </form>
                            <form method="POST" action="{{ route('admin.masters.suppliers.force-delete', $supplier->id) }}" class="d-inline" onsubmit="return confirm('Permanently delete this supplier? This cannot be undone.')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Permanently Delete"><i class="bx bx-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">Recycle bin is empty</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($suppliers, 'links'))
        <div class="card-footer">{{ $suppliers->links() }}</div>
        @endif
    </div>
</div>
@endsection
