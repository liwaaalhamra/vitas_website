@extends('layouts/main')
@section ('content')

@include('components/banner')

<div class="py-5">
	<div class="container">
		<div class="row text-black">
			<div class="col-md-6 mb-5 mb-md-0 pr-lg-5 ar-pr-lg-3">
				<div class="h-100 bg-grey-2">
					<h3 class="text-center bg-white pb-4"><b>{{ $sections['our-contact-info']['title'] }}</b></h3>
					<div class="py-5 px-4 pr-lg-5 ar-pr-lg-4">
						<div class="mb-5">
							<h6 class="text-primary mb-2"><b>{{ trans('app.call-center-number') }}</b></h6>
							<a href="tel:{{ $contactFooter['call-center-number'] }}" class="d-block dir-ltr"><b>{{ $contactFooter['call-center-number'] }}</b></a>
						</div>
						<div class="mb-5">
							<h6 class="text-primary mb-2"><b>{{ trans('app.working-hours') }}:</b></h6>
							<p class="pre-line">{{ $contactFooter['working-hours'] }}</p>
						</div>
						<div class="mb-5">
							<h6 class="text-primary mb-2"><b>{{ trans('app.our-address') }}</b></h6>
							{{ $contactFooter['our-address'] }}
						</div>
						<div class="mb-5">
							<h6 class="text-primary mb-3"><b>{{ trans('app.follow-us') }}</b></h6>
							<div class="social-media svg-green">
								@if($contactFooter['facebook'])
								<a class="mr-4 mr-md-3 mr-lg-4" href="{{ $contactFooter['facebook'] }}" target="_blank">
									<svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"width="96.124px" height="96.123px" viewBox="0 0 96.124 96.123" style="enable-background:new 0 0 96.124 96.123;"xml:space="preserve"><path fill="#ffffff" d="M72.089,0.02L59.624,0C45.62,0,36.57,9.285,36.57,23.656v10.907H24.037c-1.083,0-1.96,0.878-1.96,1.961v15.803c0,1.083,0.878,1.96,1.96,1.96h12.533v39.876c0,1.083,0.877,1.96,1.96,1.96h16.352c1.083,0,1.96-0.878,1.96-1.96V54.287h14.654c1.083,0,1.96-0.877,1.96-1.96l0.006-15.803c0-0.52-0.207-1.018-0.574-1.386c-0.367-0.368-0.867-0.575-1.387-0.575H56.842v-9.246c0-4.444,1.059-6.7,6.848-6.7l8.397-0.003c1.082,0,1.959-0.878,1.959-1.96V1.98C74.046,0.899,73.17,0.022,72.089,0.02z"/></svg>
								</a>
								@endif
								@if($contactFooter['instagram'])
								<a class="mr-4 mr-md-3 mr-lg-4" href="{{ $contactFooter['instagram'] }}" target="_blank">
									<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"width="169.063px" height="169.063px" viewBox="0 0 169.063 169.063" style="enable-background:new 0 0 169.063 169.063;"xml:space="preserve"><path fill="#ffffff" d="M122.406,0H46.654C20.929,0,0,20.93,0,46.655v75.752c0,25.726,20.929,46.655,46.654,46.655h75.752c25.727,0,46.656-20.93,46.656-46.655V46.655C169.063,20.93,148.133,0,122.406,0z M154.063,122.407c0,17.455-14.201,31.655-31.656,31.655H46.654C29.2,154.063,15,139.862,15,122.407V46.655C15,29.201,29.2,15,46.654,15h75.752c17.455,0,31.656,14.201,31.656,31.655V122.407z"/><path fill="#ffffff" d="M84.531,40.97c-24.021,0-43.563,19.542-43.563,43.563c0,24.02,19.542,43.561,43.563,43.561s43.563-19.541,43.563-43.561C128.094,60.512,108.552,40.97,84.531,40.97z M84.531,113.093c-15.749,0-28.563-12.812-28.563-28.561c0-15.75,12.813-28.563,28.563-28.563s28.563,12.813,28.563,28.563C113.094,100.281,100.28,113.093,84.531,113.093z"/><path fill="#ffffff" d="M129.921,28.251c-2.89,0-5.729,1.17-7.77,3.22c-2.051,2.04-3.23,4.88-3.23,7.78c0,2.891,1.18,5.73,3.23,7.78c2.04,2.04,4.88,3.22,7.77,3.22c2.9,0,5.73-1.18,7.78-3.22c2.05-2.05,3.22-4.89,3.22-7.78c0-2.9-1.17-5.74-3.22-7.78C135.661,29.421,132.821,28.251,129.921,28.251z"/></svg>
								</a>
								@endif
								@if($contactFooter['youtube'])
								<a class="mr-4 mr-md-3 mr-lg-4" href="{{ $contactFooter['youtube'] }}" target="_blank">
									<svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve"><path fill="#ffffff" d="M490.24,113.92c-13.888-24.704-28.96-29.248-59.648-30.976C399.936,80.864,322.848,80,256.064,80c-66.912,0-144.032,0.864-174.656,2.912c-30.624,1.76-45.728,6.272-59.744,31.008C7.36,138.592,0,181.088,0,255.904C0,255.968,0,256,0,256c0,0.064,0,0.096,0,0.096v0.064c0,74.496,7.36,117.312,21.664,141.728c14.016,24.704,29.088,29.184,59.712,31.264C112.032,430.944,189.152,432,256.064,432c66.784,0,143.872-1.056,174.56-2.816c30.688-2.08,45.76-6.56,59.648-31.264C504.704,373.504,512,330.688,512,256.192c0,0,0-0.096,0-0.16c0,0,0-0.064,0-0.096C512,181.088,504.704,138.592,490.24,113.92z M192,352V160l160,96L192,352z"/></svg>
								</a>
								@endif
							</div>
						</div>
						<div class="pt-5 pr-lg-5 ar-pr-lg-0">
							<h6 class="mb-3"><b>{{ trans('app.faqs') }}</b></h6>
							<a href="{{ route('faqs', $locale) }}">
								<button class="btn btn-custom btn-filled w-100"><b>{{ trans('app.lets-go') }}</b></button>
							</a>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-6 mt-5 mt-md-0 pl-lg-5 ar-pl-lg-3">
				<div class="h-100 bg-grey-2">
					<h3 class="text-center bg-white pb-4"><b>{{ $sections['send-message']['title'] }}</b></h3>
					<div class="py-5 px-4 px-lg-5">
						<form class="formSerialize" method="post" action="{{ route('send-contact', $locale) }}">
							@csrf
							<input type="text" class="d-none" name="website">
							<input type="text" class="d-none" name="app" value="LDOTFKWNSTPVSY">
							<div class="mb-5 px-lg-4">
								<h6 class="mb-3"><b>{{ trans('app.personal-information') }}</b></h6>
								<div class="form-group mb-2">
									<input class="form-control form-custom input-height-45" name="first_name" placeholder="{{ trans('app.first_name') }}">
								</div>
								<div class="form-group mb-2">
									<input class="form-control form-custom input-height-45" name="last_name" placeholder="{{ trans('app.last_name') }}">
								</div>
							</div>
							<div class="px-lg-4">
								<h6 class="mb-3"><b>{{ trans('app.contact-information') }}</b></h6>
								<div class="mb-2">
									<div class="custom-select-wrapper">
										<input type="hidden">
										<input class="form-control form-custom input-height-45" name="country" placeholder="{{ trans('app.country') }}" readonly="">
										<ul>
											@foreach($countries as $country)
											<li data-value="{{ $country }}">{{ $country }}</li>
											@endforeach
										</ul>
										<svg width="12.674" height="7.815" viewBox="0 0 12.674 7.815">
											<path d="M60.129,0,58.65,1.479l4.858,4.858L58.65,11.195l1.479,1.479,6.337-6.337Z" transform="translate(12.674 -58.65) rotate(90)" fill="#fff"/>
										</svg>
									</div>
								</div>
								<div class="form-group mb-2">
									<input class="form-control form-custom input-height-45" name="place_of_residence" placeholder="{{ trans('app.place_of_Residence') }}">
								</div>
								<div class="form-group mb-2">
									<input class="form-control form-custom input-height-45" name="email_address" placeholder="{{ trans('app.email_address') }}">
								</div>
								<div class="form-group mb-2">
									<input class="form-control form-custom input-height-45" name="mobile_number" placeholder="{{ trans('app.mobile_number') }}">
								</div>
								<div class="form-group mb-2">
									<textarea class="form-control form-custom" rows="7" name="message" placeholder="{{ trans('app.message') }}"></textarea>
								</div>

								<div class="alert alert-danger" role="alert">
									<ul></ul>
								</div>
								<div class="alert alert-success" role="alert"></div>

								<button type="submit" class="btn btn-custom btn-filled w-100"><b data-text="{{ trans('app.submit') }}" data-loading-text="{{ trans('app.loading') }}">{{ trans('app.submit') }}</b></button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

@endsection