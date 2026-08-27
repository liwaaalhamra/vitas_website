@php $count = 0; @endphp
@php $x = 0; @endphp
@foreach($imagesGrid as $key => $image)
@if($count % 2 == 0) <div class="row"> @endif

	@if($x % 2 == 0)
	@if($count % 2 == 0)
	<div class="col-md-7 pr-md-1 ar-pr-md-3 mb-3 mb-md-2" data-aos="fade-up" data-aos-delay="50">
		<div class="picture-wrapper padding-top-55">
			<img class="cover" src="{{ Voyager::image($image) }}">
		</div>
	</div>
	@else
	<div class="col-md-5 pl-md-1 ar-pl-md-3 mb-3 mb-md-2" data-aos="fade-up" data-aos-delay="100">
		<div class="picture-wrapper padding-top-55 h-100">
			<img class="cover" src="{{ Voyager::image($image) }}">
		</div>
	</div>
	@php ($x++)
	@endif
	@php ($count++)
	@else
	@if($count % 2 == 0)
	<div class="col-md-5 pr-md-1 ar-pr-md-3 mb-3 mb-md-2" data-aos="fade-up" data-aos-delay="150">
		<div class="picture-wrapper padding-top-55 h-100">
			<img class="cover" src="{{ Voyager::image($image) }}">
		</div>
	</div>
	@else
	<div class="col-md-7 pl-md-1 ar-pl-md-3 mb-3 mb-md-2" data-aos="fade-up" data-aos-delay="200">
		<div class="picture-wrapper padding-top-55">
			<img class="cover" src="{{ Voyager::image($image) }}">
		</div>
	</div>
	@php ($x++)
	@endif
	@php ($count++)
	@endif

@if(count($imagesGrid) == 0 || $count != 0 && $count % 2 == 0) </div> @endif
@endforeach
@if($count % 2 != 0) </div> @endif