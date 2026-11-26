@extends('web.layouts.app')

@section('title', 'About Us - The Infinity Unisex Salon and Spa')
@section('meta_description', 'Learn about The Infinity Unisex Salon and Spa - our story, team, and commitment to beauty')

@section('content')

<!-- Page Title -->
<section class="page_title s-parallax s-overlay ls title-overlay s-py-25">
	<div class="container">
		<div class="row">
			<div class="fw-divider-space hidden-below-lg mt-130"></div>
			<div class="fw-divider-space hidden-above-lg mt-60"></div>
			<div class="col-md-12 text-center">
				<h1>About Us</h1>
				<ol class="breadcrumb">
					<li class="breadcrumb-item">
						<a href="{{ url('/') }}">Home</a>
					</li>
					<li class="breadcrumb-item active">
						About Us
					</li>
				</ol>
			</div>
			<div class="fw-divider-space hidden-below-lg mt-130"></div>
			<div class="fw-divider-space hidden-above-lg mt-60"></div>
		</div>
	</div>
</section>

<!-- Welcome Section -->
<section class="ls hello s-pt-60 s-pt-lg-100 s-pt-xl-150 c-mb-30 c-gutter-60">
	<div class="container">
		<div class="row">
			<div class="col-5 d-none d-md-block">
				<img src="{{ asset('website/images/parallax/hello-bg.png') }}" alt="">
			</div>
			<div class="col-md-7 col-12">
				<div class="divider-60 d-none d-xl-block"></div>
				<div class="title-section text-center text-md-left">
					<span class="sub-title absolute-subtitle">welcome</span>
					<h3 class="special-heading"> <span><span>T</span>he Infinity <br></span>Salon & SPA</h3>
				</div>
				<div class="divider-60 d-none d-xl-block"></div>
				<div class="row">
					<div class="col-lg-6">
						<p class="text-center text-md-left">At The Infinity Unisex Salon and Spa, we believe everyone deserves to look and feel their best. Our expert team of stylists and therapists are dedicated to providing premium beauty services.</p>
					</div>
					<div class="col-lg-6">
						<p class="text-center text-md-left">From classic haircuts to modern styling, relaxing spa treatments to professional makeup, we offer a complete range of beauty and wellness services in a luxurious environment.</p>
					</div>
				</div>
				<div class="divider-40 d-none d-xl-block"></div>
				<div class="row c-gutter-30 justify-content-start">
					<div class="owl-carousel" data-loop="true" data-margin="30" data-nav="false" data-dots="true" data-center="false" data-items="1" data-autoplay="false" data-responsive-xs="1" data-responsive-sm="1" data-responsive-md="2" data-responsive-lg="3">
						<div class="step-item">
							<div class="hello-img text-center">
								<img src="{{ asset('website/images/square/05.jpg') }}" width="200" height="200" alt="">
								<span>Haircut</span>
							</div>
							<div class="hello-content text-center">
								<h3>01</h3>
								<p>Professional hair cutting and styling for men and women.</p>
							</div>
						</div>
						<div class="step-item">
							<div class="hello-item">
								<div class="hello-img text-center">
									<img src="{{ asset('website/images/square/12.jpg') }}" width="200" height="200" alt="">
									<span>Makeup</span>
								</div>
								<div class="hello-content text-center">
									<h3>02</h3>
									<p>Expert makeup services for all occasions and events.</p>
								</div>
							</div>
						</div>
						<div class="step-item">
							<div class="hello-item">
								<div class="hello-img text-center">
									<img src="{{ asset('website/images/square/13.jpg') }}" width="200" height="200" alt="">
									<span>Spa</span>
								</div>
								<div class="hello-content text-center">
									<h3>03</h3>
									<p>Relaxing spa treatments to rejuvenate your body and mind.</p>
								</div>
							</div>
						</div>
						<div class="step-item">
							<div class="hello-item">
								<div class="hello-img text-center">
									<img src="{{ asset('website/images/square/01.jpg') }}" width="200" height="200" alt="">
									<span>Nails</span>
								</div>
								<div class="hello-content text-center">
									<h3>04</h3>
									<p>Beautiful manicure and pedicure with nail art designs.</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- Video Section -->
