@extends('app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-12">
			<h2>Welcome, {{ $userMeta['first_name'] . ' ' . $userMeta['last_name'] }}</h2>
		</div>
		<div class="col-md-6">
				<div class="panel panel-default">
					<div class="panel-heading">Msg Dashboard</div>

					<div class="panel-body">

					</div>
				</div>
		</div>
		<div class="col-md-6">
				<div class="panel panel-default">
					<div class="panel-heading">User Dashboard</div>

					<div class="panel-body">

					</div>
				</div>
		</div>
	</div>
</div>
@endsection
