<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
	<!-- Global site tag (gtag.js) - Google Analytics -->
	<!-- Global site tag (gtag.js) - Google Ads: 684299578 -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=UA-153570767-1"></script>
	<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());

		gtag('config', 'UA-153570767-1');
		gtag('config', 'AW-684299578');
	</script>

	<!-- Google Tag Manager -->
	<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
	new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
	j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
	'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
	})(window,document,'script','dataLayer','GTM-PFR42D7');</script>
	<!-- End Google Tag Manager -->

	@stack('head')

	<title>{{ ($locale == 'ar') ? 'فيتاس' : 'Vitas' }} | {{ $pageTitle }}</title>
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
	<meta name="facebook-domain-verification" content="eqqjg6z74quvn59fn2ov92wb7v8iy2" />
	{{-- Primary Meta Tags --}}
	<meta name="title" content="{{ $seo['title'] }}">
	<meta name="description" content="{{ $seo['description'] }}"> {{-- Up to 160 characters --}}
	<meta name="keywords" content="{{ $seo['keywords'] }}">
	<link rel="canonical" href="{{ route('home', $locale) }}" />
	<meta name="robots" content="index, follow">
	{{-- Open Graph / Facebook --}}
	<meta property="og:type" content="website">
	<meta property="og:title" content="{{ $seo['title'] }}">
	<meta property="og:url" content="{{ route('home', $locale) }}">
	<meta property="og:description" content="{{ $seo['description'] }}">
	<meta property="og:image" content="{{ asset('assets/images/logo.png') }}">
	<meta property="og:image:height" content="155">
	<meta property="og:image:width" content="297">
	{{-- Twitter --}}
	<meta property="twitter:card" content="summary_large_image">
	<meta property="twitter:url" content="{{ route('home', $locale) }}">
	<meta property="twitter:title" content="{{ $seo['title'] }}">
	<meta property="twitter:description" content="{{ $seo['description'] }}">
	<meta property="twitter:image" content="{{ asset('assets/images/logo.png') }}">
	{{-- Favicon --}}
	<link rel="apple-touch-icon" sizes="120x120" href="{{ asset('assets/images/favicon/apple-touch-icon.png') }}">
	<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/favicon/favicon-32x32.png') }}">
	<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/favicon/favicon-16x16.png') }}">
	<link rel="manifest" href="{{ asset('assets/images/favicon/site.webmanifest') }}">
	<link rel="mask-icon" href="{{ asset('assets/images/favicon/safari-pinned-tab.svg') }}" color="#5bbad5">
	<meta name="msapplication-TileColor" content="#ffffff">
	<meta name="theme-color" content="#ffffff">
	<link rel="stylesheet" type="text/css" href="{{ asset('assets/libraries/bootstrap-4.3.1/bootstrap.min.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('assets/libraries/slick-1.8.1/slick/slick.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('assets/libraries/slick-1.8.1/slick/slick-theme.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('assets/libraries/animate/animate.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('assets/libraries/aos-master/aos.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('assets/fonts/verdana/styles.css') }}"/>
	<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/main.css?v'.now()->format('m:i:s')) }}"/>
	@if($locale == 'ar')
	<link rel="stylesheet" type="text/css" href="{{ asset('assets/fonts/fedra-ar/styles.css') }}"/>
	<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/main-ar.css?v'.now()->format('m:i:s')) }}"/>
	@endif
