@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h5 class="mb-0">Permission: {{ $permission->name }}</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0 mt-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.permissions.index') }}">Permissions</a></li>
                    <li class="breadcrumb-item active">{{ $permission->name }}</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.permissions.edit', $permission) }}" class="btn btn-warning"><i class="bx bx-edit me-1"></i> Edit</a>
            <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary"><i class="bx bx-arrow-back me-1"></i> Back</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header"><h6 class="mb-0">Permission Details</h6></div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr><th style="width:180px">Name</th><td><code>{{ $permission->name }}</code></td></tr>
                        <tr><th>Group</th><td>{{ $permission->group ?: '-' }}</td></tr>
                        <tr><th>Guard</th><td>{{ $permission->guard_name }}</td></tr>
                        <tr><th>Created</th><td>{{ $permission->created_at->format('d M Y, h:i A') }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header"><h6 class="mb-0">Assigned to Roles ({{ $permission->roles->count() }})</h6></div>
                <div class="card-body">
                    @forelse($permission->roles as $role)
                        <span class="badge bg-label-primary me-1 mb-1">{{ $role->name }}</span>
                    @empty
                        <p class="text-muted mb-0">Not assigned to any role.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
