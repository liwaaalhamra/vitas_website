@extends('layouts/main')
@section ('content')

@include('components/banner')

<div class="py-5">
	<div class="py-3">
		<h3 class="text-black text-center mb-5"><b>{{ $sections['locate-us']['title'] }}</b></h3>
		<div class="row">
			<div class="col-md-5 pr-0 ar-pr-3">
				<ul class="list-style-none bg-secondary locate-us-list text-white">
					@foreach($branches as $branch)
					<li class="py-4 px-5" data-location="{{ $branch['id'] }}">
						<div class="position-relative">
							<h6 class="mb-1"><b>{{ $branch['title'] }}</b></h6>
							<p>{{ $branch['text'] }}</p>
						</div>
					</li>
					@endforeach
				</ul>
			</div>
			<div class="col-md-7 pl-0 ar-pl-3">
				<div id="map" class="map"></div>
			</div>
		</div>
	</div>
</div>

@endsection