</head>
<body>
	<!-- Google Tag Manager (noscript) -->
	<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PFR42D7"
	height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
	<!-- End Google Tag Manager (noscript) -->

	@if(Route::currentRouteName() != 'app-stores')
	<div class="loadingScreen starting">
		<div class="loading-spinner">
			<img src="{{ asset('assets/images/v_logo.svg') }}">
		</div>
	</div>

	<div class="top-bar text-black">
		<div class="text-right pt-3 px-3 px-md-5">
			<a href="@if($locale == 'ar') {{ url(('en' . substr(Request::path(), 2))) }} @else {{ url(('ar' . substr(Request::path(), 2))) }} @endif" class="language">
				<img class="language-icon mr-1" src="{{ asset('assets/images/icons/globe.svg') }}">
				@if($locale == 'ar') ENG @else عربي @endif
			</a>
		</div>
		<nav class="navbar navbar-expand-lg py-md-0 pb-md-2 px-md-5">
			<a class="navbar-brand" href="{{ route('home', $locale) }}">
				<img src="{{ asset('assets/images/logo.png') }}" alt="Logo">
			</a>
			<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-expanded="false">
				<span></span>
				<span></span>
				<span></span>
			</button>
			<div class="collapse navbar-collapse" id="navbarNav">
				<ul class="navbar-nav ml-auto pr-0">
					<li class="nav-item my-2 my-lg-0 pr-lg-2 pl-lg-0 en-d-none ar-d-block">
						<a href="{{ route('apply-loan', [$locale]) }}">
							<button class="btn btn-custom btn-filled px-3">{{ trans('app.link-apply-for-a-loan') }}</button>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link {{ Route::currentRouteName() == 'home' ? 'active' : '' }}" href="{{ route('home', $locale) }}">{{ trans('app.link-home') }}</a>
					</li>
					<li class="nav-item">
						<a class="nav-link {{ Route::currentRouteName() == 'about' ? 'active' : '' }}" href="{{ route('about', $locale) }}">{{ trans('app.link-about-us') }}</a>
					</li>
					<li class="nav-item">
						<a class="nav-link {{ Route::currentRouteName() == 'social-impact' ? 'active' : '' }}" href="{{ route('social-impact', $locale) }}">{{ trans('app.link-social-impact') }}</a>
					</li>
					<li class="nav-item">
						<a class="nav-link {{ Route::currentRouteName() == 'products' ? 'active' : '' }}" href="{{ route('products', $locale) }}">{{ trans('app.link-products') }}</a>
					</li>
					<li class="nav-item">
						<a class="nav-link {{ Route::currentRouteName() == 'careers' ? 'active' : '' }}" href="{{ route('careers', $locale) }}">{{ trans('app.link-careers') }}</a>
					</li>
					<li class="nav-item">
						<a class="nav-link {{ Route::currentRouteName() == 'contact' ? 'active' : '' }}" href="{{ route('contact', $locale) }}">{{ trans('app.link-contact-us') }}</a>
					</li>
					<li class="nav-item my-2 my-lg-0 pr-lg-0 pl-lg-2 ar-d-none">
						<a href="{{ route('apply-loan', [$locale]) }}">
							<button class="btn btn-custom btn-filled px-3">{{ trans('app.link-apply-for-a-loan') }}</button>
						</a>
					</li>
				</ul>
			</div>
		</nav>
	</div>

	<a href="{{ route('locate-us', $locale) }}">
		<button class="btn btn-custom btn-filled btn-locate-us box-shadow"><b>{{ trans('app.locate-us') }}</b></button>
	</a>
	@endif

	@yield('content')


    <div class="floating-div">
		<a href="https://wa.me/9647822909192/?text=" target="_blank">
			<svg width="3rem" height="3rem" viewBox="0 0 30 30" fill="none"
				xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
				<rect width="30" height="30" fill="url(#pattern0)" />
				<defs>
					<pattern id="pattern0" patternContentUnits="objectBoundingBox" width="1"
						height="1">
						<use xlink:href="#image0_82_186" transform="scale(0.0217391)" />
					</pattern>
					<image id="image0_82_186" width="46" height="46"
						xlink:href="data:image/png;base64,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" />
				</defs>
			</svg>
		</a>
	</div>

	@if(Route::currentRouteName() != 'app-stores')
	<footer class="bg-secondary text-white py-5">
		<div class="container">
			<div class="row mb-4">
				<div class="col-md-3 mb-4 mb-md-0">
					<p class="text-primary mb-2"><b>{{ trans('app.call-center-number') }}</b></p>
					<a href="tel:{{ $contactFooter['call-center-number'] }}" class="dir-ltr"><h6 class="mb-1"><b>{{ $contactFooter['call-center-number'] }}</b></h6></a>
					<a href="{{ route('faqs', $locale) }}">
						<button class="btn btn-custom btn-filled mt-2">{{ trans('app.try-faqs') }}</button>
					</a>
				</div>
				<div class="col-md-3 mb-4 mb-md-0">
					<p class="text-primary mb-2"><b>{{ trans('app.our-address') }}</b></p>
					{{ $contactFooter['our-address'] }}
				</div>
				<div class="col-md-3 mb-4 mb-md-0">
					<p class="text-primary mb-2"><b>{{ trans('app.working-hours') }}</b></p>
					<p class="pre-line">{{ $contactFooter['working-hours'] }}</p>
				</div>
				<div class="col-md-3">
					<p class="text-primary mb-2"><b>{{ trans('app.follow-us') }}</b></p>
					<div class="social-media svg-white mb-2">
						@if($contactFooter['facebook'])
						<a class="mr-4 mr-md-3 mr-lg-4 ar-mr-0 ar-ml-4 ar-ml-md-3 ar-ml-lg-4" href="{{ $contactFooter['facebook'] }}" target="_blank">
							<svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"width="96.124px" height="96.123px" viewBox="0 0 96.124 96.123" style="enable-background:new 0 0 96.124 96.123;"xml:space="preserve"><path fill="#ffffff" d="M72.089,0.02L59.624,0C45.62,0,36.57,9.285,36.57,23.656v10.907H24.037c-1.083,0-1.96,0.878-1.96,1.961v15.803c0,1.083,0.878,1.96,1.96,1.96h12.533v39.876c0,1.083,0.877,1.96,1.96,1.96h16.352c1.083,0,1.96-0.878,1.96-1.96V54.287h14.654c1.083,0,1.96-0.877,1.96-1.96l0.006-15.803c0-0.52-0.207-1.018-0.574-1.386c-0.367-0.368-0.867-0.575-1.387-0.575H56.842v-9.246c0-4.444,1.059-6.7,6.848-6.7l8.397-0.003c1.082,0,1.959-0.878,1.959-1.96V1.98C74.046,0.899,73.17,0.022,72.089,0.02z"/></svg>
						</a>
						@endif
						@if($contactFooter['instagram'])
						<a class="mr-4 mr-md-3 mr-lg-4 ar-mr-0 ar-ml-4 ar-ml-md-3 ar-ml-lg-4" href="{{ $contactFooter['instagram'] }}" target="_blank">
							<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"width="169.063px" height="169.063px" viewBox="0 0 169.063 169.063" style="enable-background:new 0 0 169.063 169.063;"xml:space="preserve"><path fill="#ffffff" d="M122.406,0H46.654C20.929,0,0,20.93,0,46.655v75.752c0,25.726,20.929,46.655,46.654,46.655h75.752c25.727,0,46.656-20.93,46.656-46.655V46.655C169.063,20.93,148.133,0,122.406,0z M154.063,122.407c0,17.455-14.201,31.655-31.656,31.655H46.654C29.2,154.063,15,139.862,15,122.407V46.655C15,29.201,29.2,15,46.654,15h75.752c17.455,0,31.656,14.201,31.656,31.655V122.407z"/><path fill="#ffffff" d="M84.531,40.97c-24.021,0-43.563,19.542-43.563,43.563c0,24.02,19.542,43.561,43.563,43.561s43.563-19.541,43.563-43.561C128.094,60.512,108.552,40.97,84.531,40.97z M84.531,113.093c-15.749,0-28.563-12.812-28.563-28.561c0-15.75,12.813-28.563,28.563-28.563s28.563,12.813,28.563,28.563C113.094,100.281,100.28,113.093,84.531,113.093z"/><path fill="#ffffff" d="M129.921,28.251c-2.89,0-5.729,1.17-7.77,3.22c-2.051,2.04-3.23,4.88-3.23,7.78c0,2.891,1.18,5.73,3.23,7.78c2.04,2.04,4.88,3.22,7.77,3.22c2.9,0,5.73-1.18,7.78-3.22c2.05-2.05,3.22-4.89,3.22-7.78c0-2.9-1.17-5.74-3.22-7.78C135.661,29.421,132.821,28.251,129.921,28.251z"/></svg>
						</a>
						@endif
						@if($contactFooter['youtube'])
						<a class="mr-4 mr-md-3 mr-lg-4 ar-mr-0 ar-ml-4 ar-ml-md-3 ar-ml-lg-4" href="{{ $contactFooter['youtube'] }}" target="_blank">
							<svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve"><path fill="#ffffff" d="M490.24,113.92c-13.888-24.704-28.96-29.248-59.648-30.976C399.936,80.864,322.848,80,256.064,80c-66.912,0-144.032,0.864-174.656,2.912c-30.624,1.76-45.728,6.272-59.744,31.008C7.36,138.592,0,181.088,0,255.904C0,255.968,0,256,0,256c0,0.064,0,0.096,0,0.096v0.064c0,74.496,7.36,117.312,21.664,141.728c14.016,24.704,29.088,29.184,59.712,31.264C112.032,430.944,189.152,432,256.064,432c66.784,0,143.872-1.056,174.56-2.816c30.688-2.08,45.76-6.56,59.648-31.264C504.704,373.504,512,330.688,512,256.192c0,0,0-0.096,0-0.16c0,0,0-0.064,0-0.096C512,181.088,504.704,138.592,490.24,113.92z M192,352V160l160,96L192,352z"/></svg>
						</a>
						@endif
					</div>
					<img src="{{ asset('assets/images/CHF.png') }}">
				</div>
			</div>
			<div class="text-center font-english">
				Copyright &copy; Vitas. All rights reserved.&nbsp;&nbsp;Powered by <a class="hover-a" href="https://tedmob.com" target="_blank">TEDMOB.com</a>
			</div>
		</div>
	</footer>
	@endif

	<script type="text/javascript" src="{{ asset('assets/js/jquery-3.1.1.js') }}"></script>
	<script type="text/javascript" src="{{ asset('assets/libraries/bootstrap-4.3.1/bootstrap.min.js') }}"></script>
	<script type="text/javascript" src="{{ asset('assets/libraries/slick-1.8.1/slick/slick.min.js') }}"></script>
	<script type="text/javascript" src="{{ asset('assets/libraries/aos-master/aos.js') }}"></script>
	<script type="text/javascript" src="{{ asset('assets/js/main.js?v'.now()->format('m:i:s')) }}"></script>

	@stack('scripts')

	@if(Route::currentRouteName() == 'locate-us')
	<script type="text/javascript">
		function initMap() {

			var locations = {!! json_encode($latlng) !!};

			var map = new google.maps.Map(document.getElementById('map'), {
				scrollwheel: false,
				mapTypeId: google.maps.MapTypeId.ROADMAP
			});

			var bounds = new google.maps.LatLngBounds();

			var marker, i;
			var markers = new Array();

			for (i = 0; i < locations.length; i++) {
				marker = new google.maps.Marker({
					id: locations[i].id,
					position: new google.maps.LatLng(locations[i].lat, locations[i].lng),
					map: map,
					icon: "{{ asset('assets/images/icons/marker.svg') }}"
				});
				markers.push(marker);

				google.maps.event.addListener(marker, 'click', (function(marker, i) {
					return function() {
						map.setZoom(15);
						map.setCenter(marker.getPosition());

						var markerId = marker.id;
						$(".locate-us-list li").removeClass("active");
						$(".locate-us-list li[data-location='"+ markerId +"']").addClass("active");
					}
				})(marker, i));

				bounds.extend(new google.maps.LatLng(locations[i].lat, locations[i].lng));
			};

			$(".locate-us-list li").click(function(){
				var locationId = $(this).attr("data-location");
				for (i = 0; i < markers.length; i++) {
					if( locationId == markers[i].id ){
						$(".locate-us-list li").removeClass("active");
						$(this).addClass("active");
						map.setZoom(15);
						map.setCenter(markers[i].getPosition());

						if($(window).width() < 768){
							$('html, body').stop().animate({
								'scrollTop': parseInt($("#map").offset().top - 110)
							}, 900, 'swing');
						}
					}
				}

			})
			map.fitBounds(bounds);
		}
	</script>
	<script async defer
	src="https://maps.googleapis.com/maps/api/js?key=env('GOOGLE_API_KEY')&callback=initMap&language={{ $locale }}">
</script>
@endif

</body>
</html>