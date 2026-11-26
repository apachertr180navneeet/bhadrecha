@extends('web.layouts.app')

@section('title', 'Our Services - The Infinity Unisex Salon and Spa')
@section('meta_description', 'Explore our wide range of salon and spa services - Hair, Makeup, Nails, Spa & more')

@section('content')

<!-- Page Title -->
<section class="page_title s-parallax s-overlay ls title-overlay s-py-25">
	<div class="container">
		<div class="row">
			<div class="fw-divider-space hidden-below-lg mt-130"></div>
			<div class="fw-divider-space hidden-above-lg mt-60"></div>
			<div class="col-md-12 text-center">
				<h1>Our Services</h1>
				<ol class="breadcrumb">
					<li class="breadcrumb-item">
						<a href="{{ url('/') }}">Home</a>
					</li>
					<li class="breadcrumb-item active">
						Our Services
					</li>
				</ol>
			</div>
			<div class="fw-divider-space hidden-below-lg mt-130"></div>
			<div class="fw-divider-space hidden-above-lg mt-60"></div>
		</div>
	</div>
</section>

<!-- Services List -->
<section class="ls service s-pt-70 s-pb-20 s-pb-sm-60 s-py-lg-100 s-pt-xl-160 s-pb-xl-100 c-mb-60">
	<div class="container">
		<div class="row">

			<!-- Service 1 -->
			<div class="col-lg-12">
				<div class="vertical-item">
					<div class="item-media">
						<div class="media-image">
							<img src="{{ asset('website/images/services/06.jpg') }}" width="500" height="600" alt="">
						</div>
						<div class="media-links">
							<a class="abs-link" title="" href="#"></a>
						</div>
					</div>
					<div class="item-content">
						<h3><a href="#">Beard Cutting Experts</a></h3>
						<a href="#"><span>Hair</span></a>
						<p>Professional beard trimming, shaping, and styling services. Our expert barbers use premium tools and techniques to give you the perfect beard look. Whether you prefer a clean shave or a styled beard, we've got you covered.</p>
						<a class="btn btn-outline-maincolor" href="#">read more</a>
					</div>
				</div>
			</div>

			<!-- Service 2 -->
			<div class="col-lg-12">
				<div class="vertical-item">
					<div class="item-media">
						<div class="media-image">
							<img src="{{ asset('website/images/services/05.jpg') }}" width="500" height="600" alt="">
						</div>
						<div class="media-links">
							<a class="abs-link" title="" href="#"></a>
						</div>
					</div>
					<div class="item-content">
						<h3><a href="#">Professional Makeup</a></h3>
						<a href="#"><span>Makeup</span></a>
						<p>Transform your look with our professional makeup services. From bridal makeup to party looks, our skilled makeup artists create stunning looks for every occasion. We use high-quality products that are gentle on your skin.</p>
						<a class="btn btn-outline-maincolor" href="#">read more</a>
					</div>
				</div>
			</div>

			<!-- Service 3 -->
			<div class="col-lg-12">
				<div class="vertical-item">
					<div class="item-media">
						<div class="media-image">
							<img src="{{ asset('website/images/services/04.jpg') }}" width="500" height="600" alt="">
						</div>
						<div class="media-links">
							<a class="abs-link" title="" href="#"></a>
						</div>
					</div>
					<div class="item-content">
						<h3><a href="#">Manicure & Pedicure Services</a></h3>
						<a href="#"><span>Nails</span></a>
						<p>Pamper your hands and feet with our luxurious manicure and pedicure services. Choose from classic, gel, or creative nail art designs. Our nail artists stay updated with the latest trends to give you beautiful nails.</p>
						<a class="btn btn-outline-maincolor" href="#">read more</a>
					</div>
				</div>
			</div>

			<!-- Service 4 -->
			<div class="col-lg-12">
				<div class="vertical-item">
					<div class="item-media">
						<div class="media-image">
							<img src="{{ asset('website/images/services/02.jpg') }}" width="500" height="600" alt="">
						</div>
						<div class="media-links">
							<a class="abs-link" title="" href="#"></a>
						</div>
					</div>
					<div class="item-content">
						<h3><a href="#">Hair Coloring & Styling</a></h3>
						<a href="#"><span>Hair</span></a>
						<p>Express yourself with our range of hair coloring and styling options. From subtle highlights to bold transformations, our hair colorists use premium products to achieve the perfect shade while keeping your hair healthy.</p>
						<a class="btn btn-outline-maincolor" href="#">read more</a>
					</div>
				</div>
			</div>

			<!-- Service 5 -->
			<div class="col-lg-12">
				<div class="vertical-item">
					<div class="item-media">
						<div class="media-image">
							<img src="{{ asset('website/images/services/01.jpg') }}" width="500" height="600" alt="">
						</div>
						<div class="media-links">
							<a class="abs-link" title="" href="#"></a>
						</div>
					</div>
					<div class="item-content">
						<h3><a href="#">Spa & Wellness</a></h3>
						<a href="#"><span>Spa</span></a>
						<p>Relax and rejuvenate with our spa and wellness treatments. Our range of facials, body wraps, and massage therapies are designed to restore your body and mind. Experience true relaxation in our serene spa environment.</p>
						<a class="btn btn-outline-maincolor" href="#">read more</a>
					</div>
				</div>
			</div>

		</div>
	</div>
</section>

<!-- Pricing Section -->
<section class="ls ms portfolio-section s-pt-60 s-pb-60 s-pt-lg-110 s-pb-lg-90 s-pt-xl-150 s-pb-xl-140 s-overlay s-parallax container-px-xl-165 container-px-lg-100 container-px-md-10 c-gutter-70">
	<div class="container-fluid">
		<div class="row">
			<div class="col-xl-5 text-center text-lg-left">
				<div class="title-portfolio">
					<span class="sub-title">Check Out</span>
					<h3 class="special-heading">Our Portfolio</h3>
					<p>See our latest work and transformations. Every client is a canvas and every service is a masterpiece.</p>
				</div>
			</div>
			<div class="col-xl-7">
				<div class="owl-carousel" data-loop="true" data-margin="30" data-nav="true" data-dots="true" data-center="false" data-items="1" data-autoplay="false" data-responsive-xs="1" data-responsive-sm="1" data-responsive-md="1" data-responsive-lg="1">
					<div class="posrtfolio-single">
						<img src="{{ asset('website/images/portfolio/01.jpg') }}" alt="">
					</div>
					<div class="posrtfolio-single">
						<img src="{{ asset('website/images/portfolio/02.jpg') }}" alt="">
					</div>
					<div class="posrtfolio-single">
						<img src="{{ asset('website/images/portfolio/03.jpg') }}" alt="">
					</div>
					<div class="posrtfolio-single">
						<img src="{{ asset('website/images/portfolio/04.jpg') }}" alt="">
					</div>
					<div class="posrtfolio-single">
						<img src="{{ asset('website/images/portfolio/05.jpg') }}" alt="">
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

@endsection