<section class="ls ms s-overlay s-parallax video-section video-about s-pt-295 s-pt-sm-410 s-pt-md-360 s-pb-md-60 s-pt-lg-395 s-pb-lg-110 s-pt-xl-430 s-pb-xl-230 s-pb-60">
	<div class="container">
		<div class="row">
			<div class="col-xl-2">
				<h3 class="special-heading video-title text-center text-xl-left">Our Video Presentation</h3>
			</div>
			<div class="col-xl-10">
				<div class="owl-carousel" data-loop="true" data-margin="30" data-nav="true" data-dots="true" data-center="false" data-items="1" data-autoplay="false" data-responsive-xs="1" data-responsive-sm="1" data-responsive-md="1" data-responsive-lg="1">
					<div class="video-simple">
						<a href="{{ asset('website/images/parallax/video_background_1.jpg') }}" class="photoswipe-link" data-width="800" data-height="800" data-iframe="https://www.youtube.com/embed/mcixldqDIEQ">
							<img src="{{ asset('website/images/parallax/video_background_1.jpg') }}" alt="img">
						</a>
					</div>
					<div class="video-simple">
						<a href="{{ asset('website/images/parallax/video_background_2.jpg') }}" class="photoswipe-link" data-width="800" data-height="800" data-iframe="https://www.youtube.com/embed/mcixldqDIEQ">
							<img src="{{ asset('website/images/parallax/video_background_2.jpg') }}" alt="img">
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- Team Section -->
<section class="section-team team ls s-pb-60 s-pb-md-90 s-pb-lg-130 s-pb-xl-220 s-pt-60 s-pt-lg-100 s-pt-xl-150">
	<div class="container">
		<div class="row">
			<div class="col-12">
				<span class="sub-title text-center">meet our</span>
				<h3 class="special-heading text-center">creative team</h3>
				<div class="divider-70 d-none d-xl-block"></div>
				<div class="divider-30 d-block d-xl-none"></div>
				<div class="owl-carousel" data-loop="true" data-margin="30" data-nav="true" data-dots="true" data-center="false" data-items="1" data-autoplay="false" data-responsive-xs="1" data-responsive-sm="1" data-responsive-md="2" data-responsive-lg="3">
					<div class="vertical-item content-padding text-center">
						<div class="item-media">
							<img src="{{ asset('website/images/team/01.jpg') }}" alt="img">
						</div>
						<div class="item-content">
							<h4 class="tile"><a href="#">Bridgette Jameson</a></h4>
							<a href="#"><span class="color-main">Hair Stylist</span></a>
							<p>Expert in modern and classic hair styling techniques.</p>
							<div class="social-icons">
								<a href="#" class="fa fa-facebook" title="facebook"></a>
								<a href="#" class="fa fa-twitter" title="twitter"></a>
								<a href="#" class="fa fa-instagram" title="instagram"></a>
							</div>
						</div>
					</div>
					<div class="vertical-item content-padding text-center">
						<div class="item-media">
							<img src="{{ asset('website/images/team/02.jpg') }}" alt="img">
						</div>
						<div class="item-content">
							<h4 class="tile"><a href="#">Selina Scott</a></h4>
							<a href="#"><span class="color-main">Makeup Artist</span></a>
							<p>Professional makeup for weddings, events and photoshoots.</p>
							<div class="social-icons">
								<a href="#" class="fa fa-facebook" title="facebook"></a>
								<a href="#" class="fa fa-twitter" title="twitter"></a>
								<a href="#" class="fa fa-instagram" title="instagram"></a>
							</div>
						</div>
					</div>
					<div class="vertical-item content-padding text-center">
						<div class="item-media">
							<img src="{{ asset('website/images/team/03.jpg') }}" alt="img">
						</div>
						<div class="item-content">
							<h4 class="tile"><a href="#">Carlos Linton</a></h4>
							<a href="#"><span class="color-main">Barber</span></a>
							<p>Specializes in men's grooming, beard shaping and styling.</p>
							<div class="social-icons">
								<a href="#" class="fa fa-facebook" title="facebook"></a>
								<a href="#" class="fa fa-twitter" title="twitter"></a>
								<a href="#" class="fa fa-instagram" title="instagram"></a>
							</div>
						</div>
					</div>
					<div class="vertical-item content-padding text-center">
						<div class="item-media">
							<img src="{{ asset('website/images/team/04.jpg') }}" alt="img">
						</div>
						<div class="item-content">
							<h4 class="tile"><a href="#">Robin Augustine</a></h4>
							<a href="#"><span class="color-main">Nail Artist</span></a>
							<p>Creative nail art designs and professional manicure services.</p>
							<div class="social-icons">
								<a href="#" class="fa fa-facebook" title="facebook"></a>
								<a href="#" class="fa fa-twitter" title="twitter"></a>
								<a href="#" class="fa fa-instagram" title="instagram"></a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

@endsection
