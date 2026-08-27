<!DOCTYPE html>
<html lang="en">
<head>
	<title>{{ env('APP_NAME') }} | {{ $page_title }}</title>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	{{-- <link rel="canonical" href="{{ URL::current() }}"> --}}
	{{-- <meta name="robots" content="@if(View::hasSection('meta_robot'))@yield('meta_robot')@endif"> --}}
	{{-- Primary Meta Tags --}}
	{{-- <meta name="title" content="@if(View::hasSection('title'))@yield('title')@else{{ $seo['title'] }}@endif">
	<meta name="description" content="@if(View::hasSection('description'))@yield('description')@else{{ $seo['description'] }}@endif"> --}}
	{{-- <meta name="robots" content="noindex"> --}}
	{{-- Open Graph / Facebook --}}
	{{-- <meta property="og:type" content="website">
	<meta property="og:url" content="@if(View::hasSection('url'))@yield('url')@else{{ route('home') }}@endif">
	<meta property="og:title" content="@if(View::hasSection('title'))@yield('title')@else{{ $seo['title'] }}@endif">
	<meta property="og:description" content="@if(View::hasSection('description'))@yield('description')@else{{ $seo['description'] }}@endif">
	<meta property="og:image" content="@if(View::hasSection('image'))@yield('image')@else{{ asset('assets-web/images/preview-link/image.jpeg') }}@endif"> --}}
	{{-- Twitter --}}
	{{-- <meta property="twitter:card" content="summary_large_image">
	<meta property="twitter:url" content="@if(View::hasSection('url'))@yield('url')@else{{ route('home') }}@endif">
	<meta property="twitter:title" content="@if(View::hasSection('title'))@yield('title')@else{{ $seo['title'] }}@endif">
	<meta property="twitter:description" content="@if(View::hasSection('description'))@yield('description')@else{{ $seo['description'] }}@endif">
	<meta property="twitter:image" content="@if(View::hasSection('image'))@yield('image')@else{{ asset('assets-web/images/preview-link/image.jpeg') }}@endif"> --}}
	{{-- Favicon --}}
	<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets-web/images/favicon/apple-touch-icon.png') }}">
	<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets-web/images/favicon/favicon-32x32.png') }}">
	<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets-web/images/favicon/favicon-16x16.png') }}">
	<link rel="manifest" href="{{ asset('assets-web/images/favicon/site.webmanifest') }}">
	<link rel="mask-icon" href="{{ asset('assets-web/images/favicon/safari-pinned-tab.svg" color="#e11118') }}">
	<meta name="msapplication-TileColor" content="#e11118">
	<meta name="theme-color" content="#e11118">
	{{-- Fonts --}}

	{{-- Core CSS Files --}}
	@if(App::environment('local'))
	<link rel="stylesheet" type="text/css" href="{{ asset('assets-web/libraries/bootstrap-5.1.3/bootstrap.min.css') }}"/>
	@else
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
	@endif
	{{-- Pushed StyleSheets --}}
	@stack('stylesheets')
	{{-- Main CSS --}}
	<link rel="stylesheet" type="text/css" href="{{ asset('assets-web/css/main.css') }}?v={{ env('CSS_MAIN') }}"/>
</head>
<body>
	{{-- Navbar --}}
	@include('layouts_delete.header.navbar')

	{{-- Content --}}
	<main>
		@yield('content')
	</main>

	{{-- Footer --}}
	@include('layouts_delete.footer.footer')

	{{-- Core JS Files --}}
	@if(App::environment('local'))
	<script type="text/javascript" src="{{ asset('assets-web/libraries/jquery-3.6.0/jquery.min.js') }}"/></script>
	<script type="text/javascript" src="{{ asset('assets-web/libraries/bootstrap-5.1.3/bootstrap.min.js') }}"/></script>
	@else
	<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
	@endif
	{{-- Pushed Scripts --}}
	@stack('scripts')
	{{-- Main JS --}}
	<script type="text/javascript" src="{{ asset('assets-web/js/main.js') }}?v={{ env('JS_MAIN') }}"></script>
</body>
</html>
