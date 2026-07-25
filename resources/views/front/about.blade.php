@extends('front.layouts.app')
@section('title', 'About Us')
@section('content')
<!-- Page Header Start -->
	<div class="page-header parallaxie">
		<div class="container">
			<div class="row align-items-center">
				<div class="col-lg-12">
					<!-- Page Header Box Start -->
					<div class="page-header-box">
						<h1 class="text-anime-style-2" data-cursor="-opaque">About us</h1>
						<nav class="wow fadeInUp">
                            <ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="{{ route('landing') }}">home</a></li>
								<li class="breadcrumb-item active" aria-current="page">about us</li>
							</ol>
						</nav>
					</div>
					<!-- Page Header Box End -->
				</div>
			</div>
		</div>
	</div>
	<!-- Page Header End -->

    <!-- About Us Section Start -->
    <div class="about-us page-about-us">
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

    <!-- Our Approach Section Start -->
    <div class="our-approach dark-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <!-- Our Approach Image Start -->
                    <div class="our-approach-image">
                        <!-- Our Approach Image Box Start -->
                        <div class="our-approach-img-box">
                            <div class="our-approach-img-1">
                                <figure class="image-anime reveal">
                                    <img src="{{ asset('front_assets/images/our-approach-image-1.jpg') }}" alt="">
                                </figure>
                            </div>
                            
                            <div class="trusted-client-box wow fadeInUp">
                                <h3>Trusted by 3500+ clients for reliable moves.</h3>
                            </div>
                        </div>
                        <!-- Our Approach Image Box End -->
                        
                        <!-- Our Approach Image Box Start -->
                        <div class="our-approach-img-box">
                            <div class="our-approach-img-2">
                                <figure class="image-anime reveal">
                                    <img src="{{ asset('front_assets/images/our-approach-image-2.jpg') }}" alt="">
                                </figure>
                            </div>
                        </div>
                        <!-- Our Approach Image Box End -->
                    </div>
                    <!-- Our Approach Image End -->
                </div>

                <div class="col-lg-6">
                    <!-- Our Approch Content Start -->
                    <div class="our-approch-content">
                        <!-- Section Title Start -->
                        <div class="section-title">
                            <h3 class="wow fadeInUp">our approach</h3>
                            <h2 class="text-anime-style-2" data-cursor="-opaque">Discover the heart of <span>our mission</span></h2>
                            <p class="wow fadeInUp" data-wow-delay="0.2s">Our mission is rooted in providing seamless, stress-free moving experiences tailored to your unique needs, ensuring that every step of your relocation is handled with care, efficiency, and professionalism.</p>
                        </div>
                        <!-- Section Title End -->

                        <!-- Mission Vision List Start -->
                        <div class="mission-vision-list">
                            <!-- MIssion Vision Item Start -->
                            <div class="mission-vision-item wow fadeInUp" data-wow-delay="0.4s">
                                <div class="icon-box">
                                    <img src="{{ asset('front_assets/images/icon-our-mission.svg') }}" alt="">
                                </div>
                                <div class="mission-vision-content">
                                    <h3>our mission</h3>
                                    <p>Our mission is to provide hassle-free, customized moving services with a focus on care and efficiency</p>
                                </div>
                            </div>
                            <!-- MIssion Vision Item End -->

                            <!-- MIssion Vision Item Start -->
                            <div class="mission-vision-item wow fadeInUp" data-wow-delay="0.6s">
                                <div class="icon-box">
                                    <img src="{{ asset('front_assets/images/icon-our-vision.svg') }}" alt="">
                                </div>
                                <div class="mission-vision-content">
                                    <h3>our vision</h3>
                                    <p>Our vision is to redefine the moving experience by prioritizing customer satisfaction and innovative solutions.</p>
                                </div>
                            </div>
                            <!-- MIssion Vision Item End -->
                        </div>
                        <!-- Mission Vision List End -->
                    </div>
                    <!-- Our Approch Content End -->
                </div>
            </div>
        </div>
    </div>
    <!-- Our Approach Section End -->

    <!-- Who We Are Section Start -->
    <div class="who-we-are">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <!-- Who We Are Content Start -->
                    <div class="who-we-are-content">
                        <!-- Section Title Start -->
                        <div class="section-title ">
                            <h3 class="wow fadeInUp">who we are</h3>
                            <h2 class="text-anime-style-2" data-cursor="-opaque">Redefining Relocation <span>Excellence</span></h2>
                            <p class="wow fadeInUp" data-wow-delay="0.2s">We specialize in delivering seamless moving solutions, tailored to meet your needs and designed to exceed expectations. Our dedicated team ensures every detail is managed with care, making your relocation experience smooth and hassle-free.</p>
                        </div>
                        <!-- Section Title End -->

                        <!-- Who We Body Start -->
                        <div class="who-we-body">
                            <!-- Who We Item Start -->
                            <div class="who-we-item wow fadeInUp" data-wow-delay="0.4s">
                                <div class="icon-box">
                                    <img src="{{ asset('front_assets/images/icon-who-we-are-1.svg') }}" alt="">
                                </div>
                                <div class="who-we-item-content">
                                    <h3>We simplify your move with seamless, efficient services.</h3>
                                </div>
                            </div>
                            <!-- Who We Item End -->

                            <!-- Who We Item Start -->
                            <div class="who-we-item wow fadeInUp" data-wow-delay="0.6s">
                                <div class="icon-box">
                                    <img src="{{ asset('front_assets/images/icon-who-we-are-2.svg') }}" alt="">
                                </div>
                                <div class="who-we-item-content">
                                    <h3>From careful packing to secure delivery, we handle all your needs.</h3>
                                </div>
                            </div>
                            <!-- Who We Item End -->

                            <!-- Who We Item Start -->
                            <div class="who-we-item wow fadeInUp" data-wow-delay="0.8s">
                                <div class="icon-box">
                                    <img src="{{ asset('front_assets/images/icon-who-we-are-3.svg') }}" alt="">
                                </div>
                                <div class="who-we-item-content">
                                    <h3>Our dedicated team ensures safe and reliable transportation services.</h3>
                                </div>
                            </div>
                            <!-- Who We Item End -->
                        </div>
                        <!-- Who We Body End -->
                    </div>
                    <!-- Who We Are Content End -->
                </div>

                <div class="col-lg-6">
                    <!-- Who We Are Images Start -->
                    <div class="who-we-are-images">
                        <!-- Who We Image Box Start -->
                        <div class="who-we-image-box-1">
                            <!-- Who We Img Start -->
                            <div class="who-we-img-1">
                                <figure class="image-anime reveal">
                                    <img src="{{ asset('front_assets/images/who-we-are-img-1.jpg') }}" alt="">
                                </figure>
                            </div>
                            <!-- Who We Img End -->
                            
                            <!-- Who We Img Start -->
                            <div class="who-we-img-2">
                                <figure class="image-anime reveal">
                                    <img src="{{ asset('front_assets/images/who-we-are-img-2.jpg') }}" alt="">
                                </figure>
                            </div>
                            <!-- Who We Img End -->
                        </div>
                        <!-- Who We Image Box End -->

                        <!-- Who We Image Box Start -->
                        <div class="who-we-image-box-2">
                            <!-- Goals Img Start -->
                            <div class="who-we-img-3">
                                <figure class="image-anime reveal">
                                    <img src="{{ asset('front_assets/images/who-we-are-img-3.jpg') }}" alt="">
                                </figure>
                            </div>
                            <!-- Who We Img End -->
                            
                            <!-- Who We Img Start -->
                            <div class="who-we-img-4">
                                <figure class="image-anime reveal">
                                    <img src="{{ asset('front_assets/images/who-we-are-img-4.jpg') }}" alt="">
                                </figure>
                            </div>
                            <!-- Who We Img End -->
                        </div>
                        <!-- Who We Are Image Box End -->

                        <!-- Contact Nwo Circle Start -->
                        <div class="contact-us-circle">
                            <a href="{{ url('/contact') }}"><img src="{{ asset('front_assets/images/contact-us-circle-dark.svg') }}" alt=""></a>
                        </div>
                        <!-- Contact Nwo Circle End -->
                    </div>
                    <!-- Our Goals Images End -->
                </div>
            </div>
        </div>
    </div>
    <!-- Who We Are Section End -->

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

    <!-- Our Success Story Section Start -->
    <div class="our-success-story">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <!-- Success Story Images Start -->
                    <div class="success-story-images">
                        <!-- Success Story Image 1 Start -->
                        <div class="success-story-img-1">
                            <figure class="image-anime reveal">
                                <img src="{{ asset('front_assets/images/success-story-image-1.jpg') }}" alt="">
                            </figure>
                        </div>
                        <!-- Success Story Image 1 End -->
                        
                        <!-- Success Story Image 2 Start -->
                        <div class="success-story-img-2">
                            <figure class="image-anime">
                                <img src="{{ asset('front_assets/images/success-story-image-2.jpg') }}" alt="">
                            </figure>
                        </div>
                        <!-- Success Story Image 2 End -->

                        <!-- Contact Now Box Start -->
                        <div class="contact-now-box">
                            <div class="icon-box">
                                <img src="{{ asset('front_assets/images/icon-phone-primary.svg') }}" alt="">
                            </div>
                            <div class="contact-now-box-content">
                                <h3>call us now</h3>
                                <p class="mb-1"><a href="tel:+919414194925">+91 94141-94925</a> (Mr Rajendra Suthar)</p>
                                <p class="mb-1"><a href="tel:+919829894925">+91 98298-94925</a> (Mr. Harish suthar)</p>
                                <p class="mb-0"><a href="tel:+919314194925">+91 93141-94925</a> (Mr. Dinesh suthar)</p>
                            </div>
                        </div>
                        <!-- Contact Now Box End -->
                    </div>
                    <!-- Success Story Images End -->
                </div> 

                <div class="col-lg-6">
                    <!-- Success Story Content Start -->
                    <div class="success-story-content">
                        <!-- Section Title Start -->
                        <div class="section-title ">
                            <h3 class="wow fadeInUp">Real Stories, Real Moves</h3>
                            <h2 class="text-anime-style-2" data-cursor="-opaque">Transforming moves into <span>success stories</span></h2>
                            <p class="wow fadeInUp" data-wow-delay="0.2s">At Bhadrecha, we take pride in delivering seamless moving experiences that lead to happy clients. Read the success stories of those who trusted us to handle their relocations with care and efficiency.</p>
                        </div>
                        <!-- Section Title End -->

                        <!-- Success Story Body Start -->
                        <div class="success-story-body wow fadeInUp" data-wow-delay="0.4s">
                            <ul>
                                <li>See how we simplify moves.</li>
                                <li>Effortless moving with experts.</li>
                                <li>Explore our stress moving process.</li>
                                <li>Making relocation simple.</li>
                            </ul>
                        </div>
                        <!-- Success Story Body End -->

                        <!-- Success Story Button Start -->
                        <div class="success-story-btn wow fadeInUp" data-wow-delay="0.6s">
                            <a href="{{ url('/contact') }}" class="btn-default">contact us</a>
                        </div>
                        <!-- Success Story Button End -->
                    </div>
                    <!-- Success Story Content End -->
                </div>
            </div>
        </div>
    </div>
    <!-- Our Success Story Section End -->

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

    <!-- What We DO Section Start -->
    <div class="what-we-do">
        <div class="container">
            <div class="row what-we-do-box no-gutters">
                <div class="col-lg-4">
                    <!-- What We Do Image Start -->
                    <div class="what-we-do-image">
                        <figure class="image-anime reveal">
                            <img src="{{ asset('front_assets/images/what-we-do-image.jpg') }}" alt="">
                        </figure>
                    </div>
                    <!-- What We Do Image End -->
                </div>

                <div class="col-lg-8">
                    <!-- What We Content Start -->
                    <div class="what-we-content">
                        <!-- Section Title Start -->
                        <div class="section-title ">
                            <h3 class="wow fadeInUp">what we do</h3>
                            <h2 class="text-anime-style-2" data-cursor="-opaque">Get premium our <span>services</span></h2>
                            <p class="wow fadeInUp" data-wow-delay="0.2s">Discover top-notch services designed to meet your needs with excellence. From tailored solutions to expert support, we ensure a seamless and satisfying experience every step of the way.</p>
                        </div>
                        <!-- Section Title End -->

                        <!-- What We Body Start -->
                        <div class="what-we-body">
                            <!-- What We Item Start -->
                            <div class="what-we-item wow fadeInUp" data-wow-delay="0.4s">
                                <div class="icon-box">
                                    <img src="{{ asset('front_assets/images/icon-what-we-1.svg') }}" alt="">
                                </div>
                                <div class="what-we-item-content">
                                    <h3>a full services</h3>
                                    <p>Our services cover every aspect of your need ensuring seamless.</p>
                                </div>
                            </div>
                            <!-- What We Item End -->

                            <!-- What We Item Start -->
                            <div class="what-we-item wow fadeInUp" data-wow-delay="0.6s">
                                <div class="icon-box">
                                    <img src="{{ asset('front_assets/images/icon-what-we-2.svg') }}" alt="">
                                </div>
                                <div class="what-we-item-content">
                                    <h3>maintenance</h3>
                                    <p>Our services cover every aspect of your need ensuring seamless.</p>
                                </div>
                            </div>
                            <!-- What We Item End -->
                        </div>
                        <!-- What We Body End -->
                    </div>
                    <!-- What We Content End -->
                </div>                
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <!-- What We Counter List Start -->
                    <div class="what-we-counter-list">
                        <!-- What We Counter Item Start -->
                        <div class="what-we-counter-item">
                            <h2><span class="counter">768</span>+</h2>
                            <p>residential masterpieces</p>
                        </div>
                        <!-- What We Counter Item End -->

                        <!-- What We Counter Item Start -->
                        <div class="what-we-counter-item">
                            <h2><span class="counter">970</span>+</h2>
                            <p>renovation completed</p>
                        </div>
                        <!-- What We Counter Item End -->

                        <!-- What We Counter Item Start -->
                        <div class="what-we-counter-item">
                            <h2><span class="counter">98</span>%</h2>
                            <p>commercial successes</p>
                        </div>
                        <!-- What We Counter Item End -->

                        <!-- What We Counter Item Start -->
                        <div class="what-we-counter-item">
                            <h2><span class="counter">46</span>+</h2>
                            <p>sustainable construction</p>
                        </div>
                        <!-- What We Counter Item End -->
                    </div>
                    <!-- What We Do Box End -->
                </div>
            </div>
        </div>
    </div>
    <!-- What We DO Section End -->

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
                    <!-- FAQ Content Box Start -->
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
                    <!-- FAQ Content Box End -->
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

