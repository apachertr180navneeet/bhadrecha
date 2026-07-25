@extends('front.layouts.app')
@section('title', 'Landing Page')
@section('content')
<!-- Hero Section Start -->
<div class="hero dark-section parallaxie">
    <div class="container">
        <div class="row align-items-end">
            <div class="col-lg-12">
                <!-- Hero Content Box Start -->
                <div class="hero-content-box">
                    <!-- Hero Content Start -->
                    <div class="hero-content">
                        <!-- Section Title Start -->
                        <div class="section-title">
                            <h1 class="text-anime-style-2" data-cursor="-opaque">Seamless <span>Logistics Solutions</span></h1>
                            <p class="wow fadeInUp">Excellence in Transportation & Delivery</p>
                        </div>
                        <!-- Section Title End -->
                    </div>
                    <!-- Hero Content Start -->

                    <!-- Hero Customer Box Start -->
                    <div class="hero-customer-box">
                        <div class="hero-customer-content wow fadeInUp">
                            <p>Experience reliable logistics services with real-time tracking, secure handling, and timely deliveries tailored to your business needs.</p>
                        </div>

                        <!-- Satisfy Customer Box Start -->
                        <div class="satisfy-customer-box">
                            <!-- Satisfy Customer Images Start -->
                            <div class="satisfy-customer-images">
                                <div class="satisfy-customer-image">
                                    <figure class="image-anime reveal">
                                        <img src="{{ asset('front_assets/images/satisfy-customer-img-1.jpg') }}" alt="">
                                    </figure>
                                </div>
                                <div class="satisfy-customer-image">
                                    <figure class="image-anime reveal">
                                        <img src="{{ asset('front_assets/images/satisfy-customer-img-2.jpg') }}" alt="">
                                    </figure>
                                </div>
                                <div class="satisfy-customer-image">
                                    <figure class="image-anime reveal">
                                        <img src="{{ asset('front_assets/images/satisfy-customer-img-3.jpg') }}" alt="">
                                    </figure>
                                </div>
                            </div>
                            <!-- Satisfy Customer Images End -->
                            
                            <!-- Satisfy Customer Content Start -->
                            <div class="satisfy-customer-content">
                                <h3>Trusted by Businesses</h3>
                                <p><span class="counter">4.9</span> (5K+ Reviews)</p>
                            </div>
                            <!-- Satisfy Customer Content End -->
                        </div>
                        <!-- Satisfy Customer Box End -->

                        <!-- Hero Button Start -->
                        <div class="hero-btn wow fadeInUp" data-wow-delay="0.2s">
                            <a href="{{ url('/contact') }}" class="btn-default btn-highlighted">Get Free Quote</a>
                        </div>
                        <!-- Hero Button End -->
                    </div>
                    <!-- Hero Customer Box End -->
                </div>
                <!-- Hero Content Box End -->
            </div>
        </div>
    </div>
</div>
<!-- Hero Section End -->

<!-- About Us Section Start -->
<div class="about-us">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 order-lg-1 order-2">
                <!-- About Us Image Start -->
                <div class="about-us-image">
                    <figure class="image-anime reveal">
                        <img src="{{ asset('front_assets/images/about-image.jpg') }}" alt="">
                    </figure>
                </div>
                <!-- About Us Image End -->
            </div>

            <div class="col-lg-6  order-lg-2 order-1">
                <!-- About Us Content Start -->
                <div class="about-us-content">
                    <!-- Section Title Start -->
                    <div class="section-title">
                        <h3 class="wow fadeInUp">About Us</h3>
                        <h2 class="text-anime-style-2" data-cursor="-opaque">Transforming <span>Logistics Excellence</span></h2>
                        <p class="wow fadeInUp" data-wow-delay="0.2s">We provide end-to-end logistics solutions that optimize your supply chain, reduce costs, and ensure timely delivery of goods across the region.</p>
                    </div>
                    <!-- Section Title End -->
                    
                    <!-- About Counter Box Start -->
                    <div class="about-counter-box">
                        <!-- About Counter Item Start -->
                        <div class="about-counter-item">
                            <h2><span class="counter">15+</span> Years</h2>
                            <p>of Industry Experience</p>
                        </div>
                        <!-- About Counter Item End -->
                        
                        <!-- About Counter Item Start -->
                        <div class="about-counter-item">
                            <h2><span class="counter">500+</span></h2>
                            <p>Satisfied Corporate Clients</p>
                        </div>
                        <!-- About Counter Item End -->
                    </div>
                    <!-- About Counter Box End -->
                    
                    <!-- About Us Button Start -->
                    <div class="about-us-btn wow fadeInUp"  data-wow-delay="0.4s">
                        <a href="{{ url('/about') }}" class="btn-default">Learn More About Us</a>
                    </div>
                    <!-- About Us Button End -->
                </div>
                <!-- About Us Content End -->
            </div>
            
            <div class="col-lg-12 order-3">
                <!-- About Owner Box Start -->
                <div class="about-owner-box">
                    <!-- About Owner Info Start -->
                    <div class="about-owner-info">
                        <div class="about-owner-content wow fadeInUp">
                            <p>"Our commitment is to provide seamless logistics solutions that drive your business forward with reliability, efficiency, and unmatched customer service."</p>
                        </div>
                        <div class="about-owner-info-body wow fadeInUp" data-wow-delay="0.2s">
                            <div class="about-owner-signature">
                                <img src="{{ asset('front_assets/images/about-owner-signature.png') }}" alt="">
                            </div>
                            <div class="about-owner-info-content">
                                <h3>Rajesh Kumar</h3>
                                <p>Founder & CEO</p>
                            </div>
                        </div>
                    </div>
                    <!-- About Owner Info End -->
                    
                    <!-- About Owner Image Start -->
                    <div class="about-owner-image">
                        <figure class="image-anime reveal">
                            <img src="{{ asset('front_assets/images/about-owner-image.jpg') }}" alt="">
                        </figure>
                    </div>
                    <!-- About Owner Image End -->
                </div>
                <!-- About Owner Box End -->
            </div>
        </div>
    </div>
