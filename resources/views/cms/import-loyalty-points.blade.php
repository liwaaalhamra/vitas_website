@extends('voyager::master')


@section('content')

<div class="container-fluid">
	<h3 class="text-black" style="margin-bottom: 50px;"><b>Import Loyalty Points</b></h3>

	<div class="col-12">
		@if ($message = Session::get('success'))
		<div class="alert alert-success alert-dismissible d-block" role="alert">
			{!! $message !!}
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
		@endif
	</div>

	<div class="row">
		<div class="col-md-6 col-lg-4">
			<h4><b>Import File</b></h4>
			<div class="panel" style="padding: 15px;">
				<form method="post" action="{{ route('import.loyalty') }}" enctype="multipart/form-data">
					@csrf

					<div class="form-group">
						<p><b>File .csv</b></p>
						<input type="file" name="file" style="width: 100%">
					</div>

					<button type="submit" class="btn btn-info">Submit</button>
				</form>
			</div>
		</div>
	</div>

</div>

@endsection