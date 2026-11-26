@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h5 class="mb-0">{{ $branch->name }}</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0 mt-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.branches.index') }}">Branches</a></li>
                    <li class="breadcrumb-item active">Show</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.branches.edit', $branch->id) }}" class="btn btn-primary"><i class="bx bx-edit-alt me-1"></i> Edit</a>
            <a href="{{ route('admin.branches.index') }}" class="btn btn-secondary"><i class="bx bx-arrow-back me-1"></i> Back</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr><th width="150">Name</th><td>{{ $branch->name }}</td></tr>
                        <tr><th>Company</th><td>{{ $branch->company->name ?? 'N/A' }}</td></tr>
                        <tr><th>Email</th><td>{{ $branch->email ?? 'N/A' }}</td></tr>
                        <tr><th>Phone</th><td>{{ $branch->phone ?? 'N/A' }}</td></tr>
                        <tr><th>State</th><td>{{ $branch->state ?? 'N/A' }}</td></tr>
                        <tr><th>City</th><td>{{ $branch->city ?? 'N/A' }}</td></tr>
                        <tr><th>Address</th><td>{{ $branch->address ?? 'N/A' }}</td></tr>
                        <tr><th>Status</th><td><span class="badge bg-label-{{ ($branch->status ?? 'inactive') == 'active' ? 'success' : 'danger' }}">{{ ucfirst($branch->status ?? 'inactive') }}</span></td></tr>
                        <tr><th>Created At</th><td>{{ $branch->created_at->format('d M Y, h:i A') }}</td></tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Quick Stats</h5></div>
                <div class="card-body">
                    <div><span class="text-muted">Total Users</span><h4 class="mb-0">{{ $users->count() }}</h4></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Assigned Users</h5>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-outline-primary"><i class="bx bx-plus me-1"></i> Add User</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr>
                                <td>{{ $user->full_name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->phone ?? '-' }}</td>
                                <td>{{ $user->roles->pluck('name')->implode(', ') ?? 'N/A' }}</td>
                                <td><span class="badge bg-label-{{ ($user->status ?? 'inactive') == 'active' ? 'success' : 'danger' }}">{{ ucfirst($user->status ?? 'inactive') }}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center py-4 text-muted">No users assigned</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
