@extends('layouts/main')
@section ('content')

@include('components/banner')

<div class="py-5">
	<div class="container py-3">
		<h3 class="text-black text-center mb-5"><b>{{ $sections['social-impact']['title'] }}</b></h3>
		<div class="row">
			<div class="col-md-6 line-height-22 text-justify mb-3 mb-md-0">
				{!! $sections['social-impact']['text'] !!}
			</div>
			<div class="col-md-6">

				@include('components/images-grid')

			</div>
		</div>
	</div>
</div>

<div class="box-shadow py-5">
	<div class="container py-3">
		<h3 class="text-black text-center mb-5"><b>{{ $sections['vitas-in-numbers']['title'] }}</b></h3>
		<div class="row">
			<div class="bars col-md-6 pr-lg-5 ar-pr-md-3">
				<ul class="list-style-none text-black pr-lg-5 ar-pr-md-0">
					{{-- @foreach($numbers[0] as $number)
					<li class="mb-4 pb-2" data-aos="fade-right">
						<b>{{ $number['title'] }}&nbsp;<span class="font-number text-primary ml-3">@if($locale == 'ar') {{ $number['ar_value'] }} @else {{ number_format($number['value']) }} @endif</span></b>
						<div class="bar mt-2" data-percentage="{{ $number['value'] * 100 / $number['max_value'] }}"><span></span></div>
					</li>
					@endforeach --}}
					<li class="mb-4 pb-2" data-aos="fade-right">
						<b><span class="text-uppercase">@if($locale == 'en') {{ 'Number Of Jobs Created' }} @elseif($locale == 'ar') {{ 'الوظائف التي تم انشاؤها' }} @endif</span>&nbsp;<span class="font-number text-primary ml-3">{{ (int)$numbers['NumberOfJobsCreated'] }}</span></b>
						<div class="bar mt-2" data-percentage="{{ (int)$numbers['NumberOfJobsCreated'] * 100 / 100000 }}"><span></span></div>
					</li>
					<li class="mb-4 pb-2" data-aos="fade-right">
						<b><span class="text-uppercase">@if($locale == 'en') {{ 'Number Of Jobs Sustained' }} @elseif($locale == 'ar') {{ 'وضائف مستدامة' }} @endif</span>&nbsp;<span class="font-number text-primary ml-3">{{ (int)$numbers['NumberOfJobsSustained'] }}</span></b>
						<div class="bar mt-2" data-percentage="{{ (int)$numbers['NumberOfJobsSustained'] * 100 / 100000 }}"><span></span></div>
					</li>
					<li class="mb-4 pb-2" data-aos="fade-right">
						<b><span class="text-uppercase">@if($locale == 'en') {{ 'Women Served' }} @elseif($locale == 'ar') {{ 'خدمات النساء' }} @endif</span>&nbsp;<span class="font-number text-primary ml-3">{{ (int)$numbers['WomenServed'] }}</span></b>
						<div class="bar mt-2" data-percentage="{{ (int)$numbers['WomenServed'] * 100 / 100000 }}"><span></span></div>
					</li>
					<li class="mb-4 pb-2" data-aos="fade-right">
						<b><span class="text-uppercase">@if($locale == 'en') {{ 'Number Of Improved Houses' }} @elseif($locale == 'ar') {{ 'عدد المنازل المحسنة' }} @endif</span>&nbsp;<span class="font-number text-primary ml-3">{{ (int)$numbers['NumberOfImprovedHouses'] }}</span></b>
						<div class="bar mt-2" data-percentage="{{ (int)$numbers['NumberOfImprovedHouses'] * 100 / 100000 }}"><span></span></div>
					</li>
				</ul>
			</div>
			<div class="bars col-md-6 pl-lg-5 ar-pl-md-3">
				<ul class="list-style-none text-black pl-lg-5 ar-pl-md-0">
					{{-- @foreach($numbers[1] as $number)
					<li class="mb-4 pb-2" data-aos="fade-left">
						<b>{{ $number['title'] }}&nbsp;<span class="font-number text-primary ml-3">@if($locale == 'ar') {{ $number['ar_value'] }} @else {{ number_format($number['value']) }} @endif</span></b>
						<div class="bar mt-2" data-percentage="{{ $number['value'] * 100 / $number['max_value'] }}"><span></span></div>
					</li>
					@endforeach --}}
					<li class="mb-4 pb-2" data-aos="fade-right">
						<b><span class="text-uppercase">@if($locale == 'en') {{ 'Youth' }} @elseif($locale == 'ar') {{ 'خدمات الشباب' }} @endif</span>&nbsp;<span class="font-number text-primary ml-3">{{ (int)$numbers['Youth'] }}</span></b>
						<div class="bar mt-2" data-percentage="{{ (int)$numbers['Youth'] * 100 / 500000 }}"><span></span></div>
					</li>
					<li class="mb-4 pb-2" data-aos="fade-right">
						<b><span class="text-uppercase">@if($locale == 'en') {{ 'Number Of Active Loans' }} @elseif($locale == 'ar') {{ 'عدد القروض النشطة' }} @endif</span>&nbsp;<span class="font-number text-primary ml-3">{{ (int)$numbers['NumberOfActiveLoans'] }}</span></b>
						<div class="bar mt-2" data-percentage="{{ (int)$numbers['NumberOfActiveLoans'] * 100 / 100000 }}"><span></span></div>
					</li>
					<li class="mb-4 pb-2" data-aos="fade-right">
						<b><span class="text-uppercase">@if($locale == 'en') {{ 'Number Of Disbursed Loans' }} @elseif($locale == 'ar') {{ 'عدد القروض المصروفة' }} @endif</span>&nbsp;<span class="font-number text-primary ml-3">{{ (int)$numbers['NumberOfDisbursedLoans'] }}</span></b>
						<div class="bar mt-2" data-percentage="{{ (int)$numbers['NumberOfDisbursedLoans'] * 100 / 500000 }}"><span></span></div>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>

<div class="py-5 px-3 px-md-5 bg-grey">
	<div class="py-4">
		<h3 class="text-black text-center mb-5"><b>{{ $sections['community-events']['title'] }}</b></h3>
		<div class="row" >
			@foreach($events as $key => $event)
			<div class="col-sm-6 col-lg-3 mb-3 mb-lg-0" data-aos="fade-up" data-aos-delay="{{ $key * 50 }}">
				<div class="element-hover image-colored">
					<a href="{{ route('singleCommunity', [$locale, $event['slug']]) }}">
						<div class="picture-wrapper overflow-hidden padding-top-65">
							<img class="cover" src="{{ Voyager::image($event['image']) }}" alt="community events - {{ $event['title'] }}">
						</div>
					</a>
					<div class="bg-white text-black py-3 px-4">
						<a href="{{ route('singleCommunity', [$locale, $event['slug']]) }}">
							<p class="text-ellipsis-1"><b>{{ $event['title'] }}</b></p>
						</a>
						@if($event['date'])<p class="mb-1"><em>{{ $event['date'] }}</em></p>@endif
						<p class="text-justify text-ellipsis-4 mb-2">{{ $event['text'] }}</p>
						<a href="{{ route('singleCommunity', [$locale, $event['slug']]) }}">
							<b>{{ trans('app.read-more') }}</b>
						</a>
					</div>
				</div>
			</div>
			@endforeach
		</div>
	</div>
</div>

@endsection