@extends('layouts/main')
@section ('content')

{{-- <div class="store-wrapper text-center" style="height: 100vh; background-image: url('{{ Voyager::image($banner['image']) }}'); background-repeat: no-repeat; background-size: contain; background-position: right;"> --}}
<div class="store-wrapper text-center" style="height: 100vh;">
	<div class="d-table w-100 h-100">
		<div class="d-table-cell align-bottom">
			<div class="row">
				<div class="col-md-6 py-5 py-md-0">
					<div class="d-table w-100 h-100">
						<div class="d-table-cell align-middle">
							<img class="logo mb-4" src="{{ asset('assets/images/logo.png') }}" alt="Logo">
							<h5 class="text-black mb-4"><b>{{ trans('app.download_our_app') }}</b></h5>
							<a href="https://bit.ly/302AL6W" target="_blank">
								<img class="store contain" src="{{ asset('assets/images/icons/google-play-badge.png') }}">
							</a>
							<a href="https://apple.co/2O24cyw" target="_blank">
								<img class="store contain" src="{{ asset('assets/images/icons/app-store-badge.png') }}">
							</a>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<img class="contain" src="{{ Voyager::image($banner['image']) }}" alt="Download our application on play store and app store">
				</div>
			</div>
		</div>
	</div>
</div>

@endsection