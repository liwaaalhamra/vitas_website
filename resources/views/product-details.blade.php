@extends('layouts/main')
@section ('content')

@include('components/banner')

<div class="py-5">
	<div class="container py-3">
		<div class="row justify-content-end mb-4">
			<div class="col-lg-4 mb-3 mb-lg-0 ">
				<h3 class="text-black text-center"><b>{{ $product['title'] }}</b></h3>
			</div>
			<div class="col-lg-4 text-center text-lg-right">
				<a href="mailto:?subject=Vitas product - {{ ucfirst(strtolower($product['title'])) }}&body=I want to share this link with you:%0D%0A {{ Request::url() }}">
					<button class="btn btn-custom btn-filled-grey mx-auto mb-2 mb-sm-0 px-3 d-block d-sm-inline">
						<svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 481.6 481.6" style="enable-background:new 0 0 481.6 481.6;" xml:space="preserve"><path fill="#fff" d="M381.6,309.4c-27.7,0-52.4,13.2-68.2,33.6l-132.3-73.9c3.1-8.9,4.8-18.5,4.8-28.4c0-10-1.7-19.5-4.9-28.5l132.2-73.8c15.7,20.5,40.5,33.8,68.3,33.8c47.4,0,86.1-38.6,86.1-86.1S429,0,381.5,0s-86.1,38.6-86.1,86.1c0,10,1.7,19.6,4.9,28.5l-132.1,73.8c-15.7-20.6-40.5-33.8-68.3-33.8c-47.4,0-86.1,38.6-86.1,86.1s38.7,86.1,86.2,86.1c27.8,0,52.6-13.3,68.4-33.9l132.2,73.9c-3.2,9-5,18.7-5,28.7c0,47.4,38.6,86.1,86.1,86.1s86.1-38.6,86.1-86.1S429.1,309.4,381.6,309.4z M381.6,27.1c32.6,0,59.1,26.5,59.1,59.1s-26.5,59.1-59.1,59.1s-59.1-26.5-59.1-59.1S349.1,27.1,381.6,27.1z M100,299.8c-32.6,0-59.1-26.5-59.1-59.1s26.5-59.1,59.1-59.1s59.1,26.5,59.1,59.1S132.5,299.8,100,299.8z M381.6,454.5c-32.6,0-59.1-26.5-59.1-59.1c0-32.6,26.5-59.1,59.1-59.1s59.1,26.5,59.1,59.1C440.7,428,414.2,454.5,381.6,454.5z"/></svg>
						<b>{{ trans('app.refer-a-friend') }}</b>
					</button>
				</a>
				<a href="{{ route('view-apply-loan', [$locale, $product['slug']]) }}">
					<button class="btn btn-custom btn-filled mx-auto d-block d-sm-inline"><b>{{ trans('app.apply-now') }}</b></button>
				</a>
			</div>
		</div>
		<div class="row">
			<div class="{{ $product['images'] ? "col-md-6" : "col-md-12" }} text-justify">
				{!! $product['text'] !!}
			</div>
			<div class="col-md-6">

				@if(isset($product['images']))
				@include('components/images-grid')
				@endif

			</div>
		</div>
	</div>
</div>

@endsection