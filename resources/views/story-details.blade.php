@extends('layouts/main')
@section ('content')

@include('components/banner')

<div class="py-5">
	<div class="container py-3">
		<div class="row justify-content-end mb-4">
			<div class="col-lg-12 mb-3 mb-lg-0 ">
				<h3 class="text-black text-center mb-1"><b>{{ $story['title'] }}</b></h3>
				<h6 class="text-center">{{ $story['date'] }}</h6>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12 text-justify">
				{!! $story['text'] !!}

				
			</div>
		</div>

		@if(isset($story['video_link']))	
		<div class="pt-4 text-left">	
			<a href="{{$story['video_link']}}" target="_blank" class="fs-5" style="color:#007bff !important;" >{{$story['video_link']}}</a>
		</div>
		@endif
	</div>
</div>

@endsection