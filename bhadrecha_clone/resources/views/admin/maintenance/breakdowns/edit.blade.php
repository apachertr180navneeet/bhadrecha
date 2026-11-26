@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <div>
            <h5 class="fw-bold mb-1">Edit Breakdown Record</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Maintenance</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.maintenance.breakdowns.index') }}">Breakdowns</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Edit Breakdown Record</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.maintenance.breakdowns.update', $breakdown) }}">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Vehicle <span class="text-danger">*</span></label>
                        <select name="vehicle_id" class="form-select @error('vehicle_id') is-invalid @enderror" required>
                            <option value="">Select Vehicle</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" {{ old('vehicle_id', $breakdown->vehicle_id) == $vehicle->id ? 'selected' : '' }}>{{ $vehicle->vehicle_number }}</option>
                            @endforeach
                        </select>
                        @error('vehicle_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Driver</label>
                        <select name="driver_id" class="form-select @error('driver_id') is-invalid @enderror">
                            <option value="">Select Driver (optional)</option>
                            @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}" {{ old('driver_id', $breakdown->driver_id) == $driver->id ? 'selected' : '' }}>{{ $driver->name }} ({{ $driver->phone }})</option>
                            @endforeach
                        </select>
                        @error('driver_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Issue Type <span class="text-danger">*</span></label>
                        <select name="issue_type" class="form-select @error('issue_type') is-invalid @enderror" required>
                            <option value="">Select Issue</option>
                            <option value="Engine" {{ old('issue_type', $breakdown->issue_type) == 'Engine' ? 'selected' : '' }}>Engine</option>
                            <option value="Transmission" {{ old('issue_type', $breakdown->issue_type) == 'Transmission' ? 'selected' : '' }}>Transmission</option>
                            <option value="Electrical" {{ old('issue_type', $breakdown->issue_type) == 'Electrical' ? 'selected' : '' }}>Electrical</option>
                            <option value="Tire" {{ old('issue_type', $breakdown->issue_type) == 'Tire' ? 'selected' : '' }}>Tire</option>
                            <option value="Brake" {{ old('issue_type', $breakdown->issue_type) == 'Brake' ? 'selected' : '' }}>Brake</option>
                            <option value="Clutch" {{ old('issue_type', $breakdown->issue_type) == 'Clutch' ? 'selected' : '' }}>Clutch</option>
                            <option value="Cooling System" {{ old('issue_type', $breakdown->issue_type) == 'Cooling System' ? 'selected' : '' }}>Cooling System</option>
                            <option value="Suspension" {{ old('issue_type', $breakdown->issue_type) == 'Suspension' ? 'selected' : '' }}>Suspension</option>
                            <option value="Battery" {{ old('issue_type', $breakdown->issue_type) == 'Battery' ? 'selected' : '' }}>Battery</option>
                            <option value="Fuel System" {{ old('issue_type', $breakdown->issue_type) == 'Fuel System' ? 'selected' : '' }}>Fuel System</option>
                            <option value="Body" {{ old('issue_type', $breakdown->issue_type) == 'Body' ? 'selected' : '' }}>Body</option>
                            <option value="Other" {{ old('issue_type', $breakdown->issue_type) == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('issue_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Breakdown Date <span class="text-danger">*</span></label>
                        <input type="date" max="9999-12-31" name="breakdown_date" class="form-control @error('breakdown_date') is-invalid @enderror" value="{{ old('breakdown_date', $breakdown->breakdown_date?->format('Y-m-d')) }}" required>
                        @error('breakdown_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Breakdown Time</label>
                        <input type="time" name="breakdown_time" class="form-control @error('breakdown_time') is-invalid @enderror" value="{{ old('breakdown_time', $breakdown->breakdown_time) }}">
                        @error('breakdown_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Severity <span class="text-danger">*</span></label>
                        <select name="severity" class="form-select @error('severity') is-invalid @enderror" required>
                            <option value="minor" {{ old('severity', $breakdown->severity) == 'minor' ? 'selected' : '' }}>Minor</option>
                            <option value="major" {{ old('severity', $breakdown->severity) == 'major' ? 'selected' : '' }}>Major</option>
                            <option value="critical" {{ old('severity', $breakdown->severity) == 'critical' ? 'selected' : '' }}>Critical</option>
                        </select>
                        @error('severity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Location <span class="text-danger">*</span></label>
                    <input type="text" name="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location', $breakdown->location) }}" required>
                    @error('location') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Latitude</label>
                        <input type="text" name="latitude" class="form-control @error('latitude') is-invalid @enderror" value="{{ old('latitude', $breakdown->latitude) }}">
                        @error('latitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Longitude</label>
                        <input type="text" name="longitude" class="form-control @error('longitude') is-invalid @enderror" value="{{ old('longitude', $breakdown->longitude) }}">
                        @error('longitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2">{{ old('description', $breakdown->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <h6 class="mt-3 mb-2 fw-semibold">Repair & Vendor Info</h6>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Workshop / Vendor</label>
                        <select name="vendor_id" class="form-select @error('vendor_id') is-invalid @enderror">
                            <option value="">Select Vendor</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" {{ old('vendor_id', $breakdown->vendor_id) == $vendor->id ? 'selected' : '' }}>{{ $vendor->name }}</option>
                            @endforeach
                        </select>
                        @error('vendor_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Repair Cost (₹)</label>
                        <input type="number" step="0.01" min="0" name="repair_cost" class="form-control @error('repair_cost') is-invalid @enderror" value="{{ old('repair_cost', $breakdown->repair_cost) }}">
                        @error('repair_cost') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Downtime (Hours)</label>
                        <input type="number" step="0.5" min="0" name="downtime_hours" class="form-control @error('downtime_hours') is-invalid @enderror" value="{{ old('downtime_hours', $breakdown->downtime_hours) }}">
                        @error('downtime_hours') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                            <option value="reported" {{ old('status', $breakdown->status) == 'reported' ? 'selected' : '' }}>Reported</option>
                            <option value="in_progress" {{ old('status', $breakdown->status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="resolved" {{ old('status', $breakdown->status) == 'resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="towed" {{ old('status', $breakdown->status) == 'towed' ? 'selected' : '' }}>Towed</option>
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Resolution Notes</label>
                        <textarea name="resolution_notes" class="form-control @error('resolution_notes') is-invalid @enderror" rows="2">{{ old('resolution_notes', $breakdown->resolution_notes) }}</textarea>
                        @error('resolution_notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Update Record</button>
                <a href="{{ route('admin.maintenance.breakdowns.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
