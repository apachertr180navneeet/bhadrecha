@extends('front.layouts.app')
@section('title', 'Contact Us')
@section('content')
<!-- Page Header Start -->
	<div class="page-header parallaxie">
		<div class="container">
			<div class="row align-items-center">
				<div class="col-lg-12">
					<!-- Page Header Box Start -->
					<div class="page-header-box">
						<h1 class="text-anime-style-2" data-cursor="-opaque">Contact us</h1>
						<nav class="wow fadeInUp">
                            <ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="{{ route('landing') }}">home</a></li>
								<li class="breadcrumb-item active" aria-current="page">contact us</li>
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
            <div class="row align-items-center">
                <div class="col-lg-4">
                    <!-- Contact Us Content Start -->
                    <div class="contact-us-content">
                        <!-- Section Title Start -->
                        <div class="section-title section-title-center">
                            <h3 class="wow fadeInUp">contact form</h3>
                            <h2 class="text-anime-style-2" data-cursor="-opaque">Get in to <span>touch</span></h2>
                        </div>
                        <!-- Section Title End -->

                        <!-- Contact Info List Start -->
                        <div class="contact-info-list">
                             <!-- Contact Info Item Start -->
                             <div class="contact-info-item wow fadeInUp" data-wow-delay="0.2s">
                                 <div class="icon-box">
                                     <img src="{{ asset('front_assets/images/icon-phone.svg') }}" alt="">
                                 </div>
                                 <div class="contact-info-content">
                                     <p>call to question</p>
                                     <h3><a href="tel:+919414194925">+91 94141-94925</a> (Mr Rajendra Suthar)</h3>
                                     <h3><a href="tel:+919829894925">+91 98298-94925</a> (Mr. Harish suthar)</h3>
                                     <h3><a href="tel:+919314194925">+91 93141-94925</a> (Mr. Dinesh suthar)</h3>
                                 </div>
                             </div>
                             <!-- Contact Info Item End -->

                             <!-- Contact Info Item Start -->
                             <div class="contact-info-item wow fadeInUp" data-wow-delay="0.4s">
                                 <div class="icon-box">
                                     <img src="{{ asset('front_assets/images/icon-mail.svg') }}" alt="">
                                 </div>
                                 <div class="contact-info-content">
                                     <p>send e-mail</p>
                                     <h3><a href="mailto:support@bhadrecha.co">support@bhadrecha.co</a></h3>
                                 </div>
                             </div>
                             <!-- Contact Info Item End -->

                             <!-- Contact Info Item Start -->
                             <div class="contact-info-item wow fadeInUp" data-wow-delay="0.6s">
                                 <div class="icon-box">
                                     <img src="{{ asset('front_assets/images/icon-location.svg') }}" alt="">
                                 </div>
                                 <div class="contact-info-content">
                                     <p>visit anytime</p>
                                     <h3>Behind Samrat Ashok Udhyan, 23 B 121, Pal Road, Chopasni Housing Board, Jodhpur, Jodhpur, Rajasthan, 342008</h3>
                                 </div>
                             </div>
                             <!-- Contact Info Item End -->
                        </div>
                        <!-- Contact Info List End -->
                    </div>
                    <!-- Contact Us Content End -->
                </div>

                <div class="col-lg-8">
                    <!-- Contact Us Form Start -->
                    <div class="contact-us-form">
                        @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <!-- Section Title Start -->
                        <div class="section-title">
                            <h2 class="text-anime-style-2" data-cursor="-opaque">Send message <span>with us</span></h2>
                        </div>
                        <!-- Section Title End -->

                        <!-- Contact Form Start -->
                        <div class="contact-form">
                            <form id="contactForm" action="{{ url('/contact') }}" method="POST" data-toggle="validator" class="wow fadeInUp" data-wow-delay="0.2s">
                                @csrf
                                <div class="row">
                                    <div class="form-group col-md-6 mb-4">
                                        <input type="text" name="fname" class="form-control" id="fname" placeholder="First Name" required>
                                        <i class="fa-regular fa-user"></i>
                                        <div class="help-block with-errors"></div>
                                    </div>
    
                                    <div class="form-group col-md-6 mb-4">
                                        <input type="text" name="lname" class="form-control" id="lname" placeholder="Last Name" required>
                                        <i class="fa-regular fa-user"></i>
                                        <div class="help-block with-errors"></div>
                                    </div>
    
                                    <div class="form-group col-md-6 mb-4">
                                        <input type="email" name ="email" class="form-control" id="email" placeholder="Email Address" required>
                                        <i class="fa-regular fa-envelope"></i>
                                        <div class="help-block with-errors"></div>
                                    </div>
                                    
                                    <div class="form-group col-md-6 mb-4">
                                        <input type="text" name="phone" class="form-control" id="phone" placeholder="Phone Number" required>
                                        <img src="{{ asset('front_assets/images/icon-phone-primary.svg') }}" alt="">
                                        <div class="help-block with-errors"></div>
                                    </div>
    
                                    <div class="form-group col-md-12 mb-5">
                                        <textarea name="message" class="form-control" id="message" rows="4" placeholder="Message"></textarea>
                                        <div class="help-block with-errors"></div>
                                    </div>
    
                                    <div class="col-lg-12">
                                        <div class="contact-form-btn">
                                            <button type="submit" class="btn-default">submit message</button>
                                            <div id="msgSubmit" class="h3 hidden"></div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <!-- Contact Form End -->
                    </div>
                    <!-- Contact Us Form End -->
                </div>
            </div>
        </div>
    </div>
    <!-- Page Contact Us End -->

    <!-- Google Map Start -->
    <div class="google-map">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <!-- Google Map Start -->
                    <div class="google-map-iframe">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d96737.10562045308!2d-74.08535042841811!3d40.739265258395164!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c24fa5d33f083b%3A0xc80b8f06e177fe62!2sNew%20York%2C%20NY%2C%20USA!5e0!3m2!1sen!2sin!4v1703158537552!5m2!1sen!2sin" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                    <!-- Google Map End -->
                </div>
            </div>
        </div>
    </div>
    <!-- Google Map End -->
@endsection

