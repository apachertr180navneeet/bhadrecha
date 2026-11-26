@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h5 class="mb-0">Add Driver</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0 mt-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.masters.drivers.index') }}">Drivers</a></li>
                    <li class="breadcrumb-item active">Add</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.masters.drivers.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Driver ID</label><input type="text" name="driver_id" class="form-control" value="{{ old('driver_id') }}"></div>
                    <div class="col-md-4"><label class="form-label">Name *</label><input type="text" name="name" class="form-control" value="{{ old('name') }}" required></div>
                    <div class="col-md-4"><label class="form-label">Phone *</label><input type="text" name="phone" class="form-control" value="{{ old('phone') }}" required></div>
                    <div class="col-md-4"><label class="form-label">License Number *</label><input type="text" name="license_number" class="form-control" value="{{ old('license_number') }}" required></div>
                    <div class="col-md-4"><label class="form-label">License Expiry</label><input type="date" max="9999-12-31" name="license_expiry" class="form-control" value="{{ old('license_expiry') }}"></div>
                    <div class="col-md-4"><label class="form-label">Emergency Contact</label><input type="text" name="emergency_contact" class="form-control" value="{{ old('emergency_contact') }}"></div>
                    <div class="col-md-4"><label class="form-label">City</label><input type="text" name="city" class="form-control" value="{{ old('city') }}"></div>
                    <div class="col-md-4"><label class="form-label">State</label><input type="text" name="state" class="form-control" value="{{ old('state') }}"></div>
                    <div class="col-12"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea></div>
                </div>

                <hr class="my-4">
                <h6 class="fw-bold mb-3"><i class="bx bx-file me-1"></i> Documents</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">License Front</label>
                        <input type="file" name="license_front" class="form-control" accept="image/jpeg,image/png">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">License Back</label>
                        <input type="file" name="license_back" class="form-control" accept="image/jpeg,image/png">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Aadhar Front</label>
                        <input type="file" name="aadhar_front" class="form-control" accept="image/jpeg,image/png">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Aadhar Back</label>
                        <input type="file" name="aadhar_back" class="form-control" accept="image/jpeg,image/png">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Pan Front</label>
                        <input type="file" name="pan_front" class="form-control" accept="image/jpeg,image/png">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Pan Back</label>
                        <input type="file" name="pan_back" class="form-control" accept="image/jpeg,image/png">
                    </div>
                </div>

                <div class="mt-4 d-grid gap-2 d-md-flex"><button type="submit" class="btn btn-primary">Save Driver</button> <a href="{{ route('admin.masters.drivers.index') }}" class="btn btn-secondary">Cancel</a></div>
            </form>
        </div>
    </div>
</div>
@endsection
