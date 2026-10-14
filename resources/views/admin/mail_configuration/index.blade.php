@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1" style="color: #062E39;">
                <i class="bx bx-envelope me-2 text-primary"></i>User-wise Email Configuration
            </h4>
            <p class="text-muted mb-0">Configure your personal SMTP or Gmail credentials so emails are sent directly from your account.</p>
        </div>
        @if($isSuperAdmin && count($userList) > 0)
        <div class="d-flex align-items-center gap-2">
            <label class="form-label mb-0 fw-semibold text-muted text-nowrap">Configure For User:</label>
            <form method="GET" action="{{ route('admin.mail-configuration.index') }}" class="d-inline-block">
                <select name="user_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach($userList as $u)
                        <option value="{{ $u->id }}" {{ $user->id == $u->id ? 'selected' : '' }}>
                            {{ $u->full_name }} ({{ $u->email }})
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Configuration Form Column -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold" style="color: #062E39;">
                        <i class="bx bx-slider-alt me-2 text-primary"></i>SMTP & Mail Server Settings
                    </h5>
                    <span class="badge {{ $config->is_configured ? 'bg-success' : 'bg-warning' }}">
                        {{ $config->is_configured ? 'Active & Configured' : 'Using System Default Mailer' }}
                    </span>
                </div>
                <div class="card-body p-4">
                    <!-- Quick Provider Selector -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Select Email Provider Preset:</label>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <button type="button" class="btn btn-outline-primary w-100 py-2 d-flex align-items-center justify-content-center gap-2 provider-preset-btn" data-provider="gmail">
                                    <i class="bx bxl-google text-danger fs-5"></i>
                                    <span class="fw-semibold">Google Gmail</span>
                                </button>
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-outline-primary w-100 py-2 d-flex align-items-center justify-content-center gap-2 provider-preset-btn" data-provider="outlook">
                                    <i class="bx bxl-microsoft text-info fs-5"></i>
                                    <span class="fw-semibold">Microsoft Outlook</span>
                                </button>
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-outline-primary w-100 py-2 d-flex align-items-center justify-content-center gap-2 provider-preset-btn" data-provider="custom">
                                    <i class="bx bx-server text-secondary fs-5"></i>
                                    <span class="fw-semibold">Custom SMTP</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.mail-configuration.update') }}" id="mailConfigForm">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                        <input type="hidden" name="provider" id="providerInput" value="{{ old('provider', $config->provider ?? 'smtp') }}">

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">SMTP Host <span class="text-danger">*</span></label>
                                <input type="text" name="mail_host" id="mail_host" class="form-control @error('mail_host') is-invalid @enderror" value="{{ old('mail_host', $config->mail_host ?? 'smtp.gmail.com') }}" placeholder="e.g. smtp.gmail.com" required>
                                @error('mail_host')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">SMTP Port <span class="text-danger">*</span></label>
                                <input type="number" name="mail_port" id="mail_port" class="form-control @error('mail_port') is-invalid @enderror" value="{{ old('mail_port', $config->mail_port ?? 587) }}" placeholder="587" required>
                                @error('mail_port')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Encryption</label>
                                <select name="mail_encryption" id="mail_encryption" class="form-select @error('mail_encryption') is-invalid @enderror">
                                    <option value="tls" {{ old('mail_encryption', $config->mail_encryption ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS (Port 587)</option>
                                    <option value="ssl" {{ old('mail_encryption', $config->mail_encryption) === 'ssl' ? 'selected' : '' }}>SSL (Port 465)</option>
                                    <option value="none" {{ (old('mail_encryption', $config->mail_encryption) === null || old('mail_encryption', $config->mail_encryption) === 'none') ? 'selected' : '' }}>None</option>
                                </select>
                                @error('mail_encryption')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Email / Username <span class="text-danger">*</span></label>
                                <input type="email" name="mail_username" id="mail_username" class="form-control @error('mail_username') is-invalid @enderror" value="{{ old('mail_username', $config->mail_username ?? $user->email) }}" placeholder="your-email@example.com" required>
                                @error('mail_username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">
                                    SMTP Password / App Password 
                                    @if($config->has_password)
                                        <span class="badge bg-label-success ms-2"><i class="bx bx-check-shield me-1"></i>Saved & Encrypted</span>
                                    @else
                                        <span class="text-danger">*</span>
                                    @endif
                                </label>
                                <div class="input-group">
                                    <input type="password" name="mail_password" id="mail_password" class="form-control @error('mail_password') is-invalid @enderror" placeholder="{{ $config->has_password ? '•••••••••••••••• (Leave blank to keep existing)' : 'Enter SMTP password or App Password' }}" {{ $config->has_password ? '' : 'required' }}>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePasswordBtn">
                                        <i class="bx bx-show" id="passwordIcon"></i>
                                    </button>
                                </div>
                                <small class="text-muted d-block mt-1">Passwords are encrypted at rest with AES-256 and never logged or exposed.</small>
                                @error('mail_password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">From Email Address</label>
                                <input type="email" name="mail_from_address" id="mail_from_address" class="form-control @error('mail_from_address') is-invalid @enderror" value="{{ old('mail_from_address', $config->mail_from_address ?? $user->email) }}" placeholder="e.g. {{ $user->email }}">
                                <small class="text-muted">Must be authorized on your mail server.</small>
                                @error('mail_from_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">From Sender Name</label>
                                <input type="text" name="mail_from_name" id="mail_from_name" class="form-control @error('mail_from_name') is-invalid @enderror" value="{{ old('mail_from_name', $config->mail_from_name ?? $user->full_name) }}" placeholder="e.g. {{ $user->full_name }}">
                                @error('mail_from_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-12 mt-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $config->is_active ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="is_active">
                                        Enable user-wise sending (If disabled, the system fallback mailer will be used)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bx bx-save me-1"></i> Save Configuration
                            </button>
                            <button type="button" class="btn btn-outline-info" id="openTestModalBtn">
                                <i class="bx bx-paper-plane me-1"></i> Test Email Connection
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Information & Status Column -->
        <div class="col-lg-4">
            <!-- Connection Status Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold" style="color: #062E39;">
                        <i class="bx bx-pulse me-2 text-primary"></i>Connection Verification Status
                    </h6>
                </div>
                <div class="card-body p-3">
                    @if($config->last_tested_at)
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted small">Status:</span>
                            @if($config->last_test_status === 'success')
                                <span class="badge bg-success"><i class="bx bx-check me-1"></i>Operational</span>
                            @else
                                <span class="badge bg-danger"><i class="bx bx-x me-1"></i>Verification Failed</span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted small">Last Tested:</span>
                            <span class="small fw-semibold">{{ $config->last_tested_at->format('d M Y, h:i A') }}</span>
                        </div>
                        @if($config->last_test_message)
                            <div class="p-2 rounded bg-light border mt-2 small text-break">
                                <strong>Details:</strong><br>{{ $config->last_test_message }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-3 text-muted">
                            <i class="bx bx-info-circle fs-3 d-block mb-1 opacity-50"></i>
                            <span class="small">No connection tests have been recorded yet. Click <strong>Test Email Connection</strong> to verify your SMTP setup.</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Gmail Instructions Card -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold" style="color: #062E39;">
                        <i class="bx bxl-google me-2 text-danger"></i>Gmail Setup Instructions
                    </h6>
                </div>
                <div class="card-body p-3 small">
                    <p class="mb-2">If you are using a <strong>@gmail.com</strong> or Google Workspace email, Google requires an <strong>App Password</strong> rather than your standard account password:</p>
                    <ol class="ps-3 mb-2">
                        <li class="mb-1">Go to your <a href="https://myaccount.google.com/security" target="_blank" rel="noopener">Google Account Security</a>.</li>
                        <li class="mb-1">Enable <strong>2-Step Verification</strong> if not already active.</li>
                        <li class="mb-1">Search for <strong>"App passwords"</strong> in the security settings.</li>
                        <li class="mb-1">Create an App Password (name it e.g. <em>Transport ERP</em>).</li>
                        <li class="mb-1">Paste the generated <strong>16-character code</strong> into the SMTP Password field above.</li>
                    </ol>
                    <div class="alert alert-info py-2 px-3 mb-0 small">
                        <i class="bx bx-bulb me-1"></i> <strong>Port & Security:</strong> Host: <code>smtp.gmail.com</code> | Port: <code>587</code> | Encryption: <code>TLS</code>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Test Connection Modal -->
<div class="modal fade" id="testEmailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold" style="color: #062E39;">
                    <i class="bx bx-paper-plane me-2 text-primary"></i>Test Email Dispatch
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="testEmailForm">
                @csrf
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Send a real-time verification email to confirm that your SMTP credentials, port, and security handshakes are working properly.</p>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Recipient Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="test_email" id="test_email" class="form-control" value="{{ $user->email }}" required>
                        <small class="text-muted">A test message will be dispatched to this address.</small>
                    </div>

                    <div id="testResultFeedback" class="mt-3" style="display:none;"></div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="sendTestBtn">
                        <i class="bx bx-send me-1"></i> Dispatch Test Email
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    // Password visibility toggle
    $('#togglePasswordBtn').on('click', function() {
        const input = $('#mail_password');
        const icon = $('#passwordIcon');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('bx-show').addClass('bx-hide');
        } else {
            input.attr('type', 'password');
            icon.removeClass('bx-hide').addClass('bx-show');
        }
    });

    // Provider Presets
    $('.provider-preset-btn').on('click', function() {
        const provider = $(this).data('provider');
        $('#providerInput').val(provider);
        $('.provider-preset-btn').removeClass('active btn-primary text-white').addClass('btn-outline-primary');
        $(this).removeClass('btn-outline-primary').addClass('active btn-primary text-white');

        if (provider === 'gmail') {
            $('#mail_host').val('smtp.gmail.com');
            $('#mail_port').val('587');
            $('#mail_encryption').val('tls');
        } else if (provider === 'outlook') {
            $('#mail_host').val('smtp.office365.com');
            $('#mail_port').val('587');
            $('#mail_encryption').val('tls');
        }
    });

    // Open Test Modal
    $('#openTestModalBtn').on('click', function() {
        $('#testResultFeedback').hide().empty();
        $('#testEmailModal').modal('show');
    });

    // Send Test Email AJAX
    $('#testEmailForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#sendTestBtn');
        const $feedback = $('#testResultFeedback');
        
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Testing Connection...');
        $feedback.hide().empty();

        const formData = {
            _token: '{{ csrf_token() }}',
            user_id: '{{ $user->id }}',
            test_email: $('#test_email').val(),
            mail_host: $('#mail_host').val(),
            mail_port: $('#mail_port').val(),
            mail_encryption: $('#mail_encryption').val(),
            mail_username: $('#mail_username').val(),
            mail_password: $('#mail_password').val(),
        };

        $.ajax({
            url: '{{ route("admin.mail-configuration.test") }}',
            method: 'POST',
            data: formData,
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="bx bx-send me-1"></i> Dispatch Test Email');
                if (res.success) {
                    $feedback.html('<div class="alert alert-success py-2 px-3 mb-0 small"><i class="bx bx-check-circle me-1"></i> <strong>Success!</strong> ' + res.message + '</div>').show();
                    setTimeout(function() {
                        location.reload();
                    }, 2500);
                } else {
                    $feedback.html('<div class="alert alert-danger py-2 px-3 mb-0 small"><i class="bx bx-error-circle me-1"></i> <strong>Connection Failed:</strong> ' + res.message + '</div>').show();
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html('<i class="bx bx-send me-1"></i> Dispatch Test Email');
                let err = 'An unexpected error occurred while testing.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    err = xhr.responseJSON.message;
                }
                $feedback.html('<div class="alert alert-danger py-2 px-3 mb-0 small"><i class="bx bx-error-circle me-1"></i> <strong>Error:</strong> ' + err + '</div>').show();
            }
        });
    });
});
</script>
@endsection
