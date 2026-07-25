@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h5 class="mb-0">User Information</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0 mt-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                    <li class="breadcrumb-item active">Show</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary"><i class="bx bx-edit-alt me-1"></i> Edit</a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary"><i class="bx bx-arrow-back me-1"></i> Back</a>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr><th width="150">Name</th><td>{{ $user->first_name }} {{ $user->last_name }}</td></tr>
                        <tr><th>Email</th><td>{{ $user->email }}</td></tr>
                        <tr><th>Phone</th><td>{{ $user->phone }}</td></tr>
                        <tr><th>Company</th><td>{{ $user->company->name ?? 'N/A' }}</td></tr>
                        <tr><th>Branch</th><td>{{ $user->branch->name ?? 'N/A' }}</td></tr>
                        <tr><th>Role(s)</th><td>{{ $user->roles->pluck('name')->implode(', ') }}</td></tr>
                        <tr><th>Status</th><td><span class="badge bg-label-{{ $user->status == 'active' ? 'success' : 'danger' }}">{{ ucfirst($user->status) }}</span></td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr><th width="150">Address</th><td>{{ $user->address ?? 'N/A' }}</td></tr>
                        <tr><th>City</th><td>{{ $user->city ?? 'N/A' }}</td></tr>
                        <tr><th>State</th><td>{{ $user->state ?? 'N/A' }}</td></tr>
                        <tr><th>Country</th><td>{{ $user->country ?? 'N/A' }}</td></tr>
                        <tr><th>Created At</th><td>{{ $user->created_at->format('d M Y, h:i A') }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
