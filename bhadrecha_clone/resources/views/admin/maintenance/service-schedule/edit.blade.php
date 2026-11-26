@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <div>
            <h5 class="fw-bold mb-1">Edit Service Schedule</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Maintenance</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.maintenance.service-schedule.index') }}">Service Schedule</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Edit Service Schedule</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.maintenance.service-schedule.update', $serviceSchedule) }}">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Vehicle <span class="text-danger">*</span></label>
                        <select name="vehicle_id" class="form-select @error('vehicle_id') is-invalid @enderror" required>
                            <option value="">Select Vehicle</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" {{ old('vehicle_id', $serviceSchedule->vehicle_id) == $vehicle->id ? 'selected' : '' }}>{{ $vehicle->vehicle_number }}</option>
                            @endforeach
                        </select>
                        @error('vehicle_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Service Type <span class="text-danger">*</span></label>
                        <select name="service_type" class="form-select @error('service_type') is-invalid @enderror" required>
                            <option value="">Select Type</option>
                            <option value="Oil Change" {{ old('service_type', $serviceSchedule->service_type) == 'Oil Change' ? 'selected' : '' }}>Oil Change</option>
                            <option value="General Service" {{ old('service_type', $serviceSchedule->service_type) == 'General Service' ? 'selected' : '' }}>General Service</option>
                            <option value="Tire Rotation" {{ old('service_type', $serviceSchedule->service_type) == 'Tire Rotation' ? 'selected' : '' }}>Tire Rotation</option>
                            <option value="Brake Service" {{ old('service_type', $serviceSchedule->service_type) == 'Brake Service' ? 'selected' : '' }}>Brake Service</option>
                            <option value="Transmission" {{ old('service_type', $serviceSchedule->service_type) == 'Transmission' ? 'selected' : '' }}>Transmission</option>
                            <option value="Battery" {{ old('service_type', $serviceSchedule->service_type) == 'Battery' ? 'selected' : '' }}>Battery</option>
                            <option value="AC Service" {{ old('service_type', $serviceSchedule->service_type) == 'AC Service' ? 'selected' : '' }}>AC Service</option>
                            <option value="Engine" {{ old('service_type', $serviceSchedule->service_type) == 'Engine' ? 'selected' : '' }}>Engine</option>
                            <option value="Other" {{ old('service_type', $serviceSchedule->service_type) == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('service_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                            <option value="upcoming" {{ old('status', $serviceSchedule->status) == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                            <option value="overdue" {{ old('status', $serviceSchedule->status) == 'overdue' ? 'selected' : '' }}>Overdue</option>
                            <option value="completed" {{ old('status', $serviceSchedule->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ old('status', $serviceSchedule->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <h6 class="mt-3 mb-2 fw-semibold">Schedule</h6>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Scheduled Date</label>
                        <input type="date" max="9999-12-31" name="scheduled_date" class="form-control @error('scheduled_date') is-invalid @enderror" value="{{ old('scheduled_date', $serviceSchedule->scheduled_date?->format('Y-m-d')) }}">
                        @error('scheduled_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Scheduled KM</label>
                        <input type="number" step="0.01" min="0" name="scheduled_km" class="form-control @error('scheduled_km') is-invalid @enderror" value="{{ old('scheduled_km', $serviceSchedule->scheduled_km) }}">
                        @error('scheduled_km') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <h6 class="mt-3 mb-2 fw-semibold">Last Service</h6>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Last Service Date</label>
                        <input type="date" max="9999-12-31" name="last_service_date" class="form-control @error('last_service_date') is-invalid @enderror" value="{{ old('last_service_date', $serviceSchedule->last_service_date?->format('Y-m-d')) }}">
                        @error('last_service_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Last Service KM</label>
                        <input type="number" step="0.01" min="0" name="last_service_km" class="form-control @error('last_service_km') is-invalid @enderror" value="{{ old('last_service_km', $serviceSchedule->last_service_km) }}">
                        @error('last_service_km') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <h6 class="mt-3 mb-2 fw-semibold">Service Interval</h6>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Interval (Days)</label>
                        <input type="number" min="0" name="interval_days" class="form-control @error('interval_days') is-invalid @enderror" value="{{ old('interval_days', $serviceSchedule->interval_days) }}">
                        @error('interval_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Interval (KM)</label>
                        <input type="number" step="0.01" min="0" name="interval_km" class="form-control @error('interval_km') is-invalid @enderror" value="{{ old('interval_km', $serviceSchedule->interval_km) }}">
                        @error('interval_km') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2">{{ old('notes', $serviceSchedule->notes) }}</textarea>
                    @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn btn-primary">Update Schedule</button>
                <a href="{{ route('admin.maintenance.service-schedule.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
