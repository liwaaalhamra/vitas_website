@extends('layouts/main')
@section ('content')

<div class="mb-5">
	@include('components/banner')
</div>

<div class="px-3 px-md-5">

	@php $count = 0; @endphp
	@foreach($products as $product)
	@if($count % 4 == 0) <div class="row"> @endif
		<div class="col-sm-6 col-xl-3 mb-5" data-aos="fade-up" data-aos-delay="{{ ($count % 4 == 0) ? 50 : 100 }}">
			<div class="element-hover">
				<div class="bg-grey-2 text-center p-3">
					<div class="py-1">
						<a href="{{ route('singleProducts', [$locale, $product['slug']]) }}">
							<p class="text-primary mb-1"><b>{{ $product['title'] }}</b></p>
						</a>
						<p class="text-black font-size-11">{{ $product['subtitle'] }}</p>
					</div>
				</div>
				<div class="picture-wrapper overflow-hidden padding-top-77">
					<a href="{{ route('singleProducts', [$locale, $product['slug']]) }}">
						<img class="cover" src="{{ Voyager::image($product['thumbnail']) }}" alt="Products">
					</a>
				</div>
				<div class="bg-grey-2 py-3 px-4">
					<p class="line-height-22 text-ellipsis-4">{{ $product['text_preview'] }}</p>
					<div class="mb-4">
						<a class="text-black" href="{{ route('singleProducts', [$locale, $product['slug']]) }}"><b>{{ trans('app.read-more') }}</b></a>
					</div>
					<a href="{{ route('view-apply-loan', [$locale, $product['slug']]) }}">
						<button class="btn btn-custom btn-filled w-100"><b>{{ trans('app.apply-now') }}</b></button>
					</a>
				</div>
			</div>
		</div>
		@php ($count++)
	@if($count % 4 == 0) </div> @endif
	@endforeach
@if($count % 4 != 0) </div> @endif
</div>

@endsection