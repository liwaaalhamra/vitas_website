@extends('layouts/main')
@section ('content')

@include('components/banner')

<div class="py-5">
	<div class="container py-3">
		<h3 class="text-black text-center mb-5"><b>{{ $sections['about-us']['title'] }}</b></h3>
		<div class="row">
			<div class="text-justify line-height-22 col-md-6 mb-3 mb-md-0">
				{!! $sections['about-us']['text'] !!}
			</div>
			<div class="col-md-6">

				@include('components/images-grid')

			</div>
		</div>
	</div>
</div>

<div class="py-5 bg-grey">
	<div class="container py-3">
		<h3 class="text-black text-center mb-5"><b>{{ $sections['our-history']['title'] }}</b></h3>
		<div class="our-history-slick text-black pt-5">
			@foreach($history_events as $history_event)
			<div class="our-history-slider">
				<div>
					<div class="picture">
						<img class="cover" src="{{ Voyager::image($history_event['image']) }}" alt="{{ $history_event['title'] }}">
					</div>
					<div class="info">
						<h6 class="text-uppercase mb-1"><b>{{ $history_event['date'] }}</b></h6>
						<h6 class="mb-3"><b>{{ $history_event['title'] }}</b></h6>
						<p class="line-height-25">{{ $history_event['text'] }}</p>
					</div>
				</div>
			</div>
			@endforeach
		</div>
	</div>
</div>

@endsection