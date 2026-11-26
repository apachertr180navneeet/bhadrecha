@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h5 class="mb-0">{{ $company->name }}</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0 mt-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.companies.index') }}">Companies</a></li>
                    <li class="breadcrumb-item active">Show</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.companies.edit', $company->id) }}" class="btn btn-primary"><i class="bx bx-edit-alt me-1"></i> Edit</a>
            <a href="{{ route('admin.companies.index') }}" class="btn btn-secondary"><i class="bx bx-arrow-back me-1"></i> Back</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr><th width="150">Name</th><td>{{ $company->name }}</td></tr>
                        <tr><th>Email</th><td>{{ $company->email ?? 'N/A' }}</td></tr>
                        <tr><th>Phone</th><td>{{ $company->phone ?? 'N/A' }}</td></tr>
                        <tr><th>Address</th><td>{{ $company->address ?? 'N/A' }}</td></tr>
                        <tr><th>State</th><td>{{ $company->state ?? 'N/A' }}</td></tr>
                        <tr><th>Declaration</th><td>{{ $company->declaration ?? 'N/A' }}</td></tr>
                        <tr><th>Disclaimer</th><td>{{ $company->disclaimer ?? 'N/A' }}</td></tr>
                        <tr><th>GST Number</th><td>{{ $company->gst_number ?? 'N/A' }}</td></tr>
                        <tr><th>Status</th><td><span class="badge bg-label-{{ $company->status == 'active' ? 'success' : 'danger' }}">{{ ucfirst($company->status) }}</span></td></tr>
                        @if($company->logo)
                        <tr><th>Logo</th><td><img src="{{ Str::startsWith($company->logo, 'http') ? $company->logo : asset('uploads/' . $company->logo) }}" alt="Logo" style="max-height: 80px;"></td></tr>
                        @endif
                        <tr><th>Created At</th><td>{{ $company->created_at->format('d M Y, h:i A') }}</td></tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Quick Stats</h5></div>
                <div class="card-body">
                    <div class="mb-3"><span class="text-muted">Total Branches</span><h4 class="mb-0">{{ $branches->count() }}</h4></div>
                    <div><span class="text-muted">Total Users</span><h4 class="mb-0">{{ $users->count() }}</h4></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Branches</h5>
                    <a href="{{ route('admin.branches.create', ['company_id' => $company->id]) }}" class="btn btn-sm btn-outline-primary"><i class="bx bx-plus me-1"></i> Add Branch</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr><th>Name</th><th>City</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @forelse($branches as $branch)
                            <tr>
                                <td>{{ $branch->name }}</td>
                                <td>{{ $branch->city ?? '-' }}</td>
                                <td><span class="badge bg-label-{{ $branch->status == 'active' ? 'success' : 'danger' }}">{{ ucfirst($branch->status) }}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-4 text-muted">No branches found</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Users</h5>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-outline-primary"><i class="bx bx-plus me-1"></i> Add User</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr><th>Name</th><th>Email</th><th>Role</th></tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr>
                                <td>{{ $user->full_name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->roles->pluck('name')->implode(', ') ?? 'N/A' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-4 text-muted">No users found</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
