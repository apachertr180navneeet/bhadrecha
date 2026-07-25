@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h5 class="mb-0">Role: {{ $role->name }}</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0 mt-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Roles</a></li>
                    <li class="breadcrumb-item active">{{ $role->name }}</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-warning"><i class="bx bx-edit me-1"></i> Edit</a>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary"><i class="bx bx-arrow-back me-1"></i> Back</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header"><h6 class="mb-0">Role Details</h6></div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr><th style="width:180px">Name</th><td>{{ $role->name }}</td></tr>
                        <tr><th>Guard</th><td>{{ $role->guard_name }}</td></tr>
                        <tr><th>Created</th><td>{{ $role->created_at->format('d M Y, h:i A') }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header"><h6 class="mb-0">Assigned Users ({{ $role->users->count() }})</h6></div>
                <div class="card-body">
                    @forelse($role->users as $user)
                        <span class="badge bg-label-primary me-1 mb-1">{{ $user->full_name ?? $user->email }}</span>
                    @empty
                        <p class="text-muted mb-0">No users assigned to this role.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h6 class="mb-0">Permissions ({{ $role->permissions->count() }})</h6></div>
        <div class="card-body">
            @forelse($role->permissions->groupBy(function($p) { return explode(' ', $p->name)[0]; }) as $group => $perms)
                <div class="mb-3">
                    <h6 class="text-capitalize text-muted">{{ $group }}</h6>
                    @foreach($perms as $perm)
                        <span class="badge bg-label-success me-1 mb-1">{{ $perm->name }}</span>
                    @endforeach
                </div>
            @empty
                <p class="text-muted mb-0">No permissions assigned to this role.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
