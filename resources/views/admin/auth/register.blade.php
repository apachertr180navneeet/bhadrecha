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
        position: absolute; top: 0; left: 0; right: 0; bottom: 0;
        background-image: radial-gradient(#696cff 1px, transparent 1px);
        background-size: 30px 30px; opacity: 0.1; z-index: 0;
    }
    .auth-wrapper {
        position: relative; z-index: 1; width: 100%; max-width: 500px; padding: 20px;
    }
    .auth-card {
        background: #ffffff; border-radius: 24px; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
        padding: 2.5rem; border: 1px solid rgba(255, 255, 255, 0.5);
    }
    .brand-header { text-align: center; margin-bottom: 2rem; }
    .brand-icon {
        width: 70px; height: 70px; background: linear-gradient(135deg, #696cff, #8558ff);
        color: #fff; border-radius: 20px; display: flex; align-items: center; justify-content: center;
        font-size: 32px; margin: 0 auto 1rem auto; box-shadow: 0 8px 20px rgba(105, 108, 255, 0.3);
    }
    .brand-title { font-size: 1.5rem; font-weight: 800; color: #1e293b; margin: 0; }
    .brand-subtitle { color: #64748b; font-size: 0.95rem; margin-top: 0.25rem; }
    .form-floating { margin-bottom: 1rem; position: relative; }
    .form-floating > .form-control {
        height: 50px; border-radius: 12px; border: 1px solid #e2e8f0; background: #f8fafc;
        padding-left: 45px; font-size: 0.95rem;
    }
    .form-floating > .form-control:focus {
        border-color: #696cff; box-shadow: 0 0 0 3px rgba(105, 108, 255, 0.1); background: #fff;
    }
    .form-floating > label { padding-left: 45px; color: #94a3b8; }
    .input-icon {
        position: absolute; left: 15px; top: 50%; transform: translateY(-50%);
        color: #94a3b8; font-size: 18px; z-index: 5; pointer-events: none;
    }
    .btn-register {
        width: 100%; height: 50px; border-radius: 12px; font-weight: 600; font-size: 1rem;
        background: linear-gradient(135deg, #696cff, #8558ff); border: none;
        transition: all 0.3s; margin-top: 0.5rem; color: #fff;
        box-shadow: 0 8px 20px rgba(105, 108, 255, 0.25);
    }
    .btn-register:hover {
        transform: translateY(-2px); box-shadow: 0 12px 25px rgba(105, 108, 255, 0.35);
    }
    .login-link { text-align: center; margin-top: 1.5rem; color: #64748b; font-size: 0.9rem; }
    .login-link a { color: #696cff; text-decoration: none; font-weight: 600; }
    .login-link a:hover { text-decoration: underline; }

    @media (max-width: 576px) {
        .auth-wrapper { padding: 15px; }
        .auth-card { padding: 1.5rem; }
        .brand-title { font-size: 1.25rem; }
        .form-floating > .form-control { height: 45px; padding-left: 40px; font-size: 0.9rem; }
        .btn-register { height: 45px; font-size: 0.9rem; }
    }
</style>
@endsection

@section('content')
<div class="bg-pattern"></div>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="brand-header">
            <div class="brand-icon"><i class='bx bxs-user-plus'></i></div>
            <h2 class="brand-title">Create Account</h2>
            <p class="brand-subtitle">Join the Transporter Admin Portal</p>
        </div>

        <form method="POST" action="{{ route('admin.register') }}">
            @csrf
            <div class="form-floating position-relative">
                <input class="form-control" name="username" value="{{ old('username') }}" required type="text" placeholder="Username">
                <label>Username</label>
                <i class='bx bx-user input-icon'></i>
                @if ($errors->has('username')) <div class="text-danger small mt-1">{{ $errors->first('username') }}</div> @endif
            </div>

            <div class="form-floating position-relative">
                <input class="form-control" name="email" value="{{ old('email') }}" required type="email" placeholder="Email">
                <label>Email Address</label>
                <i class='bx bx-envelope input-icon'></i>
                @if ($errors->has('email')) <div class="text-danger small mt-1">{{ $errors->first('email') }}</div> @endif
            </div>

            <div class="form-floating position-relative">
                <input class="form-control" name="password" required type="password" placeholder="Password">
                <label>Password</label>
                <i class='bx bx-lock-alt input-icon'></i>
                @if ($errors->has('password')) <div class="text-danger small mt-1">{{ $errors->first('password') }}</div> @endif
            </div>

            <div class="form-floating position-relative">
                <input class="form-control" name="password_confirmation" required type="password" placeholder="Confirm Password">
                <label>Confirm Password</label>
                <i class='bx bx-lock input-icon'></i>
            </div>
                   
            <button type="submit" class="btn btn-register">Get Started <i class='bx bx-arrow-from-right ms-1'></i></button>
        </form>

        <div class="login-link">
            Already have an account? <a href="{{ route('admin.login') }}">Sign in</a>
        </div>
    </div>
</div>
@endsection
