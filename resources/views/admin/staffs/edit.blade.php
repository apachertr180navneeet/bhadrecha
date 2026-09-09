@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Edit Staff Member</h4>
            <small class="text-muted">Update staff record for {{ $staff->first_name }} {{ $staff->last_name }}</small>
        </div>
        <a href="{{ route('admin.staffs.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Back to List
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.staffs.update', $staff->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">First Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('first_name') is-invalid @enderror" name="first_name" value="{{ old('first_name', $staff->first_name) }}" required>
                        @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Last Name</label>
                        <input type="text" class="form-control @error('last_name') is-invalid @enderror" name="last_name" value="{{ old('last_name', $staff->last_name) }}">
                        @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $staff->email) }}">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone', $staff->phone) }}">
                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea class="form-control @error('address') is-invalid @enderror" name="address" rows="2">{{ old('address', $staff->address) }}</textarea>
                    @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Position</label>
                        <input type="text" class="form-control @error('position') is-invalid @enderror" name="position" value="{{ old('position', $staff->position) }}" placeholder="e.g. Hair Stylist">
                        @error('position') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Salary</label>
                        <input type="number" step="0.01" class="form-control @error('salary') is-invalid @enderror" name="salary" value="{{ old('salary', $staff->salary) }}" min="0">
                        @error('salary') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Store <span class="text-danger">*</span></label>
                        <select class="form-select @error('store_id') is-invalid @enderror" name="store_id" required>
                            <option value="">Select Store</option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}" {{ old('store_id', $staff->store_id) == $store->id ? 'selected' : '' }}>{{ $store->store_name }}</option>
                            @endforeach
                        </select>
                        @error('store_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Shift Time</label>
                        <input type="text" class="form-control @error('shift_time') is-invalid @enderror" name="shift_time" value="{{ old('shift_time', $staff->shift_time) }}" placeholder="e.g. 9 AM - 5 PM">
                        @error('shift_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Working Hours</label>
                        <input type="text" class="form-control @error('working_hours') is-invalid @enderror" name="working_hours" value="{{ old('working_hours', $staff->working_hours) }}" placeholder="e.g. 8 Hours">
                        @error('working_hours') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Joining Date</label>
                        <input type="date" class="form-control @error('joining_date') is-invalid @enderror" name="joining_date" value="{{ old('joining_date', $staff->joining_date ? \Carbon\Carbon::parse($staff->joining_date)->format('Y-m-d') : '') }}">
                        @error('joining_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">ID Proof Document</label>
                        <input type="file" class="form-control @error('id_proof') is-invalid @enderror" name="id_proof" accept=".pdf,.jpg,.jpeg,.png">
                        @error('id_proof') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @if($staff->id_proof)
                            <small class="text-muted">Current: <a href="{{ $staff->id_proof }}" target="_blank">View Document</a></small>
                        @else
                            <small class="text-muted">Accepted: PDF, JPG, PNG. Max 2MB.</small>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Qualification Document</label>
                        <input type="file" class="form-control @error('qualification_doc') is-invalid @enderror" name="qualification_doc" accept=".pdf,.jpg,.jpeg,.png">
                        @error('qualification_doc') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @if($staff->qualification_doc)
                            <small class="text-muted">Current: <a href="{{ $staff->qualification_doc }}" target="_blank">View Document</a></small>
                        @else
                            <small class="text-muted">Accepted: PDF, JPG, PNG. Max 2MB.</small>
                        @endif
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Update Staff</button>
                    <a href="{{ route('admin.staffs.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
