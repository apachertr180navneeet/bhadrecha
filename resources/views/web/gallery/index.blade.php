@extends('web.layouts.app')

@section('title', 'Gallery - The Infinity Unisex Salon and Spa')
@section('meta_description', 'View our gallery of haircuts, makeup transformations, nail art, and spa relaxation areas')

@section('content')

<!-- Page Title -->
<section class="page_title s-parallax s-overlay ls title-overlay s-py-25">
	<div class="container">
		<div class="row">
			<div class="fw-divider-space hidden-below-lg mt-130"></div>
			<div class="fw-divider-space hidden-above-lg mt-60"></div>
			<div class="col-md-12 text-center">
				<h1>Gallery</h1>
				<ol class="breadcrumb">
					<li class="breadcrumb-item">
						<a href="{{ url('/') }}">Home</a>
					</li>
					<li class="breadcrumb-item active">
						Gallery
					</li>
				</ol>
			</div>
			<div class="fw-divider-space hidden-below-lg mt-130"></div>
			<div class="fw-divider-space hidden-above-lg mt-60"></div>
		</div>
	</div>
</section>

<!-- Gallery Grid -->
<section class="ls s-py-60 s-py-lg-100 s-py-xl-150">
	<div class="container">
		<div class="row isotope-wrapper masonry-layout c-gutter-10 c-mb-10">
			@for($i = 1; $i <= 19; $i++)
				@php
					$imgName = str_pad($i, 2, '0', STR_PAD_LEFT) . '.jpg';
				@endphp
				<div class="col-xl-3 col-md-4 col-sm-6">
					<div class="vertical-item item-gallery text-center ds">
						<div class="item-media">
							<img src="{{ asset('website/images/gallery/' . $imgName) }}" class="rounded-0" alt="Gallery Image {{ $i }}">
							<div class="media-links">
								<a class="abs-link photoswipe-link" href="{{ asset('website/images/gallery/' . $imgName) }}" data-width="800" data-height="800"></a>
							</div>
						</div>
					</div>
				</div>
			@endfor
		</div>
	</div>
</section>

@endsection
