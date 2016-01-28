@extends('partials.adminUI')

@section('content')
	<div class="container">
		<div class="row">

			<div class="col-md-12">
				<div class="col-sm-6">
					<h1>3.0 Demo</h1>
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
						<h2>USERS:</h2>

						kevinprovider - kgalloprovider@circlelinkhealth.com / oyQaJz0x9XRM<br /><br />
						kevincc - kgallocc@circlelinkhealth.com / oyQaJz0x9XRM

						<h2>GENERAL:</h2>

						<strong>login as provider user - 100%</strong><br />
						- <a href="https://v3.careplanmanager.com/login">https://v3.careplanmanager.com/login</a><br /><br />

						<strong>show dashboard - 100% (no alerts count)</strong><br />
						- <a href="https://v3.careplanmanager.com/manage-patients/dashboard">https://v3.careplanmanager.com/manage-patients/dashboard</a><br /><br />

						<strong>show patient search ajax - ?TEST</strong><br />
						- <a href="https://v3.careplanmanager.com/manage-patients/search">https://v3.careplanmanager.com/manage-patients/search</a><br /><br />

						<strong>patient listing - 100%</strong><br />
						- <a href="https://v3.careplanmanager.com/manage-patients/listing">https://v3.careplanmanager.com/manage-patients/listing</a><br /><br />

						<strong>alerts - 5%</strong><br />
						- <a href="http://local-api.cpm/manage-patients/alerts">http://local-api.cpm/manage-patients/alerts</a><br /><br />


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
