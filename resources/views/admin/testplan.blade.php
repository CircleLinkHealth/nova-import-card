@extends('partials.adminUI')

@section('content')
	<div class="container">
		<div class="row">

			<div class="col-md-12">
				<div class="col-sm-6">
					<h1>3.0 Site Map</h1>
				</div>
				<div class="col-sm-6">
					<div class="pull-right" style="margin:20px;">
						<a href="{{ URL::route('patients.dashboard', array()) }}" class="btn btn-info" style="margin-left:10px;"><i class="glyphicon glyphicon-eye-open"></i> Provider UI</a>
					</div>
				</div>
			</div>

			<div class="col-md-12">
				<div class="panel panel-default">
					<div class="panel-heading">Statistics</div>

					<div class="panel-body">
						<h2>TEST USERS:</h2>

						kevinprovider - kgalloprovider@circlelinkhealth.com / {{-- oyQaJz0x9XRM --}}<br /><br />
						kevincc - kgallocc@circlelinkhealth.com / {{-- oyQaJz0x9XRM --}}

						<h2>GENERAL:</h2>

						<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseLogin"><strong><i class="glyphicon glyphicon-thumbs-up"></i> Login</strong> - 100%</a></h4>
						<div id="collapseLogin" class="panel-collapse collapse">
							<br />Link: <a href="{{ URL::route('login', array()) }}">{{ URL::route('login', array()) }}</a><br /><br />
						</div>

						<br />
						<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseDashboard"><strong><i class="glyphicon glyphicon-thumbs-up"></i> Dashboard</strong> - 100%</a></h4>
						<div id="collapseDashboard" class="panel-collapse collapse">
							<br />Link: <a href="{{ URL::route('patients.dashboard', array()) }}">{{ URL::route('patients.dashboard', array()) }}</a><br /><br />
						</div>

						<br />
						<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapsePatientSearch"><strong><i class="glyphicon glyphicon-thumbs-up"></i> Patient Search</strong> - 70%</a></h4>
						<div id="collapsePatientSearch" class="panel-collapse collapse">
							<br />Link: <a href="{{ URL::route('patients.dashboard', array()) }}">{{ URL::route('patients.dashboard', array()) }}</a><br /><br />
						</div>

						<br />
						<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapsePatientList"><strong><i class="glyphicon glyphicon-thumbs-up"></i> Patient List</strong> - 100%</a></h4>
						<div id="collapsePatientList" class="panel-collapse collapse">
							<br />Link: <a href="{{ URL::route('patients.listing', array()) }}">{{ URL::route('patients.listing', array()) }}</a><br /><br />
						</div>

						<br />
						<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseAlerts"><strong><i class="glyphicon glyphicon-thumbs-up"></i> Alerts</strong> - 10%</a></h4>
						<div id="collapseAlerts" class="panel-collapse collapse">
							<br />link: <a href="{{ URL::route('patients.alerts', array()) }}">{{ URL::route('patients.alerts', array()) }}</a><br /><br />
						</div>



						<h2>PATIENT:</h2>

						<strong>add patient, draft->provider_approved - 100%</strong><br />
						- <a href="https://v3.careplanmanager.com/manage-patients/careplan/demographics">https://v3.careplanmanager.com/manage-patients/careplan/demographics</a><br /><br />

						<strong>add observations - 100%</strong><br />
						- <a href="https://v3.careplanmanager.com/manage-patients/393/input/observation">https://v3.careplanmanager.com/manage-patients/393/input/observation</a><br /><br />

						<strong>patient summary - 80% - needs labels</strong><br />
						- <a href="https://v3.careplanmanager.com/manage-patients/393/summary">https://v3.careplanmanager.com/manage-patients/393/summary</a><br /><br />

						<strong>progress report - 80? looks good</strong><br />
						- <a href="https://v3.careplanmanager.com/manage-patients/393/progress">https://v3.careplanmanager.com/manage-patients/393/progress</a><br /><br />

						<strong>notes - 100%?</strong><br />
						- <a href="https://v3.careplanmanager.com/manage-patients/393/notes">https://v3.careplanmanager.com/manage-patients/393/notes</a><br /><br />

						<strong>record activity - 100%?</strong><br />
						- <a href="https://v3.careplanmanager.com/manage-patients/393/activities/create">https://v3.careplanmanager.com/manage-patients/393/activities/create</a><br /><br />

						<strong>billing report - 100%?</strong><br />
						- <a href="https://v3.careplanmanager.com/manage-patients/393/billing">https://v3.careplanmanager.com/manage-patients/393/billing</a><br /><br />

						<strong>activity report - 100%?</strong><br />
						- <a href="https://v3.careplanmanager.com/manage-patients/393/activities">https://v3.careplanmanager.com/manage-patients/393/activities</a><br /><br />

						<strong>print care plan - 80%?</strong><br />
						- <a href="https://v3.careplanmanager.com/manage-patients/393/view-careplan">https://v3.careplanmanager.com/manage-patients/393/view-careplan</a><br /><br />


						</tbody>
						</table>
					</div>
				</div>
			</div>

		</div>
	</div>
@endsection