</div>
<!-- About Us Section End -->

<!-- Services Section Start -->
<div class="our-services dark-section">
    <div class="container">
        <div class="row section-row align-items-center">
            <div class="col-lg-12">
                <!-- Section Title Start -->
                <div class="section-title section-title-center">
                    <h3 class="wow fadeInUp">our services</h3>
                    <h2 class="text-anime-style-2" data-cursor="-opaque">Comprehensive <span>Logistics Solutions</span></h2>
                </div>
                <!-- Section Title End -->
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <!-- Service Item Start -->
                <div class="service-item wow fadeInUp">
                    <div class="icon-box">
                        <img src="{{ asset('front_assets/images/icon-service-item-1.svg') }}" alt="">
                    </div>                                    
                    <div class="service-content">
                        <h3><a href="{{ url('/services') }}">Freight Transportation</a></h3>
                        <p>Full truckload and less-than-truckload services for efficient cargo movement across destinations.</p>                            
                    </div>
                    <div class="service-btn">
                        <a href="{{ url('/services') }}" class="readmore-btn">details</a>  
                    </div>
                </div>
                <!-- Service Item End -->
            </div>
            
            <div class="col-lg-3 col-md-6">
                <!-- Service Item Start -->
                <div class="service-item wow fadeInUp" data-wow-delay="0.2s">
                    <div class="icon-box">
                        <img src="{{ asset('front_assets/images/icon-service-item-2.svg') }}" alt="">
                    </div>                                    
                    <div class="service-content">
                        <h3><a href="{{ url('/services') }}">Warehouse & Storage</a></h3>
                        <p>Secure, climate-controlled storage facilities with inventory management and distribution services.</p>                            
                    </div>
                    <div class="service-btn">
                        <a href="{{ url('/services') }}" class="readmore-btn">details</a>  
                    </div>
                </div>
                <!-- Service Item End -->
            </div>
            
            <div class="col-lg-3 col-md-6">
                <!-- Service Item Start -->
                <div class="service-item wow fadeInUp" data-wow-delay="0.4s">
                    <div class="icon-box">
                        <img src="{{ asset('front_assets/images/icon-service-item-3.svg') }}" alt="">
                    </div>                                    
                    <div class="service-content">
                        <h3><a href="{{ url('/services') }}">Last Mile Delivery</a></h3>
                        <p>Reliable final-mile delivery services ensuring timely and secure delivery to end customers.</p>                            
                    </div>
                    <div class="service-btn">
                        <a href="{{ url('/services') }}" class="readmore-btn">details</a>  
                    </div>
                </div>
                <!-- Service Item End -->
            </div>
            
            <div class="col-lg-3 col-md-6">
                <!-- Service Item Start -->
                <div class="service-item wow fadeInUp" data-wow-delay="0.6s">
                    <div class="icon-box">
                        <img src="{{ asset('front_assets/images/icon-service-item-4.svg') }}" alt="">
                    </div>                                    
                    <div class="service-content">
                        <h3><a href="{{ url('/services') }}">Supply Chain Management</a></h3>
                        <p>End-to-end supply chain solutions optimizing logistics operations from procurement to delivery.</p>                            
                    </div>
                    <div class="service-btn">
                        <a href="{{ url('/services') }}" class="readmore-btn">details</a>  
                    </div>
                </div>
                <!-- Service Item End -->
            </div>
            
            <div class="col-lg-12">
                <!-- Section Footer Text Start -->
                <div class="section-footer-text wow fadeInUp" data-wow-delay="0.8s">
                    <p>Partner with us for integrated logistics solutions that enhance your business efficiency.</p>
                    <a href="{{ url('/services') }}" class="btn-default btn-highlighted">explore all services</a>
                </div>
                <!-- Section Footer Text End -->
            </div>
        </div>
    </div>
</div>
<!-- Services Section End -->

