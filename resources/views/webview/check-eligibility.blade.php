<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
	<link rel="stylesheet" type="text/css" href="{{ asset('assets/libraries/bootstrap-4.3.1/bootstrap.min.css') }}">
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/main.css?v=2.912') }}"/>
	@if($locale == 'ar')
	<link rel="stylesheet" type="text/css" href="{{ asset('assets/fonts/NotoNaskhArabic/styles.css') }}"/>
	<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/main-ar.css?v=2.2') }}"/>
	@endif
</head>
<body>

	{{-- Check Eligibility Form --}}
	<div class="font-family-webview bg-grey py-5">
		<div class="container py-3">
			<div class="row justify-content-center text-center mb-3">
				<div class="col-md-6 text-black">
					<h6 class="letter-spacing-05"><b>{{ $sections['check-loan']['title'] }}</b></h6>
				</div>
			</div>
			<form method="post" action="{{ route('app.checkLoan', $locale) }}">
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

						<div class="text-center mb-3">{!! $sections['check-loan']['text'] !!}</div>

						<button type="submit" class="btn btn-custom btn-filled w-100 px-5" data-aos="fade-up"><b class="px-5" data-text="{{ trans('app.submit') }}" data-loading-text="{{ trans('app.loading') }}">{{ trans('app.submit') }}</b></button>
					</div>
				</div>
			{{-- </form> --}}
		</div>
	</div>

	<script type="text/javascript" src="{{ asset('assets/js/jquery-3.1.1.js') }}"></script>
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

		// Custom Select Box
		$('.custom-select-wrapper input[readonly], .custom-select-wrapper svg').on('click', function () {
			$(this).closest('.custom-select-wrapper').find('ul').toggleClass('d-block');
		});
		$('.custom-select-wrapper ul li').on('click', function () {
			var $this = $(this);
			$this.closest('.custom-select-wrapper').find('input[type="hidden"]').val($this.data('value'));
			$this.closest('.custom-select-wrapper').find('input[readonly]').val($this.text());
			$this.closest('.custom-select-wrapper').find('ul').removeClass('d-block');
		});
		$(document).bind('click', function (e) {
			if ($(e.target).closest('.custom-select-wrapper').length) return;
			$('.custom-select-wrapper ul').removeClass('d-block');
		});
		$('.custom-select-wrapper').each(function(){
			$(this).find('ul li').each(function(){
				var li = $(this).attr('selected');
				if (typeof li !== typeof undefined && li !== false) {
					$(this).trigger('click');
				}
			});
		});
	</script>
</body>
</html>