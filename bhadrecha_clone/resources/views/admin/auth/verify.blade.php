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
        padding: 3rem 2.5rem; border: 1px solid rgba(255, 255, 255, 0.5); text-align: center;
    }
    .brand-icon {
        width: 80px; height: 80px; background: linear-gradient(135deg, #696cff, #8558ff);
        color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center;
        font-size: 40px; margin: 0 auto 1.5rem auto; box-shadow: 0 8px 20px rgba(105, 108, 255, 0.3);
    }
    .brand-title { font-size: 1.5rem; font-weight: 800; color: #1e293b; margin-bottom: 1rem; }
    .brand-subtitle { color: #64748b; font-size: 0.95rem; margin-bottom: 2rem; }
    
    .btn-resend {
        width: 100%; height: 50px; border-radius: 12px; font-weight: 600; font-size: 1rem;
        background: linear-gradient(135deg, #696cff, #8558ff); border: none;
        transition: all 0.3s; color: #fff;
        box-shadow: 0 8px 20px rgba(105, 108, 255, 0.25);
    }
    .btn-resend:hover {
        transform: translateY(-2px); box-shadow: 0 12px 25px rgba(105, 108, 255, 0.35);
    }
    .login-link { margin-top: 1.5rem; color: #696cff; text-decoration: none; font-weight: 600; }

    @media (max-width: 576px) {
        .auth-wrapper { padding: 15px; }
        .auth-card { padding: 2rem 1.5rem; }
        .brand-title { font-size: 1.25rem; }
        .btn-resend { height: 45px; font-size: 0.9rem; }
    }
</style>
@endsection

@section('content')
<div class="bg-pattern"></div>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="brand-icon"><i class='bx bx-envelope-open'></i></div>
        <h2 class="brand-title">Verify Your Email</h2>
        <p class="brand-subtitle">We've sent a verification link to your email address. Please check your inbox.</p>

        @if (session('resent'))
            <div class="alert alert-success mb-3 text-start">
                <i class='bx bx-check-circle me-2'></i> A fresh verification link has been sent!
            </div>
        @endif

        <form method="POST" action="{{ route('admin.verification.resend') }}">
            @csrf
            <button type="submit" class="btn btn-resend">Resend Verification Link</button>
        </form>

        <a href="{{ route('admin.login') }}" class="login-link d-inline-block mt-3">
            <i class='bx bx-left-arrow-alt me-1'></i> Back to Login
        </a>
    </div>
</div>
@endsection
