@extends('admin.layouts.login_layout')

@section('style')
<style>
    body {
        background: #f0f2f5;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Public Sans', sans-serif;
        position: relative;
    }

    .bg-pattern {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background-image: radial-gradient(#696cff 1px, transparent 1px);
        background-size: 30px 30px;
        opacity: 0.1;
        z-index: 0;
    }

    .auth-wrapper {
        position: relative;
        z-index: 1;
        width: 100%;
        max-width: 480px;
        padding: 20px;
    }

    .auth-card {
        background: #ffffff;
        border-radius: 24px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
        padding: 3rem 2.5rem;
        border: 1px solid rgba(255, 255, 255, 0.5);
    }

    .brand-header { text-align: center; margin-bottom: 2.5rem; }

    .brand-icon {
        width: 70px; height: 70px;
        background: linear-gradient(135deg, #696cff, #8558ff);
        color: #fff; border-radius: 20px;
        display: flex; align-items: center; justify-content: center;
        font-size: 32px; margin: 0 auto 1.5rem auto;
        box-shadow: 0 8px 20px rgba(105, 108, 255, 0.3);
    }

    .brand-title { font-size: 1.75rem; font-weight: 800; color: #1e293b; margin: 0; }
    .brand-subtitle { color: #64748b; font-size: 0.95rem; margin-top: 0.5rem; }

    .form-floating { margin-bottom: 1rem; position: relative; }

    .form-floating > .form-control {
        height: 56px; border-radius: 12px; border: 1px solid #e2e8f0;
        background: #f8fafc; padding-left: 45px; font-size: 0.95rem;
    }

    .form-floating > .form-control:focus {
        border-color: #696cff; box-shadow: 0 0 0 4px rgba(105, 108, 255, 0.1); background: #fff;
    }

    .form-floating > label { padding-left: 45px; color: #94a3b8; }

    .input-icon {
        position: absolute; left: 15px; top: 50%; transform: translateY(-50%);
        color: #94a3b8; font-size: 18px; z-index: 5; pointer-events: none;
    }

    .password-toggle {
        position: absolute; right: 15px; top: 50%; transform: translateY(-50%);
        cursor: pointer; color: #94a3b8; font-size: 18px; z-index: 5;
    }
    .password-toggle:hover { color: #696cff; }

    .btn-primary {
        width: 100%; height: 52px; border-radius: 12px; font-weight: 600; font-size: 1rem;
        background: linear-gradient(135deg, #696cff, #8558ff); border: none;
        transition: all 0.3s; margin-top: 1rem; color: #fff;
        box-shadow: 0 8px 20px rgba(105, 108, 255, 0.25);
    }

    .btn-primary:hover {
        transform: translateY(-2px); box-shadow: 0 12px 25px rgba(105, 108, 255, 0.35);
        background: linear-gradient(135deg, #5b5eef, #7244e8);
    }

    .back-link {
        display: flex; align-items: center; justify-content: center; margin-top: 2rem;
        color: #696cff; text-decoration: none; font-weight: 600; font-size: 0.95rem;
        transition: color 0.3s;
    }
    .back-link:hover { color: #4c53c4; }

    @media (max-width: 576px) {
        .auth-wrapper { padding: 15px; }
        .auth-card { padding: 2rem 1.5rem; }
        .brand-title { font-size: 1.5rem; }
        .form-floating > .form-control { height: 50px; padding-left: 40px; }
    }
</style>
@endsection

@section('content')
<div class="bg-pattern"></div>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="brand-header">
            <div class="brand-icon"><i class='bx bx-reset'></i></div>
            <h2 class="brand-title">Set New Password</h2>
            <p class="brand-subtitle">Create a strong new password</p>
        </div>

        <form method="POST" action="{{ route('admin.reset.password.post') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <div class="form-floating position-relative">
                <input type="password" class="form-control" name="password" id="password" placeholder="New Password" required>
                <label for="password">New Password</label>
                <i class='bx bx-lock-alt input-icon'></i>
                <span class="password-toggle" onclick="togglePassword('password', 'toggleIcon1')"><i class='bx bx-hide' id="toggleIcon1"></i></span>
                @if ($errors->has('password'))
                    <div class="text-danger small mt-1">{{ $errors->first('password') }}</div>
                @endif
            </div>

            <div class="form-floating position-relative">
                <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" placeholder="Confirm Password" required>
                <label for="password_confirmation">Confirm Password</label>
                <i class='bx bx-lock-alt input-icon'></i>
                <span class="password-toggle" onclick="togglePassword('password_confirmation', 'toggleIcon2')"><i class='bx bx-hide' id="toggleIcon2"></i></span>
            </div>

            <button type="submit" class="btn btn-primary">Reset Password <i class='bx bx-right-arrow-alt ms-1'></i></button>
        </form>

        <a href="{{ route('admin.login') }}" class="back-link">
            <i class='bx bx-left-arrow-alt me-1'></i> Back to Login
        </a>
    </div>
</div>

<script>
    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('bx-hide', 'bx-show');
        } else {
            input.type = 'password';
            icon.classList.replace('bx-show', 'bx-hide');
        }
    }
</script>
@endsection
