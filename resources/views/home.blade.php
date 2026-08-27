@extends('layouts/main')

@push('head')
<!-- Event snippet for Check Eligibility conversion page In your html page,
	add the snippet and call gtag_report_conversion when someone clicks on the chosen link or button. -->
	<script>
		function gtag_report_conversion(url) {
			var callback = function () {
				if (typeof(url) != 'undefined') {
					window.location = url;
				}
			};

			gtag('event', 'conversion', {
				'send_to': 'AW-684299578/CCrXCIDC0NMBELqqpsYC',
				'event_callback': callback
			});
			return false;
		}
	</script>
	@endpush

	@section ('content')

	{{-- Product Slick Section --}}
	<div class="custom-slick mb-0">
		@foreach($advertisements as $advertisement)
		<div class="custom-slider">
			@if($advertisement->link)
			<a href="{{ $advertisement->link }}">
				@endif

				<picture>
					<source media="(max-width:575px)" srcset="{{ Voyager::image($advertisement['mobile_image']) }}">
					<source media="(min-width:576px)" srcset="{{ Voyager::image($advertisement['image']) }}">
					<img src="{{ Voyager::image($advertisement['image']) }}" style="width:auto;">
				</picture>

				@if($advertisement->link)
			</a>
			@endif
		</div>
		@endforeach

		@foreach($products as $product)
		<div class="custom-slider">
			<img class="cover" src="{{ Voyager::image($product['thumbnail']) }}" alt="product - {{ $product['title'] }}">
			<div class="info">
				<h3 class="text-black mb-3"><b>{{ $product['title'] }}</b></h3>
				<p class="line-height-22 letter-spacing-025 text-ellipsis-4 mb-3 dir-rtl">{{ $product['text_preview'] }}</p>
				<a href="{{ route('view-apply-loan', [$locale, $product['slug']]) }}"><button class="btn btn-custom btn-filled"><b>{{ trans('app.apply-now') }}</b></button></a>
			</div>
		</div>
		@endforeach
	</div>

	{{-- About Us Section --}}
	<div class="background-image fixed py-5 text-center" style="background-image: url({{ asset('assets/images/AboutBGD.jpg') }})">
		<div class="container-fluid">
			<div class="row justify-content-center py-3">
				<div class="col-md-7">
					<h4 class="text-black mb-4"><b>{{ $sections['home-about-us']['title'] }}</b></h4>
					<div class="line-height-22 letter-spacing-025 text-grey mb-4">{!! $sections['home-about-us']['text'] !!}</div>
					<a href="{{ route('about', $locale) }}"><button class="btn btn-custom btn-filled"><b>{{ trans('app.read-more') }}</b></button></a>
				</div>
			</div>
		</div>
	</div>

	<div class="container-fluid">
		<div class="row">
			{{-- Vitas in Numbers Section --}}
			<div class="bars col-md-6 bg-grey-1 px-3 py-5 p-md-5">
				<div class="px-0 px-md-5 py-md-3 ar-px-5 ar-px-md-5">
					<h4 class="text-black mb-4" data-aos="fade-right"><b>{{ $sections['vitas-in-numbers']['title'] }}</b></h4>
					<ul class="list-style-none text-black">
						@foreach($numbers as $number)
						<li class="mb-4 pb-2" data-aos="fade-right">
							<b>{{ $number['title'] }}&nbsp;<span class="font-number text-primary ml-3">@if($locale == 'ar') {{ $number['ar_value'] }} @else {{ number_format($number['value']) }} @endif</span></b>
							<div class="bar mt-2" data-percentage="{{ $number['value'] * 100 / $number['max_value'] }}"><span></span></div>
						</li>
						@endforeach
						{{-- <li class="mb-4 pb-2" data-aos="fade-right">
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
						</li> --}}
					</ul>
				</div>
			</div>
			{{-- Success Stories Section --}}
			<div class="col-md-6 bg-grey px-3 py-5 px-md-4 pr-lg-0 ar-pr-lg-4">
				<h4 class="text-black text-center mb-4"><b>{{ $sections['success-stories']['title'] }}</b></h4>
				<div class="success-story-slick mb-0">
					@foreach($stories as $key => $story)
					<div class="element-hover image-colored mx-1">
						<a href="{{ route('singleStory', [$locale, $story['slug']]) }}">
							<div class="picture-wrapper overflow-hidden padding-top-77 bg-grey-2">
								@if($story['image'])
								<img class="cover" src="{{ Voyager::image($story['image']) }}" alt="success story - {{ $story['title'] }}">
								@else
								<b class="text-center font-size-11">IMAGE NOT AVAILABLE</b>
								@endif
							</div>
							<div class="bg-white text-center p-3">
								<p class="text-black mb-1"><b>{{ $story['title'] }}</b></p>
								<p class="text-ellipsis-4">{{ $story['text_preview'] }}</p>
							</div>
						</a>
					</div>
					@endforeach
				</div>
			</div>
		</div>
	</div>

	{{-- Vitas Indicators Section --}}
	<div class="about-vitas box-shadow bg-primary text-white text-center">
		<div class="container">
			<div class="row justify-content-center">
				@foreach($abouts as $key => $about)
				<div class="col mb-3 mb-md-0" data-aos="zoom-in-up" data-aos-delay="{{ $key * 50 }}">
					<img class="d-block mx-auto mb-2" src="{{ Voyager::image($about['icon']) }}" alt="{{ $about['value'] . " " . $about['title'] }}">
					<p><b class="font-number">{{ $about['value'] }}</b></p>
					<p>{{ $about['title'] }}</p>
				</div>
				@endforeach
			</div>
		</div>
	</div>

	{{-- Community Events Section --}}
	<div class="py-5 px-3 px-md-5">
		<div class="row justify-content-end mb-4">
			<div class="col-md-6 mb-3 mb-md-0">
				<h3 class="text-black text-center"><b>{{ $sections['community-events']['title'] }}</b></h3>
			</div>
			<div class="col-md-3 text-right">
				<a href="{{ route('community-events', $locale) }}">
					<button class="btn btn-custom text-primary"><b>{{ trans('app.view-all') }}</b></button>
				</a>
			</div>
		</div>

		<div class="row mb-3">
			<div class="col-md-6 pr-3 pr-md-0 ar-pr-md-3">
				<div class="picture-wrapper padding-top-70">
					<a href="{{ route('singleCommunity', [$locale, $featuredEvent['slug']]) }}">
						<img class="cover" src="{{ Voyager::image($featuredEvent['image']) }}" alt="community events - {{ $featuredEvent['title'] }}">
					</a>
				</div>
			</div>
			<div class="col-md-6 pl-md-0 ar-pl-md-3">
				<div class="d-table bg-grey text-black w-100 h-100 py-3 px-4 py-md-0 px-md-5">
					<div class="d-table-cell align-middle px-0 px-md-5">
						<a href="{{ route('singleCommunity', [$locale, $featuredEvent['slug']]) }}">
							<h4 class="mb-2 text-ellipsis-1"><b>{{ $featuredEvent['title'] }}</b></h4>
						</a>
						@if($featuredEvent['date'])<p class="mb-3"><em>{{ $featuredEvent['date'] }}</em></p>@endif
						<p class="text-ellipsis-8 mb-2">{{ $featuredEvent['text'] }}</p>
						<a class="text-black" href="{{ route('singleCommunity', [$locale, $featuredEvent['slug']]) }}">
							{{-- <b>{{ trans('app.read-more') }}</b> --}}
							<button class="btn btn-custom btn-filled py-1 px-2">{{ trans('app.read-more') }}</button>
						</a>
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			@foreach($events as $key => $event)
			<div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
				<div class="element-hover image-colored">
					<a href="{{ route('singleCommunity', [$locale, $event['slug']]) }}">
						<div class="picture-wrapper overflow-hidden padding-top-65">
							<img class="cover" src="{{ Voyager::image($event['image']) }}" alt="community events - {{ $event['title'] }}">
						</div>
					</a>
					<div class="bg-grey text-black py-3 px-4">
						<a href="{{ route('singleCommunity', [$locale, $event['slug']]) }}">
							<p class="text-ellipsis-1"><b>{{ $event['title'] }}</b></p>
						</a>
						@if($event['date'])<p class="mb-1"><em>{{ $event['date'] }}</em></p>@endif
						<p class="text-justify text-ellipsis-4 mb-2">{{ $event['text'] }}</p>
						<a class="text-black" href="{{ route('singleCommunity', [$locale, $event['slug']]) }}">
							{{-- <b>{{ trans('app.read-more') }}</b> --}}
							<button class="btn btn-custom btn-filled py-1 px-2">{{ trans('app.read-more') }}</button>
						</a>
					</div>
				</div>
			</div>
			@endforeach
		</div>
	</div>

	{{-- Testimonials Section --}}
	<div id="check_eligibility" class="custom-slick testimonial-slick bg-secondary text-white mb-0" data-aos="fade-right" data-aos-duration="1000">
		@foreach($testimonials as $testimonial)
		<div class="text-center pt-4 pb-5">
			<div class="container-fluid pt-2">
				<h6 class="testimonial letter-spacing-05 pre-line mb-2"><em>"</em> {{ $testimonial['text'] }} <em>"</em></h6>
				<small class="letter-spacing-1"><em><b>{{ $testimonial['name'] }}, </b>{{ $testimonial['position'] }}</em></small>
			</div>
		</div>
		@endforeach
	</div>

	{{-- Check Eligibility Form --}}
	<div class="bg-grey py-5">
		<div class="container py-3">
			<div class="row justify-content-center text-center mb-5">
				<div class="col-md-6 text-black">
					<h3 class="letter-spacing-05 mb-3"><b>{{ $sections['check-loan']['title'] }}</b></h3>
					<div class="line-height-22">{!! $sections['check-loan']['text'] !!}</div>
				</div>
			</div>
			<form method="post" action="{{ route('check-loan', $locale) }}">
				@csrf

				@if ($message = Session::get('danger'))
				<div class="alert alert-danger d-block" role="alert">
					<h6>{!! $message !!}</h6>
				</div>
				@endif

				@if ($message = Session::get('success'))
				<div class="alert alert-success d-block" role="alert">
					<h6>{!! $message !!}</h6>
				</div>
				@endif

				<div class="row mb-2">
					<div class="col-md-6 pr-3 pr-md-2 ar-pr-md-3">
						<div class="form-group" data-aos="fade-up">
							<input type="text" class="form-control form-custom rounded-0" name="full_name" placeholder="{{ trans('app.full_name') }}" value="{{ old('full_name') }}">

							@if ($errors->has('full_name'))
							<span class="invalid-feedback d-block" role="alert">
								<strong>{{ $errors->first('full_name') }}</strong>
							</span>
							@endif
						</div>

						<div class="form-group" data-aos="fade-up">
							<input type="text" class="form-control form-custom rounded-0" name="phone" placeholder="{{ trans('app.phone') }}" value="{{ old('phone') }}">

							@if ($errors->has('phone'))
							<span class="invalid-feedback d-block" role="alert">
								<strong>{{ $errors->first('phone') }}</strong>
							</span>
							@endif
						</div>

						<div class="form-group z-index-1" data-aos="fade-up">
							<div class="custom-select-wrapper test">
								<input type="hidden" name="type_of_loans">
								<input class="form-control form-custom" placeholder="{{ trans('app.type_of_loans') }}" readonly="">
								<ul>
									@foreach($products as $product)
									@if($product['id'] != 3)
									<li data-value="{{ $product['id'] }}" @if(old('type_of_loans') == $product['id']) selected @endif>{{ $product['title'] }}</li>
									@endif
									@endforeach
								</ul>
								<svg width="12.674" height="7.815" viewBox="0 0 12.674 7.815">
									<path d="M60.129,0,58.65,1.479l4.858,4.858L58.65,11.195l1.479,1.479,6.337-6.337Z" transform="translate(12.674 -58.65) rotate(90)" fill="#fff"/>
								</svg>
							</div>

							@if ($errors->has('type_of_loans'))
							<span class="invalid-feedback d-block" role="alert">
								<strong>{{ $errors->first('type_of_loans') }}</strong>
							</span>
							@endif
						</div>

						<div class="form-group" data-aos="fade-up">
							<input type="number" class="form-control form-custom rounded-0" name="age" placeholder="{{ trans('app.your_age') }}" value="{{ old('age') }}">

							@if ($errors->has('age'))
							<span class="invalid-feedback d-block" role="alert">
								<strong>{{ $errors->first('age') }}</strong>
							</span>
							@endif
						</div>

						<div class="form-group" data-aos="fade-up">
							<div class="like-input bg-white rounded-0">
								<div class="d-inline-block">
									{{ trans('app.business_owner') }}
								</div>
								<div class="d-inline-block float-right text-black">
									{{ trans('app.yes') }}
									<label class="switch mx-2">
										<input type="checkbox" name="business_owner" {{ old('business_owner') == 'on' ? 'checked' : '' }}>
										<span class="slider round"></span>
									</label>
									{{ trans('app.no') }}
								</div>
							</div>
						</div>

						<div class="form-group" data-aos="fade-up">
							<div class="like-input bg-white rounded-0">
								<div class="d-inline-block">
									{{ trans('app.employee') }}
								</div>
								<div class="d-inline-block float-right text-black">
									{{ trans('app.yes') }}
									<label class="switch mx-2">
										<input type="checkbox" name="employee" {{ old('employee') == 'on' ? 'checked' : '' }}>
										<span class="slider round"></span>
									</label>
									{{ trans('app.no') }}
								</div>
							</div>
						</div>

						<div class="append-fields"></div>
					</div>

					<div class="col-md-6 pl-3 pl-md-2 ar-pl-md-3">
						<div class="form-group" data-aos="fade-up">
							<input type="number" class="form-control form-custom rounded-0" name="monthly_income" placeholder="{{ trans('app.income') }}" value="{{ old('monthly_income') }}">

							@if ($errors->has('monthly_income'))
							<span class="invalid-feedback d-block" role="alert">
								<strong>{{ $errors->first('monthly_income') }}</strong>
							</span>
							@endif
						</div>

						<div class="form-group" data-aos="fade-up">
							<input type="number" class="form-control form-custom rounded-0" name="monthly_expenses" placeholder="{{ trans('app.expenses') }}" value="{{ old('monthly_expenses') }}">

							@if ($errors->has('monthly_expenses'))
							<span class="invalid-feedback d-block" role="alert">
								<strong>{{ $errors->first('monthly_expenses') }}</strong>
							</span>
							@endif
						</div>

						<div class="form-group" data-aos="fade-up">
							<div class="like-input bg-white rounded-0">
								<div class="d-inline-block">
									{{ trans('app.guarantors') }}
								</div>
								<div class="d-inline-block float-right text-black">
									{{ trans('app.yes') }}
									<label class="switch mx-2">
										<input type="checkbox" name="guarantors" {{ old('guarantors') == 'on' ? 'checked' : '' }}>
										<span class="slider round"></span>
									</label>
									{{ trans('app.no') }}
								</div>
							</div>
						</div>

						<div class="form-group z-index-1" data-aos="fade-up">
							<div class="custom-select-wrapper">
								<input type="hidden" name="governorate">
								<input class="form-control form-custom" placeholder="{{ trans('app.governorate') }}" readonly="">
								<ul>
									@foreach($governorates as $governorate)
									<li data-value="{{ $governorate['id'] }}" @if(old('governorate') == $governorate['id']) selected @endif>{{ $governorate['value'] }}</li>
									@endforeach
								</ul>
								<svg width="12.674" height="7.815" viewBox="0 0 12.674 7.815">
									<path d="M60.129,0,58.65,1.479l4.858,4.858L58.65,11.195l1.479,1.479,6.337-6.337Z" transform="translate(12.674 -58.65) rotate(90)" fill="#fff"/>
								</svg>
							</div>

							@if ($errors->has('governorate'))
							<span class="invalid-feedback d-block" role="alert">
								<strong>{{ $errors->first('governorate') }}</strong>
							</span>
							@endif
						</div>

						<button type="submit" onclick="return gtag_report_conversion({{ route('home', $locale) }})" class="btn btn-custom btn-filled w-100 px-5" data-aos="fade-up"><b class="px-5" data-text="{{ trans('app.submit') }}" data-loading-text="{{ trans('app.loading') }}">{{ trans('app.submit') }}</b></button>
					</div>
				</div>
			</form>
		</div>
	</div>

	{{-- Download App Section --}}
	<div class="store-wrapper text-center">
		<div class="row">
			<div class="col-md-6 py-5 py-md-0">
				<div class="d-table w-100 h-100">
					<div class="d-table-cell align-middle">
						<img class="logo mb-4" src="{{ asset('assets/images/logo.png') }}" alt="Logo"  data-aos="zoom-in-up">
						<h5 class="text-black mb-4" data-aos="zoom-in-up"><b>{{ trans('app.download_our_app') }}</b></h5>
						<a href="https://bit.ly/302AL6W" target="_blank">
							<img class="store contain" src="{{ asset('assets/images/icons/google-play-badge.png') }}" data-aos="zoom-in-up">
						</a>
						<a href="https://apple.co/2O24cyw" target="_blank">
							<img class="store contain" src="{{ asset('assets/images/icons/app-store-badge.png') }}" data-aos="zoom-in-up">
						</a>
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<img class="contain" src="{{ Voyager::image($banner) }}" alt="Download our application on play store and app store">
			</div>
		</div>
	</div>

	@endsection

	@push('scripts')
	<script type="text/javascript">
		// Check Elligible form field
		$('.custom-select-wrapper.test ul li').on('click', function () {
			var $this = $(this);
			var type = $this.data('value');
			$('.append-fields').html('');
			if(type == 1){
				$('.append-fields').append('<div class="form-group" data-aos="fade-up" data-type="age_business"><input type="text" class="form-control form-custom rounded-0" name="age_business" placeholder="{{ trans('app.age_business') }}" value="{{ old('age_business') }}">@if ($errors->has('age_business'))<span class="invalid-feedback d-block" role="alert"><strong>{{ $errors->first('age_business') }}</strong></span>@endif</div>')
			} else if(type == 2){
				$('.append-fields').append('<div class="form-group" data-aos="fade-up"><div class="like-input bg-white rounded-0"><div class="d-inline-block">{{ trans('app.own_home') }}</div><div class="d-inline-block float-right text-black">{{ trans('app.yes') }}<label class="switch mx-2"><input type="checkbox" name="own_home" {{ old('own_home') == 'on' ? 'checked' : '' }}><span class="slider round"></span></label>{{ trans('app.no') }}</div></div></div>')
			} else if(type == 4){
				$('.append-fields').append('<div class="form-group" data-aos="fade-up" data-type="age_business"><input type="text" class="form-control form-custom rounded-0" name="age_business" placeholder="{{ trans('app.age_business') }}" value="{{ old('age_business') }}">@if ($errors->has('age_business'))<span class="invalid-feedback d-block" role="alert"><strong>{{ $errors->first('age_business') }}</strong></span>@endif</div>')
				$('.append-fields').append('<div class="form-group" data-aos="fade-up"><div class="like-input bg-white rounded-0"><div class="d-inline-block">{{ trans('app.disability') }}</div><div class="d-inline-block float-right text-black">{{ trans('app.yes') }}<label class="switch mx-2"><input type="checkbox" name="disability" {{ old('disability') == 'on' ? 'checked' : '' }}><span class="slider round"></span></label>{{ trans('app.no') }}</div></div></div>')
			}
		});
	</script>
	@endpush