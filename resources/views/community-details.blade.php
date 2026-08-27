@extends('layouts/main')
@section ('content')

@include('components/banner')

<div class="py-5">
	<div class="container py-3">
		<div class="row justify-content-end mb-4">
			<div class="col-lg-12 mb-3 mb-lg-0 ">
				<h3 class="text-black text-center mb-1"><b>{{ $event['title'] }}</b></h3>
				@if($event['date'])
				<h6 class="text-center">{{ $event['date'] }}</h6>
				@endif
			</div>
		</div>
		<div class="row">
			<div class="col-md-12 text-justify">
				{!! $event['text'] !!}
			</div>
		</div>
	</div>
</div>

@endsection