@extends('layouts/main')
@section ('content')

@include('components/banner')

<div class="py-5">
	<div class="container py-3">
		<h3 class="text-black text-center mb-5"><b>{{ $sections['future-vacancies']['title'] }}</b></h3>

		<div class="bg-grey px-3 py-5">
			<div class="row justify-content-center">
				<div class="col-md-10">
					<form class="formData" method="post" action="{{ route('apply-future-career', $locale) }}">
						@csrf
						<div class="alert alert-danger" role="alert">
							<ul></ul>
						</div>
						<div class="alert alert-success" role="alert"></div>
						<div class="row">
							<div class="col-md-6">
								<h5 class="text-black mb-3"><b>{{ trans('app.personal-information') }}</b></h5>
								<div class="mb-5">
									<div class="form-group mb-2">
										<input class="form-control form-custom" name="first_name" placeholder="{{ trans('app.first_name') }}">
									</div>
									<div class="form-group mb-2">
										<input class="form-control form-custom" name="last_name" placeholder="{{ trans('app.last_name') }}">
									</div>
									<div class="form-group mb-2">
										<div class="row">
											<div class="col-md-6 pr-md-1 mb-2 mb-md-0 ar-pr-md-3">
												<input class="form-control form-custom" name="date_of_birth" placeholder="{{ trans('app.dob') }}">
											</div>
											<div class="col-md-6 pl-md-1 ar-pl-md-3">
												<div class="custom-select-wrapper">
													<input type="hidden" name="gender">
													<input class="form-control form-custom" placeholder="{{ trans('app.gender') }}" readonly="">
													<ul>
														<li data-value="{{ trans('app.male') }}">{{ trans('app.male') }}</li>
														<li data-value="{{ trans('app.female') }}">{{ trans('app.female') }}</li>
													</ul>
													<svg width="12.674" height="7.815" viewBox="0 0 12.674 7.815">
														<path d="M60.129,0,58.65,1.479l4.858,4.858L58.65,11.195l1.479,1.479,6.337-6.337Z" transform="translate(12.674 -58.65) rotate(90)" fill="#fff"/>
													</svg>
												</div>
											</div>
										</div>
									</div>
									<div class="form-group mb-2">
										<div class="custom-select-wrapper">
											<input type="hidden" name="nationality">
											<input class="form-control form-custom" placeholder="{{ trans('app.nationality') }}" readonly="">
											<ul>
												@foreach($nationalities as $nationality)
												<li data-value="{{ $nationality }}">{{ $nationality }}</li>
												@endforeach
											</ul>
											<svg width="12.674" height="7.815" viewBox="0 0 12.674 7.815">
												<path d="M60.129,0,58.65,1.479l4.858,4.858L58.65,11.195l1.479,1.479,6.337-6.337Z" transform="translate(12.674 -58.65) rotate(90)" fill="#fff"/>
											</svg>
										</div>
									</div>
								</div>

								<h5 class="text-black mb-3"><b>{{ trans('app.contact-information') }}</b></h5>
								<div class="mb-5">
									<div class="form-group mb-2">
										<div class="custom-select-wrapper countries-list">
											<input type="hidden" name="country">
											<input class="form-control form-custom" placeholder="{{ trans('app.country') }}" readonly="">
											<ul>
												@foreach($countries as $country)
												<li data-value="{{ $country }}" @if($country == "Iraq" || $country == "العراق") selected @endif>{{ $country }}</li>
												@endforeach
											</ul>
											<svg width="12.674" height="7.815" viewBox="0 0 12.674 7.815">
												<path d="M60.129,0,58.65,1.479l4.858,4.858L58.65,11.195l1.479,1.479,6.337-6.337Z" transform="translate(12.674 -58.65) rotate(90)" fill="#fff"/>
											</svg>
										</div>
									</div>
									<div class="form-group mb-2 governorates-list d-none">
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
									<div class="form-group mb-2">
										<input class="form-control form-custom mobile_number_pattern dir-ltr text-ar-right" name="mobile_number" placeholder="{{ trans('app.mobile_number') }}">
									</div>
									<div class="form-group mb-2">
										<input class="form-control form-custom" name="email_address" placeholder="{{ trans('app.email_address') }}">
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<h5 class="text-black mb-3"><b>{{ trans('app.professional-information') }}</b></h5>
								<div class="form-group mb-2">
									<div class="custom-select-wrapper">
										<input type="hidden" name="position">
										<input class="form-control form-custom" placeholder="{{ trans('app.position_applying_for') }}" readonly="">
										<ul>
											@foreach($positions as $position)
											<li data-value="{{ $position['id'] }}">{{ $position['value'] }}</li>
											@endforeach
										</ul>
										<svg width="12.674" height="7.815" viewBox="0 0 12.674 7.815">
											<path d="M60.129,0,58.65,1.479l4.858,4.858L58.65,11.195l1.479,1.479,6.337-6.337Z" transform="translate(12.674 -58.65) rotate(90)" fill="#fff"/>
										</svg>
									</div>
								</div>
								<div class="form-group mb-2">
									<div class="custom-select-wrapper">
										<input type="hidden" name="level_of_education">
										<input class="form-control form-custom" placeholder="{{ trans('app.level_of_education') }}" readonly="">
										<ul>
											@foreach($levels as $level)
											<li data-value="{{ $level['id'] }}">{{ $level['value'] }}</li>
											@endforeach
											<li data-value="None">None</li>
										</ul>
										<svg width="12.674" height="7.815" viewBox="0 0 12.674 7.815">
											<path d="M60.129,0,58.65,1.479l4.858,4.858L58.65,11.195l1.479,1.479,6.337-6.337Z" transform="translate(12.674 -58.65) rotate(90)" fill="#fff"/>
										</svg>
									</div>
								</div>
								<div class="form-group mb-2">
									<input class="form-control form-custom" name="major" placeholder="{{ trans('app.major_of_specialization') }}">
								</div>
								<div class="form-group mb-2">
									<input class="form-control form-custom" name="last_job_title" placeholder="{{ trans('app.last_job_title') }}">
								</div>
								<div class="form-group mb-2">
									<div class="custom-select-wrapper">
										<input type="hidden" name="years_of_experience">
										<input class="form-control form-custom" placeholder="{{ trans('app.years_of_experience') }}" readonly="">
										<ul>
											@foreach($years as $year)
											<li data-value="{{ $year['id'] }}">{{ $year['value'] }}</li>
											@endforeach
										</ul>
										<svg width="12.674" height="7.815" viewBox="0 0 12.674 7.815">
											<path d="M60.129,0,58.65,1.479l4.858,4.858L58.65,11.195l1.479,1.479,6.337-6.337Z" transform="translate(12.674 -58.65) rotate(90)" fill="#fff"/>
										</svg>
									</div>
								</div>
								<div class="form-group mb-2">
									<input class="form-control form-custom" name="field_of_experience" placeholder="{{ trans('app.field_of_experience') }}">
								</div>
								<div class="form-group mb-2">
									<textarea class="form-control form-custom" name="message" placeholder="{{ trans('app.message') }}"></textarea>
								</div>
								<div class="form-group mb-2">
									<img class="icon" src="{{ asset('assets/images/icons/attach.png') }}">
									<input id="file" type="file" class="inputfile" name="cv" accept=".pdf,.doc,.docx">
									<label for="file" class="pl-5">{{ trans('app.attach_your_cv') }}</label>
								</div>
								<button type="submit" class="btn btn-custom btn-filled w-100"><b data-text="{{ trans('app.submit') }}" data-loading-text="{{ trans('app.loading') }}">{{ trans('app.submit') }}</b></button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

@endsection