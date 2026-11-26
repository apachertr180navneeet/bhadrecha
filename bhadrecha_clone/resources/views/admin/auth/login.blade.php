@extends('admin.layouts.login_layout')

@section('style')
<style>
    body, html {
        height: 100%;
        margin: 0;
        padding: 0;
        overflow: hidden; /* Prevent scrolling on desktop */
    }
    @media (max-width: 992px) {
        body, html {
            overflow: auto;
        }
    }

    .auth-wrapper {
        height: 100vh;
        width: 100%;
    }

    .auth-cover-bg {
        background-image: url('https://images.unsplash.com/photo-1580674684081-7617fbf3d745?ixlib=rb-4.0.3&auto=format&fit=crop&w=1974&q=80');
        background-size: cover;
        background-position: center;
        position: relative;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
    }

    /* Modern Gradient Overlay */
    .auth-cover-bg::before {
        content: "";
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: linear-gradient(135deg, rgba(105, 108, 255, 0.9) 0%, rgba(115, 103, 240, 0.8) 100%);
        z-index: 1;
    }

    .auth-cover-content {
        position: relative;
        z-index: 2;
        padding: 4rem;
        text-align: left;
        max-width: 600px;
        color: #ffffff;
    }

    .brand-icon {
        width: 80px;
        height: 80px;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 2rem;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .brand-icon i {
        color: #ffffff !important;
    }

    .auth-cover-content h1 {
        color: #ffffff;
        font-weight: 800;
        letter-spacing: -1px;
        margin-bottom: 1.5rem;
        line-height: 1.1;
        text-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }

    .auth-cover-content p {
        color: #f0f2f5;
        font-size: 1.1rem;
        line-height: 1.6;
        text-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    }

    @media (max-width: 992px) {
        .auth-form-side {
            padding: 1rem;
        }
        .auth-form-container {
            padding: 1.5rem;
            box-shadow: none;
        }
        .logo-text {
            font-size: 20px;
        }
    }

    .mask-bottom-left {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        z-index: 3;
    }

    /* Form Side Styling */
    .auth-form-side {
        background: #f8f9fa;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }

    .auth-form-container {
        background: #fff;
        padding: 2.5rem;
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.05);
        width: 100%;
        max-width: 420px;
        transition: transform 0.3s;
    }

    .auth-form-container:hover {
        transform: translateY(-2px);
    }

    .input-group-text {
        background: #f0f2f5;
        border: 1px solid #e2e8f0;
        border-right: none;
    }
    
    .input-group .form-control {
        border: 1px solid #e2e8f0;
        padding: 0.7rem 1rem;
        background: #f8fafc;
    }
    
    .input-group .form-control:focus {
        background: #fff;
        border-color: #696cff;
        box-shadow: 0 0 0 3px rgba(105, 108, 255, 0.15);
    }

    .input-group:focus-within .input-group-text {
        border-color: #696cff;
        background: #fff;
        color: #696cff;
    }

    .btn-primary {
        padding: 0.8rem;
        font-weight: 600;
        background: linear-gradient(135deg, #696cff 0%, #8558ff 100%);
        border: none;
        box-shadow: 0 4px 15px rgba(105, 108, 255, 0.3);
        transition: all 0.3s;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(105, 108, 255, 0.4);
    }

    .logo-text {
        font-size: 24px;
        font-weight: 800;
        color: #696cff;
        display: inline-block;
        margin-bottom: 1rem;
    }
</style>
@endsection

@section('content')
<div class="row g-0 auth-wrapper">
    <!-- Left: Cover Image -->
    <div class="col-lg-7 d-none d-lg-block p-0">
        <div class="auth-cover-bg">
            <div class="auth-cover-content">
                <div class="brand-icon">
                    <i class='bx bxs-truck fs-1'></i>
                </div>
                <h1>Manage your logistics<br>with precision.</h1>
                <p>Secure access to your transporter management dashboard, real-time tracking, and analytics.</p>
            </div>
            <!-- SVG Wave Bottom -->
            <div class="mask-bottom-left">
                <svg viewBox="0 0 1920 75" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 75H1920V47.2193C1444.83 10.0704 948.833 10.0704 472.833 47.2193C317.611 59.3017 158.556 75 0 75Z" fill="#f8f9fa" fill-opacity="0.1"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Right: Login Form -->
    <div class="col-lg-5 auth-form-side">
        <div class="auth-form-container">
            <div class="text-center mb-4">
                <span class="logo-text">{{ config('app.name') }}</span>
                <h4 class="mb-1 fw-bold">Welcome back! 👋</h4>
                <p class="text-muted">Please sign-in to your account</p>
            </div>

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form id="formAuthentication" action="{{ route('admin.login.post') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-bold small">Email Address</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="bx bx-envelope"></i></span>
                        <input type="text" class="form-control" id="email" name="email" value="{{ old('email') }}" placeholder="name@company.com" autofocus>
                    </div>
                    @error('email')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-4">
                    <div class="d-flex justify-content-between">
                        <label class="form-label fw-bold small">Password</label>
                        <a href="{{ route('admin.forget.password.get') }}" class="small text-decoration-none text-primary">
                            Forgot Password?
                        </a>
                    </div>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="bx bx-lock-alt"></i></span>
                        <input type="password" id="password" class="form-control" name="password" placeholder="••••••••" />
                        <span class="input-group-text cursor-pointer" onclick="togglePassword()"><i class="bx bx-hide" id="toggleIcon"></i></span>
                    </div>
                    @error('password')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" />
                        <label class="form-check-label small" for="remember"> Remember Me </label>
                    </div>
                </div>
                
                <button class="btn btn-primary d-grid w-100" type="submit">Sign in</button>
            </form>
            
            <p class="text-center mt-4 mb-0 small text-muted">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </p>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.replace('bx-hide', 'bx-show');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.replace('bx-show', 'bx-hide');
    }
}
</script>
@endsection
