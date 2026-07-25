@extends('admin.layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Admin /</span> Settings
    </h4>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Application Settings</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Application Name</label>
                        <input type="text" name="app_name" class="form-control @error('app_name') is-invalid @enderror" value="{{ old('app_name', $settings['app_name'] ?? '') }}">
                        @error('app_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Timezone</label>
                        <select name="app_timezone" class="form-select @error('app_timezone') is-invalid @enderror">
                            <option value="">Select Timezone</option>
                            @foreach(timezone_identifiers_list() as $timezone)
                                <option value="{{ $timezone }}" {{ (old('app_timezone', $settings['app_timezone'] ?? '') == $timezone) ? 'selected' : '' }}>{{ $timezone }}</option>
                            @endforeach
                        </select>
                        @error('app_timezone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Application Logo</label>
                        <input type="file" name="app_logo" class="form-control @error('app_logo') is-invalid @enderror">
                        @error('app_logo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if(isset($settings['app_logo']) && $settings['app_logo'])
                            <div class="mt-2">
                                <img src="{{ asset('uploads/' . $settings['app_logo']) }}" alt="Logo" style="max-height: 100px;">
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="app_email" class="form-control @error('app_email') is-invalid @enderror" value="{{ old('app_email', $settings['app_email'] ?? '') }}">
                        @error('app_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="text" name="app_phone" class="form-control @error('app_phone') is-invalid @enderror" value="{{ old('app_phone', $settings['app_phone'] ?? '') }}">
                        @error('app_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Address</label>
                        <textarea name="app_address" class="form-control @error('app_address') is-invalid @enderror">{{ old('app_address', $settings['app_address'] ?? '') }}</textarea>
                        @error('app_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Update Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
