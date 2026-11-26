    <!-- Header Start -->
	<header class="main-header">
		<div class="header-sticky">
			<nav class="navbar navbar-expand-lg">
				<div class="container">
					<!-- Logo Start -->
					<a class="navbar-brand" href="{{ route('landing') }}">
						<img src="{{ asset('front_assets/images/logo5.png') }}" alt="Logo">
					</a>
					<!-- Logo End -->

					<!-- Main Menu Start -->
					<div class="collapse navbar-collapse main-menu">
                        <div class="nav-menu-wrapper">
                            <ul class="navbar-nav mr-auto" id="menu">
                                <li class="nav-item"><a class="nav-link" href="{{ route('landing') }}">Home</a></li>                                
                                <li class="nav-item"><a class="nav-link" href="{{ url('/about') }}">About Us</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ url('/services') }}">Services</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ url('/tracking') }}">Tracking</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ url('/contact') }}">Contact Us</a></li>                         
                            </ul>
                        </div>
                        
                        <!-- Header Btn Start -->
                        <!-- <div class="header-btn">
                            <a href="{{ route('admin.login') }}" class="btn-default btn-highlighted">Login</a>
                        </div> -->
                        <!-- Header Btn End -->
					</div>
					<!-- Main Menu End -->
					<div class="navbar-toggle"></div>
				</div>
			</nav>
			<div class="responsive-menu"></div>
		</div>
	</header>
	<!-- Header End -->

