@extends('partials.adminUI')

@section('content')
<div class="container">
	<div class="row">

		<div class="col-md-12">
			<h1>Welcome, {{ $user->fullName }}</h1>
		</div>

		<div class="col-md-12">
			<div class="panel panel-default">
				<div class="panel-heading">Statistics</div>

				<div class="panel-body">
					<table class="table table-striped">
						<thead>
							<td></td>
							<td></td>
							<td></td>
						</thead>
						<tbody>
							<tr>
								<td><strong>Total Programs</strong></td>
								<td>{{ $stats['totalPrograms'] }}</td>
								<td><a class="btn btn-primary btn pull-right" href="{{ URL::route('admin.programs.index', array()) }}"><i class="icon--home--white"></i> Programs</a></td>
							</tr>
							<tr>
								<td><strong>Total Users</strong></td>
								<td>{{ $stats['totalUsers'] }}</td>
								<td><a class="btn btn-primary btn pull-right" href="{{ URL::route('admin.users.index', array()) }}"><i class="icon--home--white"></i> All Users</a></td>
							</tr>
							<tr>
								<td><strong>Total Administrators</strong></td>
								<td>{{ $stats['totalAdministrators'] }}</td>
								<td><a class="btn btn-primary btn pull-right" href="{{ URL::route('admin.users.index', array('filterRole' => 'administrator')) }}"><i class="icon--home--white"></i> Administrators</a></td>
							</tr>
							<tr>
								<td><strong>Total Providers</strong></td>
								<td>{{ $stats['totalProviders'] }}</td>
								<td><a class="btn btn-primary btn pull-right" href="{{ URL::route('admin.users.index', array('filterRole' => 'provider')) }}"><i class="icon--home--white"></i> Providers</a></td>
							</tr>
							<tr>
								<td><strong>Total Participant</strong></td>
								<td>{{ $stats['totalParticipants'] }}</td>
								<td><a class="btn btn-primary btn pull-right" href="{{ URL::route('admin.users.index', array('filterRole' => 'participant')) }}"><i class="icon--home--white"></i> Participant</a></td>
							</tr>

						</tbody>
					</table>
				</div>
			</div>
		</div>

		<div class="col-md-6">
			<div class="panel panel-default">
				<div class="panel-heading">BETA Color-coded CCD Viewer</div>

				<div class="panel-body">
					@include('CCDViewer.create')
				</div>
			</div>
		</div>
		<div class="col-md-6">
			<div class="panel panel-default">
				<div class="panel-heading">STABLE Raw CCD Viewer</div>

				<div class="panel-body">
					@include('CCDViewer.create-old-viewer')
				</div>
			</div>
		</div>

		<div class="col-md-6">
			<div class="panel panel-default">
				<div class="panel-heading">Impersonation</div>
				<div class="panel-body">
					<form action="{{ route('post.impersonate') }}" method="POST">
						<div class="form-group">
							<label for="email">Email address</label>
							<input class="form-control" type="email" name="email" placeholder="Impersonated user's email address" required>
						</div>
						<input class="btn btn-primary" type="submit" value="Impersonate">
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
