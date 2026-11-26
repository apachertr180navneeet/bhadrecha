@extends('admin.layouts.app')



@section('content')

<div class="container-fluid flex-grow-1 container-p-y">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">

        <div>

            <h5 class="mb-0">Edit Vehicle</h5>

            <nav aria-label="breadcrumb">

                <ol class="breadcrumb breadcrumb-style1 mb-0 mt-1">

                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>

                    <li class="breadcrumb-item"><a href="{{ route('admin.masters.vehicles.index') }}">Vehicles</a></li>

                    <li class="breadcrumb-item active">Edit</li>

                </ol>

            </nav>

        </div>

    </div>

    <div class="card"><div class="card-body">

            <form method="POST" action="{{ route('admin.masters.vehicles.update', $vehicle->id) }}" enctype="multipart/form-data">

                @csrf @method('PUT')

                <div class="row g-3">

                    <div class="col-md-4"><label class="form-label">Vehicle Number *</label><input type="text" name="vehicle_number" class="form-control" value="{{ old('vehicle_number', $vehicle->vehicle_number) }}" required></div>

                    <div class="col-md-4"><label class="form-label">Vehicle Type</label><input type="text" name="vehicle_type" class="form-control" value="{{ old('vehicle_type', $vehicle->vehicle_type) }}"></div>

                    <div class="col-md-4"><label class="form-label">Make/Model</label><input type="text" name="make_model" class="form-control" value="{{ old('make_model', $vehicle->make_model) }}"></div>

                    <div class="col-md-4"><label class="form-label">Capacity (Tons)</label><input type="number" step="0.01" name="capacity_tons" class="form-control" value="{{ old('capacity_tons', $vehicle->capacity_tons) }}"></div>

                <div class="col-md-4"><label class="form-label">Owner Name</label><input type="text" name="owner_name" class="form-control" value="{{ old('owner_name', $vehicle->owner_name) }}"></div>

                <div class="col-md-4"><label class="form-label">Owner Phone</label><input type="text" name="owner_phone" class="form-control" value="{{ old('owner_phone', $vehicle->owner_phone) }}"></div>

                <div class="col-md-4"><label class="form-label">Insurance Expiry</label><input type="date" max="9999-12-31" name="insurance_expiry" class="form-control" value="{{ old('insurance_expiry', $vehicle->insurance_expiry?->format('Y-m-d')) }}"></div>

                <div class="col-md-4"><label class="form-label">Fitness Expiry</label><input type="date" max="9999-12-31" name="fitness_expiry" class="form-control" value="{{ old('fitness_expiry', $vehicle->fitness_expiry?->format('Y-m-d')) }}"></div>

                <div class="col-md-4"><label class="form-label">Permit Expiry</label><input type="date" max="9999-12-31" name="permit_expiry" class="form-control" value="{{ old('permit_expiry', $vehicle->permit_expiry?->format('Y-m-d')) }}"></div>

                <div class="col-md-4"><label class="form-label">Pollution Expiry</label><input type="date" max="9999-12-31" name="pollution_expiry" class="form-control" value="{{ old('pollution_expiry', $vehicle->pollution_expiry?->format('Y-m-d')) }}"></div>

            </div>

            <hr class="my-4">

            <h6 class="fw-bold">Documents</h6>

            <div class="row g-3">

                <div class="col-md-4">

                    <label class="form-label">Registration Certificate</label>

                    <input type="file" name="registration_cert" class="form-control" accept="image/jpeg,image/png">

                    @if($vehicle->registration_cert)<div class="mt-2"><img src="{{ $vehicle->registration_cert }}" class="img-thumbnail" style="max-height:80px;" alt="RC"></div>@endif

                </div>

                <div class="col-md-4">

                    <label class="form-label">Insurance Document</label>

                    <input type="file" name="insurance_doc" class="form-control" accept="image/jpeg,image/png">

                    @if($vehicle->insurance_doc)<div class="mt-2"><img src="{{ $vehicle->insurance_doc }}" class="img-thumbnail" style="max-height:80px;" alt="Insurance"></div>@endif

                </div>

                <div class="col-md-4">

                    <label class="form-label">Fitness Certificate</label>

                    <input type="file" name="fitness_doc" class="form-control" accept="image/jpeg,image/png">

                    @if($vehicle->fitness_doc)<div class="mt-2"><img src="{{ $vehicle->fitness_doc }}" class="img-thumbnail" style="max-height:80px;" alt="Fitness"></div>@endif

                </div>

                <div class="col-md-4">

                    <label class="form-label">Permit Document</label>

                    <input type="file" name="permit_doc" class="form-control" accept="image/jpeg,image/png">

                    @if($vehicle->permit_doc)<div class="mt-2"><img src="{{ $vehicle->permit_doc }}" class="img-thumbnail" style="max-height:80px;" alt="Permit"></div>@endif

                </div>

                <div class="col-md-4">

                    <label class="form-label">Pollution Certificate</label>

                    <input type="file" name="pollution_cert" class="form-control" accept="image/jpeg,image/png">

                    @if($vehicle->pollution_cert)<div class="mt-2"><img src="{{ $vehicle->pollution_cert }}" class="img-thumbnail" style="max-height:80px;" alt="Pollution"></div>@endif

                </div>

            </div>

            <div class="mt-4 d-grid gap-2 d-md-flex"><button type="submit" class="btn btn-primary">Update Vehicle</button> <a href="{{ route('admin.masters.vehicles.index') }}" class="btn btn-secondary">Cancel</a></div>

        </form>

    </div></div>

</div>

@endsection


