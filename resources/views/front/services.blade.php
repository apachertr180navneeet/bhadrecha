@extends('front.layouts.app')
@section('title', 'Our Services')
@section('content')
<!-- Page Header Start -->
	<div class="page-header parallaxie">
		<div class="container">
			<div class="row align-items-center">
				<div class="col-lg-12">
					<!-- Page Header Box Start -->
					<div class="page-header-box">
						<h1 class="text-anime-style-2" data-cursor="-opaque">Our services</h1>
						<nav class="wow fadeInUp">
                            <ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="{{ route('landing') }}">home</a></li>
								<li class="breadcrumb-item active" aria-current="page">services</li>
							</ol>
						</nav>
					</div>
					<!-- Page Header Box End -->
				</div>
			</div>
		</div>
	</div>
	<!-- Page Header End -->

    <!-- Our Services Section Start -->
    <div class="page-services">
        <div class="container">
            <div class="row">
                <div class="col-lg-3">
                    <!-- Service Item Start -->
                    <div class="service-item wow fadeInUp">
                        <div class="icon-box">
                            <img src="{{ asset('front_assets/images/icon-service-item-1.svg') }}" alt="">
                        </div>                                    
                        <div class="service-content">
                            <h3><a href="service-single.html">Residential Moving</a></h3>
                            <p>Hassle-free home relocation services tailored to your needs. to your needs.</p>                            
                        </div>
                        <div class="service-btn">
                            <a href="service-single.html" class="readmore-btn">read more</a>  
                        </div>
                    </div>
                    <!-- Service Item End -->
                </div>

                <div class="col-lg-3">
                    <!-- Service Item Start -->
                    <div class="service-item wow fadeInUp" data-wow-delay="0.2s">
                        <div class="icon-box">
                            <img src="{{ asset('front_assets/images/icon-service-item-2.svg') }}" alt="">
                        </div>                                    
                        <div class="service-content">
                            <h3><a href="service-single.html">Commercial Moving</a></h3>
                            <p>Professional moving services tailored to business, ensuring efficient, timely relocations.</p>                            
                        </div>
                        <div class="service-btn">
                            <a href="service-single.html" class="readmore-btn">read more</a>  
                        </div>
                    </div>
                    <!-- Service Item End -->
                </div>

                <div class="col-lg-3">
                    <!-- Service Item Start -->
                    <div class="service-item wow fadeInUp" data-wow-delay="0.4s">
                        <div class="icon-box">
                            <img src="{{ asset('front_assets/images/icon-service-item-3.svg') }}" alt="">
                        </div>                                    
                        <div class="service-content">
                            <h3><a href="service-single.html">Specialty Item Moving</a></h3>
                            <p>Expert handling for fragile and valuable items, ensuring safe transport of antiques.</p>                            
                        </div>
                        <div class="service-btn">
                            <a href="service-single.html" class="readmore-btn">read more</a>  
                        </div>
                    </div>
                    <!-- Service Item End -->
                </div>

                <div class="col-lg-3">
                    <!-- Service Item Start -->
                    <div class="service-item wow fadeInUp" data-wow-delay="0.6s">
                        <div class="icon-box">
                            <img src="{{ asset('front_assets/images/icon-service-item-4.svg') }}" alt="">
                        </div>                                    
                        <div class="service-content">
                            <h3><a href="service-single.html">Eco-Friendly Moving</a></h3>
                            <p>Sustainable moving solutions using eco-friendly materials and practices to reduce.</p>                            
                        </div>
                        <div class="service-btn">
                            <a href="service-single.html" class="readmore-btn">read more</a>  
                        </div>
                    </div>
                    <!-- Service Item End -->
                </div>
                
                <div class="col-lg-3">
                    <!-- Service Item Start -->
                    <div class="service-item wow fadeInUp" data-wow-delay="0.8s">
                        <div class="icon-box">
                            <img src="{{ asset('front_assets/images/icon-service-item-5.svg') }}" alt="">
                        </div>                                    
                        <div class="service-content">
                            <h3><a href="service-single.html">Office Relocation</a></h3>
                            <p>Hassle-free home relocation services tailored to your needs. to your needs.</p>                            
                        </div>
                        <div class="service-btn">
                            <a href="service-single.html" class="readmore-btn">read more</a>  
                        </div>
                    </div>
                    <!-- Service Item End -->
                </div>

                <div class="col-lg-3">
                    <!-- Service Item Start -->
                    <div class="service-item wow fadeInUp" data-wow-delay="1s">
                        <div class="icon-box">
                            <img src="{{ asset('front_assets/images/icon-service-item-6.svg') }}" alt="">
                        </div>                                    
                        <div class="service-content">
                            <h3><a href="service-single.html">Packing & Unpacking</a></h3>
                            <p>Professional moving services tailored to business, ensuring efficient, timely relocations.</p>                            
                        </div>
                        <div class="service-btn">
                            <a href="service-single.html" class="readmore-btn">read more</a>  
                        </div>
                    </div>
                    <!-- Service Item End -->
                </div>

                <div class="col-lg-3">
                    <!-- Service Item Start -->
                    <div class="service-item wow fadeInUp" data-wow-delay="1.2s">
                        <div class="icon-box">
                            <img src="{{ asset('front_assets/images/icon-service-item-7.svg') }}" alt="">
                        </div>                                    
                        <div class="service-content">
                            <h3><a href="service-single.html">Storage Solutions</a></h3>
                            <p>Expert handling for fragile and valuable items, ensuring safe transport of antiques.</p>                            
                        </div>
                        <div class="service-btn">
                            <a href="service-single.html" class="readmore-btn">read more</a>  
                        </div>
                    </div>
                    <!-- Service Item End -->
                </div>

                <div class="col-lg-3">
                    <!-- Service Item Start -->
                    <div class="service-item wow fadeInUp" data-wow-delay="1.6s">
                        <div class="icon-box">
                            <img src="{{ asset('front_assets/images/icon-service-item-8.svg') }}" alt="">
                        </div>                                    
                        <div class="service-content">
                            <h3><a href="service-single.html">Moving Assistance</a></h3>
                            <p>Sustainable moving solutions using eco-friendly materials and practices to reduce.</p>                            
                        </div>
                        <div class="service-btn">
                            <a href="service-single.html" class="readmore-btn">read more</a>  
                        </div>
                    </div>
                    <!-- Service Item End -->
                </div>
            </div>
        </div>
    </div>
    <!-- Our Services Section End -->

    <!-- Why Choose Us Section Start -->
    <div class="why-choose-us dark-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="why-choose-box">
                        <!-- Why Choose Content Start -->
                        <div class="why-choose-content">
                            <!-- Section Title Start -->
                            <div class="section-title">
                                <h3 class="wow fadeInUp">why choose us</h3>
                                <h2 class="text-anime-style-2" data-cursor="-opaque">Why we're the preferred choice <span>for moving</span></h2>
                            </div>
                            <!-- Section Title End -->

                            <!-- Why Choose Body Start -->
                            <div class="why-choose-body wow fadeInUp" data-wow-delay="0.2s">
                                <ul>
                                    <li>Timely service with no hidden fees.</li>
                                    <li>Safe, secure transport for your belongings.</li>
                                    <li>Customized moving plans to fit your needs.</li>
                                    <li>Eco-friendly practices for a sustainable move.</li>
                                </ul>
                            </div>
                            <!-- Why Choose Body End -->
                        </div>
                        <!-- Why Choose Content End -->

                        <!-- Request Quote Form Start -->
                        <div class="request-quote-form-box wow fadeInUp" data-wow-delay="0.4s">
                            <h3>request a quote</h3>

                            <!-- Request Quote Form Start -->
                            <div class="request-quote-form">
                                <form id="requestquoteForm" action="#" method="POST">
                                    <div class="row">
                                        <div class="form-group col-md-6 mb-4">
                                            <input type="text" name="name" class="form-control" id="name" placeholder="name" required>
                                            <div class="help-block with-errors"></div>
                                        </div>
                                
                                        <div class="form-group col-md-6 mb-4">
                                            <input type="email" name ="email" class="form-control" id="email" placeholder="Email" required>
                                            <div class="help-block with-errors"></div>
                                        </div>
                                
                                        <div class="form-group col-md-6 mb-4">
                                            <input type="text" name="phone" class="form-control" id="phone" placeholder="Phone" required>
                                            <div class="help-block with-errors"></div>
                                        </div>
                            
                                        <div class="form-group col-md-6 mb-4">
                                            <input type="date" max="9999-12-31" name="date" class="form-control" id="date" required>
                                            <div class="help-block with-errors"></div>
                                        </div>

                                        <div class="form-group col-md-6 mb-4">
                                            <input type="text" name="distance" class="form-control" id="distance" placeholder="distance" required>
                                            <div class="help-block with-errors"></div>
                                        </div>

                                        <div class="form-group col-md-6 mb-4">
                                            <select name="movetype" class="form-control form-select" id="movetype" required>
                                                <option value="" disabled selected>move type</option>
                                                <option value="teeth_whitening">Skin tightening</option>
                                                <option value="pediatric_dental_care">Scar revision</option>
                                                <option value="advanced_oral_care">Wrinkle reduction</option>
                                            </select>
                                            <div class="help-block with-errors"></div>
                                        </div>

                                        <div class="form-group col-md-12 mb-4">
                                            <select name="services" class="form-control form-select" id="services" required>
                                                <option value="" disabled selected>service type</option>
                                                <option value="residential_moving">Residential Moving</option>
                                                <option value="commercial_moving">Commercial Moving</option>
                                                <option value="specialty_item">Specialty Item Moving</option>
                                                <option value="eco_friendly">Eco-Friendly Moving</option>
                                                <option value="office_relocation">Office Relocation</option>
                                                <option value="packing_unpacking">Packing & Unpacking</option>
                                                <option value="storage_solutions">Storage Solutions</option>
                                                <option value="moving_assistance">Moving Assistance</option>
                                            </select>
                                            <div class="help-block with-errors"></div>
                                        </div>

                                        <div class="form-group col-md-12">
                                            <button type="submit" class="btn-default">contact us now</button>
                                            <div id="msgSubmit" class="h3 hidden"></div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <!-- Request Quote Form End -->
                        </div>
                        <!-- Request Quote Form End -->
                    </div>
                </div>  
            </div>
        </div>
    </div>
    <!-- Why Choose Us Section End -->
    
    <!-- Our Testimonials Section Start -->
    <div class="our-testimonials">
        <div class="container">
            <div class="row section-row">
                <div class="col-lg-12">
                    <!-- Section Title Start -->
                    <div class="section-title section-title-center">
                        <h3 class="wow fadeInUp">testimonials</h3>
                        <h2 class="text-anime-style-2" data-cursor="-opaque">Words of appreciation from <span>our customers</span></h2>
                    </div>
                    <!-- Section Title End -->
                </div>   
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <!-- Testimonial Slider Start -->
                    <div class="testimonial-slider">
                        <div class="swiper">
                            <div class="swiper-wrapper" data-cursor-text="Drag">
                                <!-- Testimonial Slide Start -->
                                <div class="swiper-slide">
                                    <div class="testimonial-item">	
                                        <div class="author-image">
                                            <figure class="image-anime">
                                                <img src="{{ asset('front_assets/images/author-1.jpg') }}" alt="">
                                            </figure>
                                        </div>
                                    
                                        <div class="author-content">
                                            <div class="author-title">
                                                <h3>David P., Property Manager</h3>
                                            </div>
                                            <div class="author-rating">
                                                <i class="fa-solid fa-star"></i>
                                                <i class="fa-solid fa-star"></i>
                                                <i class="fa-solid fa-star"></i>
                                                <i class="fa-solid fa-star"></i>
                                                <i class="fa-solid fa-star"></i>
                                            </div>
                                        </div>

                                        <div class="testimonial-content">
                                            <p>From the first phone call to the final box, everything was seamless. The movers were friendly, efficient, and went above and beyond to  make sure my furniture arrived safely. Amazing experience!</p>
                                        </div>
                                    </div>
                                </div>
                                <!-- Testimonial Slide End -->

                                <!-- Testimonial Slide Start -->
                                <div class="swiper-slide">
                                    <div class="testimonial-item">	
                                        <div class="author-image">
                                            <figure class="image-anime">
                                                <img src="{{ asset('front_assets/images/author-2.jpg') }}" alt="">
                                            </figure>
                                        </div>
                                    
                                        <div class="author-content">
                                            <div class="author-title">
                                                <h3>David P., Property Manager</h3>
                                            </div>
                                            <div class="author-rating">
                                                <i class="fa-solid fa-star"></i>
                                                <i class="fa-solid fa-star"></i>
                                                <i class="fa-solid fa-star"></i>
                                                <i class="fa-solid fa-star"></i>
                                                <i class="fa-solid fa-star"></i>
                                            </div>
                                        </div>

                                        <div class="testimonial-content">
                                            <p>From the first phone call to the final box, everything was seamless. The movers were friendly, efficient, and went above and beyond to  make sure my furniture arrived safely. Amazing experience!</p>
                                        </div>
                                    </div>
                                </div>
                                <!-- Testimonial Slide End -->

                                <!-- Testimonial Slide Start -->
                                <div class="swiper-slide">
                                    <div class="testimonial-item">	
                                        <div class="author-image">
                                            <figure class="image-anime">
                                                <img src="{{ asset('front_assets/images/author-1.jpg') }}" alt="">
                                            </figure>
                                        </div>
                                    
                                        <div class="author-content">
                                            <div class="author-title">
                                                <h3>David P., Property Manager</h3>
                                            </div>
                                            <div class="author-rating">
                                                <i class="fa-solid fa-star"></i>
                                                <i class="fa-solid fa-star"></i>
                                                <i class="fa-solid fa-star"></i>
                                                <i class="fa-solid fa-star"></i>
                                                <i class="fa-solid fa-star"></i>
                                            </div>
                                        </div>

                                        <div class="testimonial-content">
                                            <p>From the first phone call to the final box, everything was seamless. The movers were friendly, efficient, and went above and beyond to  make sure my furniture arrived safely. Amazing experience!</p>
                                        </div>
                                    </div>
                                </div>
                                <!-- Testimonial Slide End -->

                            </div>
                            <div class="testimonial-btn">
                                <div class="testimonial-btn-prev"></div>
                                <div class="testimonial-btn-next"></div>
                            </div>
                        </div>
                    </div>
                    <!-- Testimonial Slider End -->
                </div>
            </div>
        </div>
    </div>
    <!-- Our Testimonials Section End -->

    <!-- Our Faqs Section Start -->
    <div class="our-faqs">
        <div class="container-fluid">
            <div class="row no-gutters">
                <div class="col-lg-6">
                    <div class="faq-content-box">
                        <!-- Our Faqs Content Start -->
                        <div class="our-faqs-content">
                            <!-- Section Title Start -->
                            <div class="section-title section-title-center">
                                <h3 class="wow fadeInUp">frequently asked questions</h3>
                                <h2 class="text-anime-style-2" data-cursor="-opaque">Answers to your <span>moving questions</span></h2>
                            </div>
                            <!-- Section Title End -->

                            <!-- FAQ Accordion Start -->
                            <div class="faq-accordion" id="faqaccordion">
                                <!-- FAQ Item Start -->
                                <div class="accordion-item wow fadeInUp">
                                    <h2 class="accordion-header" id="heading1">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1" aria-expanded="true" aria-controls="collapse1">
                                            How far in advance should I book my move?
                                        </button>
                                    </h2>
                                    <div id="collapse1" class="accordion-collapse collapse show" aria-labelledby="heading1" data-bs-parent="#faqaccordion">
                                        <div class="accordion-body">
                                            <p>We recommend booking your move at least 2-4 weeks in advance to ensure availability, especially during peak seasons.</p>
                                        </div>
                                    </div>
                                </div>
                                <!-- FAQ Item End -->

                                <!-- FAQ Item Start -->
                                <div class="accordion-item wow fadeInUp" data-wow-delay="0.2s">
                                    <h2 class="accordion-header" id="heading2">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2" aria-expanded="false" aria-controls="collapse2">
                                            Do you provide packing materials?
                                        </button>
                                    </h2>
                                    <div id="collapse2" class="accordion-collapse collapse" aria-labelledby="heading2" data-bs-parent="#faqaccordion">
                                        <div class="accordion-body">
                                            <p>We recommend booking your move at least 2-4 weeks in advance to ensure availability, especially during peak seasons.</p>
                                        </div>
                                    </div>
                                </div>
                                <!-- FAQ Item End -->

                                <!-- FAQ Item Start -->
                                <div class="accordion-item wow fadeInUp" data-wow-delay="0.4s">
                                    <h2 class="accordion-header" id="heading3">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3" aria-expanded="false" aria-controls="collapse3">
                                            Are my belongings insured during the move?
                                        </button>
                                    </h2>
                                    <div id="collapse3" class="accordion-collapse collapse" aria-labelledby="heading3" data-bs-parent="#faqaccordion">
                                        <div class="accordion-body">
                                            <p>We recommend booking your move at least 2-4 weeks in advance to ensure availability, especially during peak seasons.</p>
                                        </div>
                                    </div>
                                </div>
                                <!-- FAQ Item End -->

                                <!-- FAQ Item Start -->
                                <div class="accordion-item wow fadeInUp" data-wow-delay="0.6s">
                                    <h2 class="accordion-header" id="heading4">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4" aria-expanded="false" aria-controls="collapse4">
                                            Do you offer long-distance moving services?
                                        </button>
                                    </h2>
                                    <div id="collapse4" class="accordion-collapse collapse" aria-labelledby="heading4" data-bs-parent="#faqaccordion">
                                        <div class="accordion-body">
                                            <p>We recommend booking your move at least 2-4 weeks in advance to ensure availability, especially during peak seasons.</p>
                                        </div>
                                    </div>
                                </div>
                                <!-- FAQ Item End -->
                            </div>
                            <!-- FAQ Accordion End -->
                        </div>
                        <!-- Our Faqs Content End -->
                    </div>
                </div>

                <div class="col-lg-6">
                    <!-- Faqs Image Start -->
                    <div class="faqs-image">
                        <div class="faq-image">
                            <figure class="image-anime">
                                <img src="{{ asset('front_assets/images/faq-image.jpg') }}" alt="">
                            </figure>
                        </div>

                        <!-- Intro Video Button Start -->
                        <div class="intro-video-button">
                            <a href="https://www.youtube.com/watch?v=Y-x0efG1seA" class="popup-video" data-cursor-text="Play">
                                <figure>
                                    <img src="{{ asset('front_assets/images/intro-video-circle.svg') }}" alt="">
                                </figure>

                                <div class="into-video-play-icon">
                                    <img src="{{ asset('front_assets/images/intro-video-play-btn.svg') }}" alt="">
                                </div>
                            </a>
                        </div>
                        <!-- Intro Video Button End -->
                    </div>
                    <!-- Faqs Image End -->
                </div>
            </div>
        </div>
    </div>
    <!-- Our Faqs Section End -->
@endsection

