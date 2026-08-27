@extends('layouts/main')
@section ('content')

@include('components/banner')

<div class="py-5">
	<div class="container py-3">
		<h3 class="text-black text-center mb-5"><b>{{ $sections['faqs']['title'] }}</b></h3>

		<ul class="list-style-none faqs-list text-black">
			@foreach($faqs as $key => $faq)
			<li class="bg-grey-2 py-3 px-4" data-aos="fade-up" data-aos-delay="{{ $key * 50 }}">
				<div class="position-relative">
					<b>{{ $faq['title'] }}</b>
					<img src="{{ asset('assets/images/icons/arrow-white.png') }}">
				</div>
				<div class="content">
					<div class="line-height-22 pt-3">
						{!! $faq['text'] !!}
					</div>
				</div>
			</li>
			@endforeach
		</ul>
	</div>
</div>

@endsection