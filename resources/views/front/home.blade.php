@extends('front.layouts.app')
@section('title', 'Home')
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
                                <h1 class="text-anime-style-2" data-cursor="-opaque">Effortless moving <span>experiences</span></h1>
                                <p class="wow fadeInUp">Experts in Hassle-Free Relocation</p>
                            </div>
                            <!-- Section Title End -->
                        </div>
                        <!-- Hero Content Start -->

                        <!-- Hero Customer Box Start -->
                        <div class="hero-customer-box">
                            <div class="hero-customer-content wow fadeInUp">
                                <p>Experience a stress-free move with our professional services. From careful packing to seamless transportation and timely delivery.</p>
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
                                    <h3>Customer Satisfied</h3>
                                    <p><span class="counter">4.8</span> (15K Reviews)</p>
                                </div>
                                <!-- Satisfy Customer Content End -->
                            </div>
                            <!-- Satisfy Customer Box End -->

                            <!-- Hero Button Start -->
                            <div class="hero-btn wow fadeInUp" data-wow-delay="0.2s">
                                <a href="{{ url('/contact') }}" class="btn-default btn-highlighted">Request a Quote</a>
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
                            <h3 class="wow fadeInUp">About us</h3>
                            <h2 class="text-anime-style-2" data-cursor="-opaque">Setting new standards in the <span>moving industry</span></h2>
                            <p class="wow fadeInUp" data-wow-delay="0.2s">Redefining the moving experience with unmatched professionalism, reliability, and care. We combine innovative solutions, personalized service, and a commitment to excellence to set new benchmarks.</p>
                        </div>
                        <!-- Section Title End -->
                        
                        <!-- About Counter Box Start -->
                        <div class="about-counter-box">
                            <!-- About Counter Item Start -->
                            <div class="about-counter-item">
                               <h2><span class="counter">92</span>%</h2>
                               <p>Our customer satisfaction rate stands at impressive</p>
                            </div>
                            <!-- About Counter Item End -->
                            
                            <!-- About Counter Item Start -->
                            <div class="about-counter-item">
                                <h2><span class="counter">1082</span>+</h2>
                                <p>Helping families and businesses to their new destinations</p>
                            </div>
                            <!-- About Counter Item End -->
                        </div>
                        <!-- About Counter Box End -->
                        
                        <!-- About Us Button Start -->
                        <div class="about-us-btn wow fadeInUp"  data-wow-delay="0.4s">
                            <a href="{{ url('/about') }}" class="btn-default">more about us</a>
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
                                <p>“Moving isn't just about transporting items, it's about embracing change and starting fresh. We're dedicated to ensuring your journey is seamless, so you can focus on settling into your new beginning.”</p>
                            </div>
                            <div class="about-owner-info-body wow fadeInUp" data-wow-delay="0.2s">
                                <div class="about-owner-signature">
                                    <img src="{{ asset('front_assets/images/about-owner-signature.png') }}" alt="">
                                </div>
                                <div class="about-owner-info-content">
                                    <h3>Savannah Nguyen</h3>
                                    <p>CEO Website</p>
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

    <!-- Our Services Section Start -->
    <div class="our-services dark-section">
        <div class="container">
            <div class="row section-row align-items-center">
                <div class="col-lg-12">
                    <!-- Section Title Start -->
                    <div class="section-title section-title-center">
                        <h3 class="wow fadeInUp">our services</h3>
                        <h2 class="text-anime-style-2" data-cursor="-opaque">Reliable moving services built <span>around you</span></h2>
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
                            <h3><a href="service-single.html">Residential Moving</a></h3>
                            <p>Hassle-free home relocation services tailored to your needs. to your needs.</p>                            
                        </div>
                        <div class="service-btn">
                            <a href="service-single.html" class="readmore-btn">read more</a>  
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
                            <h3><a href="service-single.html">Commercial Moving</a></h3>
                            <p>Professional moving services tailored to business ensuring efficient timely relocations.</p>                            
                        </div>
                        <div class="service-btn">
                            <a href="service-single.html" class="readmore-btn">read more</a>  
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
                            <h3><a href="service-single.html">Specialty Item Moving</a></h3>
                            <p>Expert handling for fragile and valuable items, ensuring safe transport of antiques.</p>                            
                        </div>
                        <div class="service-btn">
                            <a href="service-single.html" class="readmore-btn">read more</a>  
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
                            <h3><a href="service-single.html">Eco-Friendly Moving</a></h3>
                            <p>Sustainable moving solutions using eco-friendly materials and practices to reduce.</p>                            
                        </div>
                        <div class="service-btn">
                            <a href="service-single.html" class="readmore-btn">read more</a>  
                        </div>
                    </div>
                    <!-- Service Item End -->
                </div>

                <div class="col-lg-12">
                    <!-- Section Footer Text Start -->
                    <div class="section-footer-text wow fadeInUp" data-wow-delay="0.8s">
                        <p>Expert packing for your move. <a href="{{ url('/contact') }}">Contact us now</a></p>
                        <a href="{{ url('/services') }}" class="btn-default btn-highlighted">view all services</a>
                    </div>
                    <!-- Section Footer Text End -->
                </div>
            </div>
        </div>
    </div>
    <!-- Our Services Section End -->

    <!-- Our Projects Section Start -->
    <div class="our-projects">
        <div class="container">
            <div class="row section-row align-items-center">
                <div class="col-lg-6">
                    <!-- Section Title Start -->
                    <div class="section-title">
                        <h3 class="wow fadeInUp">latest projects</h3>
                        <h2 class="text-anime-style-2" data-cursor="-opaque">Every move counts discover our <span>latest work</span></h2>
                    </div>
                    <!-- Section Title End -->
                </div>

                <div class="col-lg-6">
                    <!-- Section Title Content Start -->
                    <div class="section-title-content wow fadeInUp" data-wow-delay="0.2s">
                        <p>Take a closer look at our recent projects and see how we turn every move into a seamless experience. From small local moves to large-scale relocations, we're proud to showcase the care.</p>
                    </div>
                    <!-- Section Title Content End -->
                </div>
            </div>

            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <!-- Project Item Start -->
                    <div class="project-item wow fadeInUp">
                        <div class="project-image">
                            <figure class="image-anime">
                                <img src="{{ asset('front_assets/images/project-1.jpg') }}" alt="">
                            </figure>

                            <div class="project-btn">
                                <a href="project-single.html"><img src="{{ asset('front_assets/images/arrow-white.svg') }}" alt=""></a>
                            </div>
                        </div>
                        <div class="project-content">
                            <h3><a href="project-single.html">Urban Retreat Relocation</a></h3>
                        </div>
                    </div>
                    <!-- Project Item End -->
                </div>

                <div class="col-lg-4 col-md-6">
                    <!-- Project Item Start -->
                    <div class="project-item wow fadeInUp" data-wow-delay="0.2s">
                        <div class="project-image">
                            <figure class="image-anime">
                                <img src="{{ asset('front_assets/images/project-2.jpg') }}" alt="">
                            </figure>

                            <div class="project-btn">
                                <a href="project-single.html"><img src="{{ asset('front_assets/images/arrow-white.svg') }}" alt=""></a>
                            </div>
                        </div>
                        <div class="project-content">
                            <h3><a href="project-single.html">Cityscape Transitions</a></h3>
                        </div>
                    </div>
                    <!-- Project Item End -->
                </div>

                <div class="col-lg-4 col-md-6">
                    <!-- Project Item Start -->
                    <div class="project-item wow fadeInUp" data-wow-delay="0.4s">
                        <div class="project-image">
                            <figure class="image-anime">
                                <img src="{{ asset('front_assets/images/project-3.jpg') }}" alt="">
                            </figure>

                            <div class="project-btn">
                                <a href="project-single.html"><img src="{{ asset('front_assets/images/arrow-white.svg') }}" alt=""></a>
                            </div>
                        </div>
                        <div class="project-content">
                            <h3><a href="project-single.html">Luxury Living Moves</a></h3>
                        </div>
                    </div>
                    <!-- Project Item End -->
                </div>

                <div class="col-lg-4 col-md-6">
                    <!-- Project Item Start -->
                    <div class="project-item wow fadeInUp" data-wow-delay="0.6s">
                        <div class="project-image">
                            <figure class="image-anime">
                                <img src="{{ asset('front_assets/images/project-4.jpg') }}" alt="">
                            </figure>

                            <div class="project-btn">
                                <a href="project-single.html"><img src="{{ asset('front_assets/images/arrow-white.svg') }}" alt=""></a>
                            </div>
                        </div>
                        <div class="project-content">
                            <h3><a href="project-single.html">Seamless Office Relocation</a></h3>
                        </div>
                    </div>
                    <!-- Project Item End -->
                </div>

                <div class="col-lg-4 col-md-6">
                    <!-- Project Item Start -->
                    <div class="project-item wow fadeInUp" data-wow-delay="0.8s">
                        <div class="project-image">
                            <figure class="image-anime">
                                <img src="{{ asset('front_assets/images/project-5.jpg') }}" alt="">
                            </figure>

                            <div class="project-btn">
                                <a href="project-single.html"><img src="{{ asset('front_assets/images/arrow-white.svg') }}" alt=""></a>
                            </div>
                        </div>
                        <div class="project-content">
                            <h3><a href="project-single.html">Suburban Dream Shifts</a></h3>
                        </div>
                    </div>
                    <!-- Project Item End -->
                </div>

                <div class="col-lg-4 col-md-6">
                    <!-- Project Item Start -->
                    <div class="project-item wow fadeInUp" data-wow-delay="1s">
                        <div class="project-image">
                            <figure class="image-anime">
                                <img src="{{ asset('front_assets/images/project-6.jpg') }}" alt="">
                            </figure>

                            <div class="project-btn">
                                <a href="project-single.html"><img src="{{ asset('front_assets/images/arrow-white.svg') }}" alt=""></a>
                            </div>
                        </div>
                        <div class="project-content">
                            <h3><a href="project-single.html">Corporate Move</a></h3>
                        </div>
                    </div>
                    <!-- Project Item End -->
                </div>

                <div class="col-lg-12">
                    <!-- Section Footer Button Start -->
                    <div class="section-footer-btn wow fadeInUp" data-wow-delay="1.2s">
                        <a href="projects.html" class="btn-default">view all projects</a>
                    </div>
                    <!-- Section Footer Button End -->
                </div>
            </div>
        </div>
    </div>
    <!-- Our Projects Section End -->

    <!-- Our Work Process Section Start -->
    <div class="our-work-process dark-section">
        <div class="container">
            <div class="row section-row">
                <div class="col-lg-12">
                    <!-- Section Title Start -->
                    <div class="section-title section-title-center">
                        <h3 class="wow fadeInUp">our work process</h3>
                        <h2 class="text-anime-style-2" data-cursor="-opaque">Reliable moving services built <span>around you</span></h2>
                    </div>
                    <!-- Section Title End -->
                </div>
            </div>

            <div class="row">
                <!-- Work Process List Start -->
                <div class="wrok-process-list">
                    <!-- Work Process Item Start -->
                    <div class="work-process-item wow fadeInUp">
                        <div class="icon-box">
                            <img src="{{ asset('front_assets/images/icon-work-process-1.svg') }}" alt="">
                            <div class="work-process-number">
                                <h3>1</h3>
                            </div>
                        </div>
                        <div class="work-process-content">
                            <h3>get a quote</h3>
                            <p>Start by contacting us for a free, personalized quote. Share your moving details.</p>
                        </div>
                    </div>
                    <!-- Work Process Item End -->

                    <!-- Work Process Item Start -->
                    <div class="work-process-item wow fadeInUp" data-wow-delay="0.2s">
                        <div class="icon-box">
                            <img src="{{ asset('front_assets/images/icon-work-process-2.svg') }}" alt="">
                            <div class="work-process-number">
                                <h3>2</h3>
                            </div>
                        </div>
                        <div class="work-process-content">
                            <h3>plan your move</h3>
                            <p>Our team works with you to create a customized moving plan. From packing to logistics.</p>
                        </div>
                    </div>
                    <!-- Work Process Item End -->

                    <!-- Work Process Item Start -->
                    <div class="work-process-item wow fadeInUp" data-wow-delay="0.4s">
                        <div class="icon-box">
                            <img src="{{ asset('front_assets/images/icon-work-process-3.svg') }}" alt="">
                            <div class="work-process-number">
                                <h3>3</h3>
                            </div>
                        </div>
                        <div class="work-process-content">
                            <h3>safe & secure moving</h3>
                            <p>Our expert movers handle your belongings with care, using high-quality packing materials.</p>
                        </div>
                    </div>
                    <!-- Work Process Item End -->

                    <!-- Work Process Item Start -->
                    <div class="work-process-item wow fadeInUp" data-wow-delay="0.6s">
                        <div class="icon-box">
                            <img src="{{ asset('front_assets/images/icon-work-process-4.svg') }}" alt="">
                            <div class="work-process-number">
                                <h3>4</h3>
                            </div>
                        </div>
                        <div class="work-process-content">
                            <h3>unpack & settle in</h3>
                            <p>Once we've delivered your items, we'll help with unpacking and setting up, so you can enjoy.</p>
                        </div>
                    </div>
                    <!-- Work Process Item End -->
                </div>
                <!-- Work Process List End -->
            </div>
        </div>
    </div>
    <!-- Our Work Process Section End -->

    <!-- Our Skill Section Start -->
    <div class="our-skill">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <!-- Our Skill Images Start -->
                    <div class="our-skill-images">
                        <div class="our-skill-image-1">
                            <figure class="image-anime reveal">
                                <img src="{{ asset('front_assets/images/our-skill-image-1.jpg') }}" alt="">
                            </figure>
                        </div>

                        <div class="our-skill-image-2">
                            <figure class="image-anime reveal">
                                <img src="{{ asset('front_assets/images/our-skill-image-2.jpg') }}" alt="">
                            </figure>
                        </div>

                        <div class="contact-us-circle">
                            <a href="{{ url('/contact') }}">
                                <img src="{{ asset('front_assets/images/contact-us-circle.svg') }}" alt="">
                            </a>
                        </div>
                    </div>
                    <!-- Our Skill Images End -->
                </div>

                <div class="col-lg-6">
                    <!-- Our Skill Content Start -->
                    <div class="our-skill-content">
                        <!-- Section Title Start -->
                        <div class="section-title">
                            <h3 class="wow fadeInUp">our skill</h3>
                            <h2 class="text-anime-style-2" data-cursor="-opaque">Experienced movers with a passion <span>for perfection</span></h2>
                            <p class="wow fadeInUp" data-wow-delay="0.2s">Our team is equipped with the skills and expertise to handle every aspect of your move. From meticulous packing to careful transportation, we ensure your belongings are treated.</p>
                        </div>
                        <!-- Section Title End -->

                        <!-- Our Skill Body Start -->
                        <div class="our-skill-body">
                            <!-- Skills Progress Bar Start -->
                            <div class="skills-progress-bar">
                                <!-- Skill Item Start -->
                                <div class="skillbar" data-percent="85%">
                                    <div class="skill-data">
                                        <div class="skill-title">real-time tracking</div>
                                        <div class="skill-no">85%</div>
                                    </div>
                                    <div class="skill-progress">
                                        <div class="count-bar"></div>
                                    </div>
                                </div>
                                <!-- Skill Item End -->
                            </div>
                            <!-- Skills Progress Bar End -->

                            <!-- Skills Progress Bar Start -->
                            <div class="skills-progress-bar">
                                <!-- Skill Item Start -->
                                <div class="skillbar" data-percent="90%">
                                    <div class="skill-data">
                                        <div class="skill-title">specialized handling</div>
                                        <div class="skill-no">90%</div>
                                    </div>
                                    <div class="skill-progress">
                                        <div class="count-bar"></div>
                                    </div>
                                </div>
                                <!-- Skill Item End -->
                            </div>
                            <!-- Skills Progress Bar End -->

                            <!-- Skills Progress Bar Start -->
                            <div class="skills-progress-bar">
                                <!-- Skill Item Start -->
                                <div class="skillbar" data-percent="94%">
                                    <div class="skill-data">
                                        <div class="skill-title">customer-centered approach</div>
                                        <div class="skill-no">94%</div>
                                    </div>
                                    <div class="skill-progress">
                                        <div class="count-bar"></div>
                                    </div>
                                </div>
                                <!-- Skill Item End -->
                            </div>
                            <!-- Skills Progress Bar End -->
                        </div>
                        <!-- Our Skill Body End -->

                        <!-- Our Skill Button Start -->
                        <div class="our-skill-btn wow fadeInUp" data-wow-delay="0.4s">
                            <a href="{{ url('/contact') }}" class="btn-default">book service now</a>
                        </div>
                        <!-- Our Skill Button End -->
                    </div>
                    <!-- Our Skill Content End -->
                </div>
            </div>
        </div>
    </div>
    <!-- Our Skill Section End -->

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
                                                <h3>Johan D., Relocation Specialist</h3>
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
                                            <p>From the first phone call to the final box, everything was seamless. The movers were friendly, efficient, and went above and beyond to make sure my furniture arrived safely. Amazing experience!</p>
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
                                                <h3>Antoine B., Logistics coordinator</h3>
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
                                            <p>From the first phone call to the final box, everything was seamless. The movers were friendly, efficient, and went above and beyond to make sure my furniture arrived safely. Amazing experience!</p>
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
                                                <h3>Corolina S., Move consultant</h3>
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
                                            <p>From the first phone call to the final box, everything was seamless. The movers were friendly, efficient, and went above and beyond to make sure my furniture arrived safely. Amazing experience!</p>
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
                            <div class="section-title">
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

    <!-- Scrolling Ticker Section Start -->
    <div class="our-scrolling-ticker">
        <!-- Scrolling Ticker Start -->
        <div class="scrolling-ticker-box">
            <div class="scrolling-content">
                <span><img src="{{ asset('front_assets/images/icon-asterisk.svg') }}" alt="">100% trusted trasportation</span>
                <span><img src="{{ asset('front_assets/images/icon-asterisk.svg') }}" alt="">100% trusted trasportation</span>
            </div>

            <div class="scrolling-content">
                <span><img src="{{ asset('front_assets/images/icon-asterisk.svg') }}" alt="">100% trusted trasportation</span>
                <span><img src="{{ asset('front_assets/images/icon-asterisk.svg') }}" alt="">100% trusted trasportation</span>
            </div>
        </div>
        <!-- Scrolling Ticker End -->
    </div>
    <!-- Scrolling Ticker Section End -->

    <!-- Our Team Section Start -->
    <div class="our-team dark-section">
        <div class="container">
            <div class="row section-row">
                <div class="col-lg-12">
                    <!-- Section Title Start -->
                    <div class="section-title section-title-center">
                        <h3 class="wow fadeInUp">team member</h3>
                        <h2 class="text-anime-style-2" data-cursor="-opaque">Professionals making your <span>move seamless</span></h2>
                    </div>
                    <!-- Section Title End -->
                </div>
            </div>

            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <!-- Team Item Start -->
                    <div class="team-item wow fadeInUp">
                        <!-- Team Image Start -->
                        <div class="team-image">
                            <a href="team-single.html" data-cursor-text="View">
                                <figure class="image-anime">
                                    <img src="{{ asset('front_assets/images/team-1.jpg') }}" alt="">
                                </figure>
                            </a>
                            <!-- Team Social Icon Start -->
                            <div class="team-social-icon">
                                <ul>
                                    <li><a href="#"><i class="fa-brands fa-instagram"></i></a></li>
                                    <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                                    <li><a href="#"><i class="fa-brands fa-dribbble"></i></a></li>
                                    <li><a href="#"><i class="fa-brands fa-linkedin-in"></i></a></li>
                                </ul>
                            </div>
                            <!-- Team Social Icon End -->
                        </div>	
                        <!-- Team Image End -->

                        <!-- Team Content Start -->
                        <div class="team-content">
                            <h3><a href="team-single.html">kristin watson</a></h3>
                            <p>crane rigger</p>
                        </div>
                        <!-- Team Content End -->
                    </div>
                    <!-- Team Item End -->
                </div>

                <div class="col-lg-3 col-md-6">
                    <!-- Team Item Start -->
                    <div class="team-item wow fadeInUp" data-wow-delay="0.2s">
                        <!-- Team Image Start -->
                        <div class="team-image">
                            <a href="team-single.html" data-cursor-text="View">
                                <figure class="image-anime">
                                    <img src="{{ asset('front_assets/images/team-2.jpg') }}" alt="">
                                </figure>
                            </a>
                            <!-- Team Social Icon Start -->
                            <div class="team-social-icon">
                                <ul>
                                    <li><a href="#"><i class="fa-brands fa-instagram"></i></a></li>
                                    <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                                    <li><a href="#"><i class="fa-brands fa-dribbble"></i></a></li>
                                    <li><a href="#"><i class="fa-brands fa-linkedin-in"></i></a></li>
                                </ul>
                            </div>
                            <!-- Team Social Icon End -->
                        </div>	
                        <!-- Team Image End -->

                        <!-- Team Content Start -->
                        <div class="team-content">
                            <h3><a href="team-single.html">darrell steward</a></h3>
                            <p>landscaping supervisor</p>
                        </div>
                        <!-- Team Content End -->
                    </div>
                    <!-- Team Item End -->
                </div>

                <div class="col-lg-3 col-md-6">
                    <!-- Team Item Start -->
                    <div class="team-item wow fadeInUp" data-wow-delay="0.4s">
                        <!-- Team Image Start -->
                        <div class="team-image">
                            <a href="team-single.html" data-cursor-text="View">
                                <figure class="image-anime">
                                    <img src="{{ asset('front_assets/images/team-3.jpg') }}" alt="">
                                </figure>
                            </a>
                            <!-- Team Social Icon Start -->
                            <div class="team-social-icon">
                                <ul>
                                    <li><a href="#"><i class="fa-brands fa-instagram"></i></a></li>
                                    <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                                    <li><a href="#"><i class="fa-brands fa-dribbble"></i></a></li>
                                    <li><a href="#"><i class="fa-brands fa-linkedin-in"></i></a></li>
                                </ul>
                            </div>
                            <!-- Team Social Icon End -->
                        </div>	
                        <!-- Team Image End -->

                        <!-- Team Content Start -->
                        <div class="team-content">
                            <h3><a href="team-single.html">floyd miles</a></h3>
                            <p>estimator</p>
                        </div>
                        <!-- Team Content End -->
                    </div>
                    <!-- Team Item End -->
                </div>

                <div class="col-lg-3 col-md-6">
                    <!-- Team Item Start -->
                    <div class="team-item wow fadeInUp" data-wow-delay="0.6s">
                        <!-- Team Image Start -->
                        <div class="team-image">
                            <a href="team-single.html" data-cursor-text="View">
                                <figure class="image-anime">
                                    <img src="{{ asset('front_assets/images/team-4.jpg') }}" alt="">
                                </figure>
                            </a>
                            <!-- Team Social Icon Start -->
                            <div class="team-social-icon">
                                <ul>
                                    <li><a href="#"><i class="fa-brands fa-instagram"></i></a></li>
                                    <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                                    <li><a href="#"><i class="fa-brands fa-dribbble"></i></a></li>
                                    <li><a href="#"><i class="fa-brands fa-linkedin-in"></i></a></li>
                                </ul>
                            </div>
                            <!-- Team Social Icon End -->
                        </div>	
                        <!-- Team Image End -->

                        <!-- Team Content Start -->
                        <div class="team-content">
                            <h3><a href="team-single.html">brooklyn simmons</a></h3>
                            <p>concrete inspector</p>
                        </div>
                        <!-- Team Content End -->
                    </div>
                    <!-- Team Item End -->
                </div>
            </div>
        </div>
    </div>
    <!-- Our Team Section End -->

    <!-- Our Blog Section Start -->
    <div class="our-blog">
        <div class="container">
            <div class="row section-row align-items-center">
                <div class="col-lg-6">
                    <!-- Section Title Start -->
                    <div class="section-title ">
                        <h3 class="wow fadeInUp">latest blogs</h3>
                        <h2 class="text-anime-style-2" data-cursor="-opaque">Expert Advice for Stress- <span>Free Moves</span></h2>
                    </div>
                    <!-- Section Title End -->
                </div>

                <div class="col-lg-6">
                    <!-- Section Title Content Start -->
                    <div class="section-title-content wow fadeInUp" data-wow-delay="0.2s">
                        <p>Stay informed with our expert moving advice and practical insights. Whether you're relocating locally or long-distance, our blog has everything you need to make your journey seamless.</p>
                    </div>
                    <!-- Section Title Content End -->
                </div>
            </div>

            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <!-- Post Item Start -->
                    <div class="post-item wow fadeInUp">
                        <!-- Post Featured Image Start-->
                        <div class="post-featured-image">
                            <a href="blog-single.html" data-cursor-text="View">
                                <figure class="image-anime">
                                    <img src="{{ asset('front_assets/images/post-1.jpg') }}" alt="">
                                </figure>
                            </a>
                        </div>
                        <!-- Post Featured Image End -->

                        <!-- Post Item Body Start -->
                        <div class="post-item-body">
                            <!-- Post Item Content Start -->
                            <div class="post-item-content">
                                <h3><a href="blog-single.html">How to Unpack Efficiently After Your Move</a></h3>
                            </div>
                            <!-- Post Item Content End -->

                            <!-- Post Item Readmore Button Start-->
                            <div class="post-item-btn">
                                <a href="blog-single.html" class="readmore-btn">read more</a>
                            </div>
                            <!-- Post Item Readmore Button End-->
                        </div>
                        <!-- Post Item Body End -->
                    </div>
                    <!-- Post Item End -->
                </div>

                <div class="col-lg-4 col-md-6">
                    <!-- Post Item Start -->
                    <div class="post-item wow fadeInUp" data-wow-delay="0.2s">
                       <!-- Post Featured Image Start-->
                       <div class="post-featured-image">
                            <a href="blog-single.html" data-cursor-text="View">
                                <figure class="image-anime">
                                    <img src="{{ asset('front_assets/images/post-2.jpg') }}" alt="">
                                </figure>
                            </a>
                        </div>
                        <!-- Post Featured Image End -->

                        <!-- Post Item Body Start -->
                        <div class="post-item-body">
                            <!-- Post Item Content Start -->
                            <div class="post-item-content">
                                <h3><a href="blog-single.html">The Ultimate Guide to Packing Fragile Items</a></h3>
                            </div>
                            <!-- Post Item Content End -->

                            <!-- Post Item Readmore Button Start-->
                            <div class="post-item-btn">
                                <a href="blog-single.html" class="readmore-btn">read more</a>
                            </div>
                            <!-- Post Item Readmore Button End-->
                        </div>
                        <!-- Post Item Body End -->
                    </div>
                    <!-- Post Item End -->
                </div>

                <div class="col-lg-4 col-md-6">
                    <!-- Post Item Start -->
                    <div class="post-item wow fadeInUp" data-wow-delay="0.4s">
                        <!-- Post Featured Image Start-->
                        <div class="post-featured-image">
                            <a href="blog-single.html" data-cursor-text="View">
                                <figure class="image-anime">
                                    <img src="{{ asset('front_assets/images/post-3.jpg') }}" alt="">
                                </figure>
                            </a>
                        </div>
                        <!-- Post Featured Image End -->

                        <!-- Post Item Body Start -->
                        <div class="post-item-body">
                            <!-- Post Item Content Start -->
                            <div class="post-item-content">
                                <h3><a href="blog-single.html">The Benefits of Professional Packing Services</a></h3>
                            </div>
                            <!-- Post Item Content End -->

                            <!-- Post Item Readmore Button Start-->
                            <div class="post-item-btn">
                                <a href="blog-single.html" class="readmore-btn">read more</a>
                            </div>
                            <!-- Post Item Readmore Button End-->
                        </div>
                        <!-- Post Item Body End -->
                    </div>
                    <!-- Post Item End -->
                </div>
            </div>
        </div>
    </div>
    <!-- Our Blog Section End -->
@endsection