<!-- Call to Action Section -->
<div class="cta-section dark-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="cta-content wow fadeInLeft">
                    <h2>Ready to Optimize Your Logistics?</h2>
                    <p>Let us handle your transportation needs while you focus on growing your business. Get started with a free consultation today.</p>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="cta-btn wow fadeInRight">
                    <a href="{{ url('/contact') }}" class="btn-default btn-highlighted">schedule consultation</a>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Call to Action Section End -->

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
                            <h2 class="text-anime-style-2" data-cursor="-opaque">Your Trusted <span>Logistics Partner</span></h2>
                        </div>
                        <!-- Section Title End -->
                        
                        <!-- Why Choose Body Start -->
                        <div class="why-choose-body wow fadeInUp" data-wow-delay="0.2s">
                            <ul>
                                <li>Real-time GPS tracking for all shipments</li>
                                <li>24/7 customer support and proactive communication</li>
                                <li>Competitive pricing with transparent billing</li>
                                <li>Experienced drivers and handling specialists</li>
                                <li>Comprehensive insurance coverage</li>
                                <li>Eco-friendly fleet and sustainable practices</li>
                            </ul>
                        </div>
                        <!-- Why Choose Body End -->
                    </div>
                    <!-- Why Choose Content End -->
                    
                    <!-- Request Quote Form Start -->
                    <div class="request-quote-form-box wow fadeInUp" data-wow-delay="0.4s">
                        <h3>request a free quote</h3>
                        
                        <!-- Request Quote Form Start -->
                        <div class="request-quote-form">
                            <form id="requestquoteForm" action="#" method="POST">
                                <div class="row">
                                    <div class="form-group col-md-6 mb-4">
                                        <input type="text" name="name" class="form-control" id="name" placeholder="Full Name" required>
                                        <div class="help-block with-errors"></div>
                                    </div>
                                    
                                    <div class="form-group col-md-6 mb-4">
                                        <input type="email" name="email" class="form-control" id="email" placeholder="Email Address" required>
                                        <div class="help-block with-errors"></div>
                                    </div>
                                    
                                    <div class="form-group col-md-6 mb-4">
                                        <input type="tel" name="phone" class="form-control" id="phone" placeholder="Phone Number" required>
                                        <div class="help-block with-errors"></div>
                                    </div>
                                    
                                    <div class="form-group col-md-6 mb-4">
                                        <select name="service_type" class="form-control form-select" id="service_type" required>
                                            <option value="" disabled selected>select service type</option>
                                            <option value="ftl">Full Truckload (FTL)</option>
                                            <option value="ltl">Less Than Truckload (LTL)</option>
                                            <option value="warehousing">Warehousing & Storage</option>
                                            <option value="lastmile">Last Mile Delivery</option>
                                            <option value="supplychain">Supply Chain Management</option>
                                        </select>
                                        <div class="help-block with-errors"></div>
                                    </div>
                                    
                                    <div class="form-group col-md-6 mb-4">
                                        <input type="date" max="9999-12-31" name="pickup_date" class="form-control" id="pickup_date" required>
                                        <div class="help-block with-errors"></div>
                                    </div>
                                    
                                    <div class="form-group col-md-12">
                                        <button type="submit" class="btn-default">get free quote</button>
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

<!-- Testimonials Section Start -->
<div class="our-testimonials">
    <div class="container">
        <div class="row section-row">
            <div class="col-lg-12">
                <!-- Section Title Start -->
                <div class="section-title section-title-center">
                    <h3 class="wow fadeInUp">testimonials</h3>
                    <h2 class="text-anime-style-2" data-cursor="-opaque">What Our <span>Clients Say</span></h2>
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
                                            <h3>Michael Chen</h3>
                                            <p>Operations Manager, ABC Manufacturing</p>
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
                                        <p>"Their logistics solutions have transformed our supply chain operations. Real-time tracking, on-time deliveries, and exceptional customer service have made them our trusted logistics partner for over 3 years."</p>
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
                                            <h3>Sarah Williams</h3>
                                            <p>Logistics Director, XYZ Retail</p>
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
                                        <p>"From warehousing to last-mile delivery, their team provides seamless end-to-end logistics services. Their technology platform gives us complete visibility into our shipments at all times."</p>
                                    </div>
                                </div>
                            </div>
                            <!-- Testimonial Slide End -->
                            
                            <!-- Testimonial Slide Start -->
                            <div class="swiper-slide">
                                <div class="testimonial-item">	
                                    <div class="author-image">
                                        <figure class="image-anime">
                                            <img src="{{ asset('front_assets/images/author-3.jpg') }}" alt="">
                                        </figure>
                                    </div>                                    
                                    <div class="author-content">
                                        <div class="author-title">
                                            <h3>David Rodriguez</h3>
                                            <p>Supply Chain Manager, Global Foods Inc.</p>
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
                                        <p>"Their competitive pricing combined with reliable service has significantly reduced our logistics costs while improving delivery performance. Highly recommended for businesses seeking professional logistics solutions."</p>
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
<!-- Testimonials Section End -->
@endsection