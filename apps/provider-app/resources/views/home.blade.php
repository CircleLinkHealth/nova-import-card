@extends('partials.adminUI')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-12">
			<h2>Welcome, {{ $user->getFullName() }}</h2>
		</div>
		Default home dashboard
	</div>
</div>
@endsection
