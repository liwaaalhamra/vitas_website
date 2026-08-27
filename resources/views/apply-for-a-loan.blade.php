@extends('layouts/main')

@push('head')
<!-- Event snippet for Apply for a loan 4 conversion page In your html page,
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

@include('components/banner')

<div class="py-5">
	<div class="container py-3">
		<h3 class="text-black text-center mb-5"><b>{{ $sections['apply-for-a-loan']['title'] }}</b></h3>

		<div class="bg-grey px-3 py-5">
			<div class="row justify-content-center">
				<div class="col-md-10">
					<h5 class="text-black mb-4"><b>{{ $sections['apply-for-a-loan-notice']['title'] }}</b></h5>
					<div class="dir-ltr mb-4">
						{!! $sections['apply-for-a-loan-notice']['text'] !!}
					</div>
					<form class="formSerialize" method="post" action="{{ route('apply-loan', $locale) }}">
						@csrf
						<div class="alert alert-danger" role="alert">
							<ul></ul>
						</div>
						<div class="alert alert-success" role="alert"></div>
						<div class="row">
							<div class="col-md-6">
								<div class="form-group mb-2">
									<div class="custom-select-wrapper">
										<input type="hidden" name="loan_type" value="{{ ($selectedProduct) ? $selectedProduct['id'] : "" }}">
										<input class="form-control form-custom" placeholder="{{ trans('app.choose_a_loan') }}" value="{{ ($selectedProduct) ? ucfirst(strtolower($selectedProduct['title'])) : "" }}" readonly="">
										<ul>
											@foreach($products as $product)
											<li data-value="{{ $product['id'] }}">{{ ucfirst(strtolower($product['title'])) }}</li>
											@endforeach
										</ul>
										<svg width="12.674" height="7.815" viewBox="0 0 12.674 7.815">
											<path d="M60.129,0,58.65,1.479l4.858,4.858L58.65,11.195l1.479,1.479,6.337-6.337Z" transform="translate(12.674 -58.65) rotate(90)" fill="#fff"/>
										</svg>
									</div>
								</div>

								<div class="form-group mb-2">
									<div class="like-input bg-white">
										<div class="d-inline-block">
											{{ trans('app.business_owner') }}
										</div>
										<div class="d-inline-block float-right text-black">
											{{ trans('app.yes') }}
											<label class="switch mx-2">
												<input type="checkbox" name="business_owner">
												<span class="slider round"></span>
											</label>
											{{ trans('app.no') }}
										</div>
									</div>
								</div>

								<div class="form-group mb-2">
									<div class="like-input bg-white rounded-0">
										<div class="d-inline-block">
											{{ trans('app.employee') }}
										</div>
										<div class="d-inline-block float-right text-black">
											{{ trans('app.yes') }}
											<label class="switch mx-2">
												<input type="checkbox" name="employee">
												<span class="slider round"></span>
											</label>
											{{ trans('app.no') }}
										</div>
									</div>
								</div>

								<div class="form-group mb-2">
									<input class="form-control form-custom" name="full_name" placeholder="{{ trans('app.full_name') }}">
								</div>
								<div class="form-group">
									<div class="custom-select-wrapper">
										<input class="form-control form-custom" name="age" placeholder="{{ trans('app.age') }}">
									</div>
								</div>
								<a href="{{ route('home', [$locale, "#check_eligibility"]) }}">
									<button type="button" class="btn btn-custom btn-filled w-100 text-uppercase"><b>{{ trans('app.check_eligibility') }}</b></button>
								</a>
							</div>
							<div class="col-md-6">
								<div class="form-group mb-2">
									<input class="form-control form-custom" name="phone_number" placeholder="{{ trans('app.phone') }}">
								</div>
								<div class="form-group mb-2">
									<input class="form-control form-custom" name="email_address" placeholder="{{ trans('app.email_address') }}">
								</div>
								<div class="form-group mb-2">
									<div class="custom-select-wrapper">
										<input type="hidden" name="governorate">
										<input class="form-control form-custom" placeholder="{{ trans('app.governorate') }}" readonly="">
										<ul>
											@foreach($governorates as $governorate)
											<li data-value="{{ $governorate['id'] }}">{{ $governorate['value'] }}</li>
											@endforeach
										</ul>
										<svg width="12.674" height="7.815" viewBox="0 0 12.674 7.815">
											<path d="M60.129,0,58.65,1.479l4.858,4.858L58.65,11.195l1.479,1.479,6.337-6.337Z" transform="translate(12.674 -58.65) rotate(90)" fill="#fff"/>
										</svg>
									</div>
								</div>
								<div class="form-group">
									<input class="form-control form-custom" name="address" placeholder="{{ trans('app.address_city_providence') }}">
								</div>
								<button type="submit" class="btn btn-custom btn-filled w-100"><b data-text="{{ trans('app.submit') }}" data-loading-text="{{ trans('app.loading') }}">{{ trans('app.apply') }}</b></button>
								{{-- <button onclick="return gtag_report_conversion('{{ route('apply-loan', $locale) }}')" type="submit" class="btn btn-custom btn-filled w-100"><b data-text="{{ trans('app.submit') }}" data-loading-text="{{ trans('app.loading') }}">{{ trans('app.apply') }}</b></button> --}}
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

@endsection