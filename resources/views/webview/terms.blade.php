<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
	<link rel="stylesheet" type="text/css" href="{{ asset('assets/libraries/bootstrap-4.3.1/bootstrap.min.css') }}">
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/main.css?v=2.3') }}"/>
	@if($locale == 'ar')
	<link rel="stylesheet" type="text/css" href="{{ asset('assets/fonts/NotoNaskhArabic/styles.css') }}"/>
	<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/main-ar.css?v=2.3') }}"/>
	@endif
</head>
<body>

	<div class="font-family-webview p-4">
		{!! $terms['text'] !!}
	</div>

</body>
</html>