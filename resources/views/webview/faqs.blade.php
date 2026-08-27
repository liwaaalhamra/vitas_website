<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
	<link rel="stylesheet" type="text/css" href="{{ asset('assets/libraries/bootstrap-4.3.1/bootstrap.min.css') }}">
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/main.css?v=2.2') }}"/>
	@if($locale == 'ar')
	<link rel="stylesheet" type="text/css" href="{{ asset('assets/fonts/NotoNaskhArabic/styles.css') }}"/>
	<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/main-ar.css?v=2.2') }}"/>
	@endif
</head>
<body>

	<ul class="font-family-webview list-style-none webview-faqs-list text-black">
		@foreach($faqs as $key => $faq)
		<li @if($key % 2 == 0) class="bg-grey-2" @endif>
			<div class="position-relative pr-2">
				<b>{{ $faq['title'] }}</b>
				<img src="{{ asset('assets/images/icons/angle-right.svg') }}">
			</div>
			<div class="content">
				<div class="line-height-22 pt-3">
					{!! $faq['text'] !!}
				</div>
			</div>
		</li>
		@endforeach
	</ul>

	<script type="text/javascript" src="{{ asset('assets/js/jquery-3.1.1.js') }}"></script>
	<script type="text/javascript">
		$(document).ready(function () {
			$(".webview-faqs-list li").click(function(){
				$(this).toggleClass("active");
				$(this).find(".content").slideToggle();
			});
		});
	</script>

</body>
</html>