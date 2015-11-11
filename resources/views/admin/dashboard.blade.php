@extends('app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-12">
			<h2>Welcome, {{ $user->fullName }}</h2>
		</div>

		<div class="col-md-6">
			<div class="panel panel-default">
				<div class="panel-heading">CCD Viewer</div>

				<div class="panel-body">
					@include('CCDViewer.create')
				</div>
			</div>
		</div>
		<div class="col-md-6">
			<div class="panel panel-default">
				<div class="panel-heading">Box</div>

				<div class="panel-body">
					Administrator Useful stuff box 1
				</div>
			</div>
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
								<td><a class="btn btn-primary btn pull-right" href="{{ URL::route('users.index', array()) }}"><i class="icon--home--white"></i> All Users</a></td>
							</tr>
							<tr>
								<td><strong>Total Administrators</strong></td>
								<td>{{ $stats['totalAdministrators'] }}</td>
								<td><a class="btn btn-primary btn pull-right" href="{{ URL::route('users.index', array('filterRole' => 'administrator')) }}"><i class="icon--home--white"></i> Administrators</a></td>
							</tr>
							<tr>
								<td><strong>Total Providers</strong></td>
								<td>{{ $stats['totalProviders'] }}</td>
								<td><a class="btn btn-primary btn pull-right" href="{{ URL::route('users.index', array('filterRole' => 'provider')) }}"><i class="icon--home--white"></i> Providers</a></td>
							</tr>
							<tr>
								<td><strong>Total Patients</strong></td>
								<td>{{ $stats['totalPatients'] }}</td>
								<td><a class="btn btn-primary btn pull-right" href="{{ URL::route('users.index', array('filterRole' => 'patient')) }}"><i class="icon--home--white"></i> Patients</a></td>
							</tr>

						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
