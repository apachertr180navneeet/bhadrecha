@extends('front.layouts.app')
@section('title', 'Tracking')
@section('content')
<!-- Page Header Start -->
	<div class="page-header parallaxie">
		<div class="container">
			<div class="row align-items-center">
				<div class="col-lg-12">
					<!-- Page Header Box Start -->
					<div class="page-header-box">
						<h1 class="text-anime-style-2" data-cursor="-opaque">LR Tracking</h1>
						<nav class="wow fadeInUp">
                            <ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="{{ route('landing') }}">home</a></li>
								<li class="breadcrumb-item active" aria-current="page">Tracking</li>
							</ol>
						</nav>
					</div>
					<!-- Page Header Box End -->
				</div>
			</div>
		</div>
	</div>
	<!-- Page Header End -->

    <!-- Page Contact Us Start -->
    <div class="page-contact-us">
        <div class="container">
            <div class="row align-items-center justify-content-center">
                <div class="col-lg-8">
                    <!-- Contact Us Form Start -->
                    <div class="contact-us-form">
@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

                        <!-- Section Title Start -->
                        <div class="section-title">
                            <h2 class="text-anime-style-2" data-cursor="-opaque">Track your <span>Shipment</span></h2>
                        </div>
                        <!-- Section Title End -->

                        <!-- Contact Form Start -->
                        <div class="contact-form">
                            <form id="trackingForm" action="{{ url('/tracking') }}" method="POST" data-toggle="validator" class="wow fadeInUp" data-wow-delay="0.2s">
                                @csrf
                                <div class="row">
                                    <div class="form-group col-md-12 mb-4">
                                        <input type="text" name="lr_number" class="form-control" id="lr_number" placeholder="Enter LR Number" required>
                                        <div class="help-block with-errors"></div>
                                    </div>
    
                                    <div class="col-lg-12">
                                        <div class="contact-form-btn">
                                            <button type="submit" class="btn-default">Track Now</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <!-- Contact Form End -->
                        
                        @if(isset($trackingResult) && $trackingResult)
                        <div class="mt-5 wow fadeInUp">
                            <h3>Tracking Results: {{ $trackingResult->lr_number }}</h3>
                            <table class="table table-bordered mt-3">
                                <tr><th>Status</th><td>{{ $trackingResult->status }}</td></tr>
                                <tr><th>Current Location</th><td>{{ $trackingResult->current_location }}</td></tr>
                                <tr><th>Date</th><td>{{ $trackingResult->date }}</td></tr>
                                <tr><th>Remarks</th><td>{{ $trackingResult->remarks }}</td></tr>
                            </table>
                        </div>
                        @endif

                    </div>
                    <!-- Contact Us Form End -->
                </div>
            </div>
        </div>
    </div>
    <!-- Page Contact Us End -->
@endsection

