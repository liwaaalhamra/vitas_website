@extends('voyager::master')

@section('content')

<div class="container-fluid">
	<h3 class="text-black" style="margin-bottom: 50px;"><b>Push Notifications</b></h3>

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

	<div class="col-12">
		@if ($message = Session::get('warning'))
		<div class="alert alert-warning alert-dismissible d-block" role="alert">
			{!! $message !!}
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
		@endif
	</div>

	<div class="col-12">
		@if ($message = Session::get('error_msg'))
		<div class="alert alert-error alert-dismissible d-block" role="alert">
			{!! $message !!}
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
		@endif
	</div>

	<div class="row">
		<div class="col-md-6 col-lg-4">
			<h4><b>All Users Notification</b></h4>
			<div class="panel" style="padding: 15px">
				<form method="post" action="{{ route('send.push') }}" enctype="multipart/form-data">
					@csrf

					<div class="form-group" style="margin-bottom: 25px;">
						<p><b>Language</b> <small>Check the below to send it as Arabic</small></p>
						<input type="checkbox" name="lang" value="ar">
					</div>

					<div class="form-group">
						<p><b>Title</b></p>
						<input class="form-control" name="title">

						@if ($errors->has('title'))
						<span class="invalid-feedback text-danger" role="alert">
							<strong>{{ $errors->first('title') }}</strong>
						</span>
						@endif
					</div>

					<div class="form-group">
						<p><b>Message</b></p>
						<textarea class="form-control" rows="7" name="message" style="resize: none;"></textarea>

						@if ($errors->has('message'))
						<span class="invalid-feedback text-danger" role="alert">
							<strong>{{ $errors->first('message') }}</strong>
						</span>
						@endif
					</div>

					<div class="form-group">
						<p><b>Image</b></p>
						<input type="file" name="image" style="width: 100%">
					</div>

					<button type="submit" class="btn btn-info">Send</button>
				</form>
			</div>
		</div>

		<div class="col-md-6 col-lg-4 col-lg-offset-4">
			<h4><b>Specific Notification</b></h4>
			<div class="panel" style="padding: 15px">
				<form method="post" action="{{ route('send.single.push') }}" enctype="multipart/form-data">
					@csrf

					<div class="form-group" style="margin-bottom: 25px;">
						<p><b>Language</b> <small>Check the below to send it as Arabic</small></p>
						<input type="checkbox" name="lang" value="ar">
					</div>

					<div class="form-group" style="margin-bottom: 25px;">
						<p><b>Users</b></p>
						<select class="form-control select2" name="users[]" multiple>
							@foreach($app_users as $row)
							<option value="{{ $row->users_id }}">{{ $row->user->employee_id . ' | ' . $row->user->customer_name }}</option>
							@endforeach
						</select>

						@if ($errors->has('users'))
						<span class="invalid-feedback text-danger" role="alert">
							<strong>{{ $errors->first('users') }}</strong>
						</span>
						@endif
					</div>

					<div class="form-group">
						<p><b>Title</b></p>
						<input class="form-control" name="title">

						@if ($errors->has('title'))
						<span class="invalid-feedback text-danger" role="alert">
							<strong>{{ $errors->first('title') }}</strong>
						</span>
						@endif
					</div>

					<div class="form-group">
						<p><b>Message</b></p>
						<textarea class="form-control" rows="7" name="message" style="resize: none;"></textarea>

						@if ($errors->has('message'))
						<span class="invalid-feedback text-danger" role="alert">
							<strong>{{ $errors->first('message') }}</strong>
						</span>
						@endif
					</div>

					<div class="form-group">
						<p><b>Image</b></p>
						<input type="file" name="image" style="width: 100%">
					</div>

					<button type="submit" class="btn btn-info">Send</button>
				</form>
			</div>
		</div>
	</div>
</div>

@endsection