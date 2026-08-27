@extends('layouts/main')
@section ('content')

<div class="mb-5">
	@include('components/banner')
</div>

<div class="px-3 px-md-5">

	@php $count = 0; @endphp
	@foreach($events as $event)
	@if($count % 2 == 0) <div class="row"> @endif
		<div class="col-md-6 mb-5" data-aos="fade-up" data-aos-delay="{{ ($count % 2 == 0) ? 50 : 100 }}">
			<div class="element-hover">
				<div class="bg-grey-2 text-center p-4">
					<div class="py-1">
						<a href="{{ route('singleCommunity', [$locale, $event['slug']]) }}">
							<h5 class="text-primary mb-2"><b>{{ $event['title'] }}</b></h5>
						</a>
						@if($event['date'])
						<h6 class="text-black">{{ $event['date'] }}</h6>
						@endif
					</div>
				</div>
				<div class="picture-wrapper overflow-hidden padding-top-77 bg-grey">
					<a href="{{ route('singleCommunity', [$locale, $event['slug']]) }}">
						@if($event['image'])
						<img class="cover" src="{{ Voyager::image($event['image']) }}" alt="community events - {{ $event['title'] }}">
						@else
						<b class="text-center">IMAGE NOT AVAILABLE</b>
						@endif
					</a>
				</div>
				<div class="bg-grey-2 p-4 px-md-5">
					<p class="line-height-22 text-justify text-ellipsis-4 mb-2">{{ $event['text'] }}</p>
					<div class="mb-4">
						<a href="{{ route('singleCommunity', [$locale, $event['slug']]) }}"><b>{{ trans('app.read-more') }}</b></a>
					</div>
				</div>
			</div>
		</div>
		@php ($count++)
	@if($count % 2 == 0) </div> @endif
	@endforeach
@if($count % 2 != 0) </div> @endif
</div>

@endsection