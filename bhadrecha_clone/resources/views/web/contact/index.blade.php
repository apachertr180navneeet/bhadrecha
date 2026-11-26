@extends('web.layouts.app')

@section('title', 'Contact Us - The Infinity Unisex Salon and Spa')
@section('meta_description', 'Contact The Infinity Unisex Salon and Spa. Find our location, phone number, working hours, and send us a message.')

@section('content')

<!-- Page Title -->
<section class="page_title s-parallax s-overlay ls title-overlay s-py-25">
	<div class="container">
		<div class="row">
			<div class="fw-divider-space hidden-below-lg mt-130"></div>
			<div class="fw-divider-space hidden-above-lg mt-60"></div>
			<div class="col-md-12 text-center">
				<h1>Contacts</h1>
				<ol class="breadcrumb">
					<li class="breadcrumb-item">
						<a href="{{ url('/') }}">Home</a>
					</li>
					<li class="breadcrumb-item active">
						Contacts
					</li>
				</ol>
			</div>
			<div class="fw-divider-space hidden-below-lg mt-130"></div>
			<div class="fw-divider-space hidden-above-lg mt-60"></div>
		</div>
	</div>
</section>

<!-- Contact Section -->
<section class="ls background-contact s-pt-60 s-pt-lg-100 s-pt-xl-150 s-pb-60 s-pb-xl-90 c-mb-20 c-gutter-60">
	<div class="container">
		<div class="row">

			<div class="col-lg-7 col-xl-8">
				<h3 class="mt-0 mb-35 text-capitalize">get in touch</h3>
				
				@if(session('success'))
					<div class="alert alert-success">
						{{ session('success') }}
					</div>
				@endif

				<form class="contact-form c-mb-20 c-gutter-20" method="post" action="{{ url('/contact') }}">
					@csrf
					<div class="row">
						<div class="col-sm-6">
							<div class="form-group">
								<i class="flaticon-profile"></i>
								<input type="text" name="name" class="form-control" placeholder="name" required>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form-group">
								<i class="flaticon-volume"></i>
								<input type="tel" name="phone" class="form-control" placeholder="Phone Number" required>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-12">
							<div class="form-group">
								<i class="flaticon-envelope"></i>
								<input type="email" name="email" class="form-control" placeholder="email" required>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-12">
							<div class="form-group">
								<i class="flaticon-clip"></i>
								<textarea rows="4" cols="45" name="message" class="form-control" placeholder="message" required></textarea>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-12">
							<div class="fw-divider-space hidden-below-lg mt-60"></div>
							<div class="form-group">
								<button type="submit" class="btn btn-outline-maincolor">send message</button>
							</div>
						</div>
					</div>
				</form>
			</div>

			<div class="col-lg-5 col-xl-4">
				<div class="fw-divider-space hidden-above-lg mt-40"></div>
				<h3 class="mt-0 mb-25 text-capitalize">Contact info</h3>
				<div class="media mb-15">
					<h5 class="fs-20 mb-0 min-w-100">Address:</h5>
					<div class="media-body ml-0 d-flex flex-column">
						<span>1928 Grant View Drive</span>
					</div>
				</div>
				<div class="media mb-15">
					<h5 class="fs-20 mb-0 min-w-100">Phone:</h5>
					<div class="media-body ml-0 d-flex flex-column">
						<span>+1 (800) 123-45-67</span>
						<span>+1 (800) 123-45-68</span>
					</div>
				</div>
				<div class="media mb-20">
					<h5 class="fs-20 mb-30 min-w-100">Email:</h5>
					<div class="media-body ml-0 d-flex flex-column">
						<span>info@theinfinitysalon.com</span>
					</div>
				</div>
				
				<h3 class="mt-0 mb-20 text-capitalize">Open Hours</h3>
				<div class="media mb-15">
					<h5 class="fs-20 mb-0 min-w-100">Weekdays:</h5>
					<div class="media-body ml-0 d-flex flex-column">
						<span>9:00 - 17:00</span>
					</div>
				</div>
				<div class="media mb-15">
					<h5 class="fs-20 mb-0 min-w-100">Saturday:</h5>
					<div class="media-body ml-0 d-flex flex-column">
						<span>10:00 - 15:00</span>
					</div>
				</div>
				<div class="media mb-15">
					<h5 class="fs-20 mb-0 min-w-100">Sunday:</h5>
					<div class="media-body ml-0 d-flex flex-column">
						<span>Closed</span>
					</div>
				</div>
			</div>

		</div>
	</div>
</section>
<div class="fw-divider-space hidden-below-lg mt-50"></div>

@endsection
