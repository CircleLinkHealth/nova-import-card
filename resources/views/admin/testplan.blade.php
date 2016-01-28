@extends('partials.adminUI')

@section('content')
	<div class="container">
		<div class="row">

			<div class="col-md-12">
				<div class="col-sm-6">
					<h1>3.0 Provider Site Map</h1>
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

						<h2>MISC</h2>

						<br />
						<h5>Known Issues</h5>
						<ul>
							<li>Change "Print Care Plan" to "View Care Plan"</li>
							<li>Display patient name on top right of page if viewing</li>
							<li>"Edit Care Plan" link should go to page 1 care plan, not patient demographic page</li>
							<li>Toggle show/hide of child items when parent item checked/unchecked. </li>
						</ul>

						{{--
						<h2>TEST USERS:</h2>
						kevinprovider - kgalloprovider@circlelinkhealth.com / oyQaJz0x9XRM<br /><br />
						kevincc - kgallocc@circlelinkhealth.com / oyQaJz0x9XRM --}}

						<h2>GENERAL:</h2>

						<br />
						<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseLogin"><strong><i class="glyphicon glyphicon-list"></i> Login</strong> - 100%</a></h4>
						<div id="collapseLogin" class="panel-collapse collapse">
							<br />Link: <a href="{{ URL::route('login', array()) }}">{{ URL::route('login', array()) }}</a><br /><br />
							<h5>Known Issues</h5>
							<ul>
								<li>Add trademark symbol to CarePlanManager</li>
								<li>Responsive, right/left borders stick out the top on small resoluation</li>
								<li>Center submit button / all content</li>
								<li>Remove link from logo</li>
								<li>Blue login button</li>
								<li>Change "Login" to "Log In"</li>
								<li>Change "Forgot" to "Lost"</li>
								<li>Responsive - viewing on phone looks bad</li>
								<li>big* - switch to login with wp_users.user_login column vs. user_email</li>
							</ul>
						</div>

						<br />
						<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseDashboard"><strong><i class="glyphicon glyphicon-list"></i> Dashboard</strong> - 100%</a></h4>
						<div id="collapseDashboard" class="panel-collapse collapse">
							<br />Link: <a href="{{ URL::route('patients.dashboard', array()) }}">{{ URL::route('patients.dashboard', array()) }}</a><br /><br />
							<h5>Known Issues</h5>
							<ul>
								<li>Alert count # is omitted until alerts page is done</li>
								<li>Import CCD button is omitted</li>
							</ul>
						</div>

						<br />
						<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapsePatientSearch"><strong><i class="glyphicon glyphicon-list"></i> Patient Search</strong> - 70%</a></h4>
						<div id="collapsePatientSearch" class="panel-collapse collapse">
							<br />Link: <a href="{{ URL::route('patients.search', array()) }}">{{ URL::route('patients.search', array()) }}</a><br /><br />
						</div>

						<br />
						<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapsePatientList"><strong><i class="glyphicon glyphicon-list"></i> Patient List</strong> - 95%</a></h4>
						<div id="collapsePatientList" class="panel-collapse collapse">
							<br />Link: <a href="{{ URL::route('patients.listing', array()) }}">{{ URL::route('patients.listing', array()) }}</a><br /><br />
							<h5>Known Issues</h5>
							<ul>
								<li>Missing billing provider name</li>
							</ul>
						</div>

						<br />
						<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseAlerts"><strong><i class="glyphicon glyphicon-list"></i> Alerts</strong> - 15%</a></h4>
						<div id="collapseAlerts" class="panel-collapse collapse">
							<br />link: <a href="{{ URL::route('patients.alerts', array()) }}">{{ URL::route('patients.alerts', array()) }}</a><br /><br />
						</div>

						<br />
						<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseAddPatient"><strong><i class="glyphicon glyphicon-list"></i> Add Patient</strong> - 95%</a></h4>
						<div id="collapseAddPatient" class="panel-collapse collapse">
							<br />link: <a href="{{ URL::route('patients.demographics.show') }}">{{ URL::route('patients.demographics.show') }}</a><br /><br />
							<h5>Known Issues</h5>
							<ul>
								<li>Choose program - show only allowed programs, use disdplay_name</li>
								<li>Add contact days</li>
								<li>Populate locations based on program form.</li>
								<li>Make Not Required:
									email<br />
									street address<br />
									city<br />
									zip<br />
								</li>
								<li>Default birthdate to 01-01-1960</li>
							</ul>
						</div>









						@if(($patient))
							<h2>PATIENT:</h2>

							<strong>Links for patient: {{ $patient->fullNameWithID }}</strong>

							<br />
							<br />
							<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseCareTeam"><strong><i class="glyphicon glyphicon-list"></i> Care Team Setup</strong> - 100%</a></h4>
							<div id="collapseCareTeam" class="panel-collapse collapse">
								<br />link: <a href="{{ empty($patient) ? '' : URL::route('patient.careteam.show', array('patient' => $patient->ID)) }}">{{ empty($patient) ? '' : URL::route('patient.careteam.show', array('patient' => $patient->ID)) }}</a><br /><br />
								<h5>Known Issues</h5>
								<ul>
									<li>"Are you sure?" confirmation modal missing green submit button</li>
									<li>New patient did not get care_plan_id set</li>
								</ul>
							</div>


							<br />
							<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseCarePlan"><strong><i class="glyphicon glyphicon-list"></i> Care Plan</strong> - 100%</a></h4>
							<div id="collapseCarePlan" class="panel-collapse collapse">
								<br />link: <a href="{{ empty($patient) ? '' : URL::route('patient.careplan.show', array('patient' => $patient->ID, 'page' => 1)) }}">{{ empty($patient) ? '' : URL::route('patient.careplan.show', array('patient' => $patient->ID, 'page' => 1)) }}</a><br /><br />
								<h5>Known Issues</h5>
								<ul>
									<li>Remove "Care Plan" heading</li>
									<li>Hide child items for "Track Care Transitions" parent item</li>
									<li>Blue seperator bars between care sections</li>
									<li>Swap the order (reverse order) sections are shown</li>
									<li>Change "Instructions" to "Details"</li>
									<li>"Details" should be dark grey color</li>
									<li>"Details" modal textarea larger, button color green (same ui design as 2.8.8, blue heading ect)</li>
									<li>Submitting page 3 of careplan should redirect to print cacre plan</li>
								</ul>
							</div>


							<br />
							<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseAddObservation"><strong><i class="glyphicon glyphicon-list"></i> Add Observation</strong> - 95%</a></h4>
							<div id="collapseAddObservation" class="panel-collapse collapse">
								<br />link: <a href="{{ empty($patient) ? '' : URL::route('patient.observation.create', array('patient' => $patient->ID)) }}">{{ empty($patient) ? '' : URL::route('patient.observation.create', array('patient' => $patient->ID)) }}</a><br /><br />
								<h5>Known Issues</h5>
								<ul>
									<li>Date input on firefox formats weird</li>
								</ul>
							</div>


							<br />
							<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapsePatientSummary"><strong><i class="glyphicon glyphicon-list"></i> Patient Summary</strong> - 70%</a></h4>
							<div id="collapsePatientSummary" class="panel-collapse collapse">
								<br />link: <a href="{{ empty($patient) ? '' : URL::route('patient.summary', array('patient' => $patient->ID)) }}">{{ empty($patient) ? '' : URL::route('patient.summary', array('patient' => $patient->ID)) }}</a><br /><br />
								<h5>Known Issues</h5>
								<ul>
									<li>Observation labels are not correct</li>
									<li>Missing link to biometric charts + actual biometric charts page.</li>
								</ul>
							</div>
						@else
							No patient found to generate links from
						@endif


						<br />
						<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseProgressReport"><strong><i class="glyphicon glyphicon-list"></i> Progress Report</strong> - 80%</a></h4>
						<div id="collapseProgressReport" class="panel-collapse collapse">
							<br />link: <a href="{{ empty($patient) ? '' : URL::route('patient.reports.progress', array('patient' => $patient->ID)) }}">{{ empty($patient) ? '' : URL::route('patient.reports.progress', array('patient' => $patient->ID)) }}</a><br /><br />
							<h5>Known Issues</h5>
							<ul>
								<li>Charts not perfectly/cleanly aligned</li>
							</ul>
						</div>


						<br />
						<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseNotes"><strong><i class="glyphicon glyphicon-list"></i> Notes</strong> - 90%</a></h4>
						<div id="collapseNotes" class="panel-collapse collapse">
							<br />link: <a href="{{ empty($patient) ? '' : URL::route('patient.note.index', array('patient' => $patient->ID)) }}">{{ empty($patient) ? '' : URL::route('patient.note.index', array('patient' => $patient->ID)) }}</a><br /><br />
						</div>


						<br />
						<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseRecActivity"><strong><i class="glyphicon glyphicon-list"></i> Record Activity</strong> - 80%</a></h4>
						<div id="collapseRecActivity" class="panel-collapse collapse">
							<br />link: <a href="{{ empty($patient) ? '' : URL::route('patient.activity.create', array('patient' => $patient->ID)) }}">{{ empty($patient) ? '' : URL::route('patient.activity.create', array('patient' => $patient->ID)) }}</a><br /><br />
						</div>


						<br />
						<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseBillingReport"><strong><i class="glyphicon glyphicon-list"></i> Billing Report</strong> - 90%?</a></h4>
						<div id="collapseBillingReport" class="panel-collapse collapse">
							<br />link: <a href="{{ empty($patient) ? '' : URL::route('patient.reports.billing', array('patient' => $patient->ID)) }}">{{ empty($patient) ? '' : URL::route('patient.reports.billing', array('patient' => $patient->ID)) }}</a><br /><br />
						</div>


						<br />
						<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseActivityReport"><strong><i class="glyphicon glyphicon-list"></i> Activity Report</strong> - 90%?</a></h4>
						<div id="collapseActivityReport" class="panel-collapse collapse">
							<br />link: <a href="{{ empty($patient) ? '' : URL::route('patient.activity.providerUIIndex', array('patient' => $patient->ID)) }}">{{ empty($patient) ? '' : URL::route('patient.activity.providerUIIndex', array('patient' => $patient->ID)) }}</a><br /><br />
						</div>


						<br />
						<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapsePrintCarePlan"><strong><i class="glyphicon glyphicon-list"></i> Print Care Plan</strong> - 30%?</a></h4>
						<div id="collapsePrintCarePlan" class="panel-collapse collapse">
							<br />link: <a href="{{ empty($patient) ? '' : URL::route('patient.careplan.print', array('patient' => $patient->ID)) }}">{{ empty($patient) ? '' : URL::route('patient.careplan.print', array('patient' => $patient->ID)) }}</a><br /><br />
						</div>


						</tbody>
						</table>
					</div>
				</div>
			</div>

		</div>
	</div>
@endsection
