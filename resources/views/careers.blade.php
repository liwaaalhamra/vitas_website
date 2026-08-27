@extends('layouts/main')
@section ('content')

@include('components/banner')

<div class="py-5">
	<div class="container py-3">
		<h3 class="text-black text-center mb-5"><b>{{ $sections['current-vacancies']['title'] }}</b></h3>

		<div class="row">
			@foreach($careers as $career)
			<div class="col-md-6 pr-3 pr-lg-5 ar-pr-md-3 mb-3">
				<div class="bg-grey-2 text-black p-4">
					<div class="row">
						<div class="col-lg-7 order-2 order-lg-1">
							<h6 class="text-primary mb-2"><b>{{ $career['title'] }}</b></h6>
							<em>{{ $career['subtitle'] }}</em>
						</div>
						<div class="col-lg-5 text-right order-1 order-lg-2 mb-3 mb-lg-0">
							<a href="{{ route('singleCareer', [$locale, $career['slug']]) }}">
								<button class="btn btn-custom btn-filled px-3"><b>{{ trans('app.apply-now') }}</b></button>
							</a>
						</div>
					</div>
					<div class="rich-text mt-3">
						{!! $career['text'] !!}
					</div>
				</div>
			</div>
			@endforeach
		</div>

	</div>
</div>

<div class="vacancies text-center text-white background-image fixed py-5" style="background-image: url({{ Voyager::image(json_decode($sections['future-vacancies']['images'])[0]) }})">
	<div class="container position-relative">
		<h5 class="mb-3"><b>{{ $sections['future-vacancies']['title'] }}</b></h5>
		<div class="mb-3">{!! $sections['future-vacancies']['text'] !!}</div>
		<a href="{{ route('futureVacancies', $locale) }}">
			<button class="btn btn-custom btn-white px-5"><b>{{ trans('app.apply-now') }}</b></button>
		</a>
	</div>
</div>

@endsection