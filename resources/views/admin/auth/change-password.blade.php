@extends('admin.layouts.app') 

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg" style="border: none; border-radius: 16px; overflow: hidden;">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                    <div class="text-center mb-3">
                        <div class="avatar avatar-xl bg-label-primary rounded-circle mb-2">
                            <i class='bx bx-key' style="font-size: 2rem;"></i>
                        </div>
                        <h4 class="fw-bold text-primary mb-0">Change Password</h4>
                        <p class="text-muted small">Update your password to keep your account secure</p>
                    </div>
                </div>
                
                <div class="card-body p-4">
                    <form action="{{ route('admin.update.password') }}" method="POST">
                        @csrf
                        
                        <div class="form-floating mb-3 position-relative">
                            <input name="old_password" type="password" class="form-control" id="oldPassword" placeholder="Current Password" required style="padding-left: 40px;">
                            <label for="oldPassword">Current Password</label>
                            <i class='bx bx-lock-open-alt position-absolute text-muted' style="left: 12px; top: 50%; transform: translateY(-50%);"></i>
                            @error('old_password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-floating mb-3 position-relative">
                            <input name="new_password" type="password" class="form-control" id="newPassword" placeholder="New Password" required style="padding-left: 40px;">
                            <label for="newPassword">New Password</label>
                            <i class='bx bx-lock-alt position-absolute text-muted' style="left: 12px; top: 50%; transform: translateY(-50%);"></i>
                            @error('new_password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-floating mb-4 position-relative">
                            <input name="new_password_confirmation" type="password" class="form-control" id="confirmPassword" placeholder="Confirm New Password" required style="padding-left: 40px;">
                            <label for="confirmPassword">Confirm New Password</label>
                            <i class='bx bx-check-shield position-absolute text-muted' style="left: 12px; top: 50%; transform: translateY(-50%);"></i>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold" style="box-shadow: 0 4px 12px rgba(105, 108, 255, 0.25);">
                            Update Password <i class='bx bx-right-arrow-alt ms-1'></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
