<?php
$codebaseUrl = 'https://circlelink-health2.codebasehq.com/projects/cpm/tickets/';
?>

@extends('partials.adminUI')

@section('content')
	<style>
		.panel-collapse {
			border-bottom:1px solid #fff;
		}

		.complete {
			text-decoration: line-through;
		}
	</style>
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
							<li class="complete">Change "Print Care Plan" to "View Care Plan"</li>
							<li class="complete">"Edit Care Plan" link should go to page 1 care plan, not patient demographic page</li>
						</ul>

						{{--
						<h2>TEST USERS:</h2>
						kevinprovider - kgalloprovider@circlelinkhealth.com / oyQaJz0x9XRM<br /><br />
						kevincc - kgallocc@circlelinkhealth.com / oyQaJz0x9XRM --}}

						<br /><br /><br />
						<h2>GENERAL:</h2>

						<br />
						<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseLogin"><strong><i class="glyphicon glyphicon-list"></i> Login</strong> - 100%</a></h4>
						<div id="collapseLogin" class="panel-collapse collapse in">
							<br />Link: <a href="{{ URL::route('login', array()) }}" target="_blank">{{ URL::route('login', array()) }}</a><br /><br />
							<h5>Known Issues</h5>
							<ul>
								<li class="complete">Add trademark symbol to CarePlanManager <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/98">#98</a></li>
								<li class="complete">Responsive, right/left borders stick out the top on small resoluation <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/98">#98</a></li>
								<li class="complete">Center submit button / all content <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/98">#98</a></li>
								<li class="complete">Remove link from logo <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/98">#98</a></li>
								<li class="complete">Blue login button <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/98">#98</a></li>
								<li class="complete">Change "Login" to "Log In" <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/98">#98</a></li>
								<li class="complete">Change "Forgot" to "Lost" <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/98">#98</a></li>
								<li class="complete">Responsive - viewing on phone looks bad <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/98">#98</a></li>
								<li class="complete">big* - switch to login with wp_users.user_login column vs. user_email <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/98">#98</a></li>
							</ul>
						</div>

						<br />
						<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseDashboard"><strong><i class="glyphicon glyphicon-list"></i> Dashboard</strong> - 80%</a></h4>
						<div id="collapseDashboard" class="panel-collapse collapse in">
							<br />Link: <a href="{{ URL::route('patients.dashboard', array()) }}" target="_blank">{{ URL::route('patients.dashboard', array()) }}</a><br /><br />
							<h5>Known Issues</h5>
							<ul>
								<li>Alert count # is omitted until alerts page is done</li>
								<li>Import CCD button is omitted <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/69">#69</a></li>
							</ul>
						</div>

						<br />
						<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapsePatientSearch"><strong><i class="glyphicon glyphicon-list"></i> Patient Search</strong> - 70%</a></h4>
						<div id="collapsePatientSearch" class="panel-collapse collapse in">
							<br />Link: <a href="{{ URL::route('patients.search', array()) }}" target="_blank">{{ URL::route('patients.search', array()) }}</a><br /><br />
							<h5>Known Issues</h5>
							<ul>
								<li>Not searching on all columns, only searching for name</li>
								<li>CSS formatting of select options doesnt look nice</li>
							</ul>
						</div>

						<br />
						<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapsePatientList"><strong><i class="glyphicon glyphicon-list"></i> Patient List</strong> - 95%</a></h4>
						<div id="collapsePatientList" class="panel-collapse collapse in">
							<br />Link: <a href="{{ URL::route('patients.listing', array()) }}" target="_blank">{{ URL::route('patients.listing', array()) }}</a><br /><br />
							<h5>Known Issues</h5>
							<ul>
								<li class="complete">Missing billing provider name <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/107">#107</a></li>
							</ul>
						</div>

						<br />
						<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapsePatientPrintList"><strong><i class="glyphicon glyphicon-list"></i> Patient Care Plan Print List</strong> - 15%</a></h4>
						<div id="collapsePatientPrintList" class="panel-collapse collapse in">
							<br />Link: <a href="{{ URL::route('patients.listing', array()) }}" target="_blank">{{ URL::route('patients.listing', array()) }}</a><br /><br />
							<h5>Known Issues</h5>
							<ul>
								<li class="complete">Build Page <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/113">#113</a></li>
							</ul>
						</div>

						<br />
						<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseAlerts"><strong><i class="glyphicon glyphicon-list"></i> Alerts</strong> - 15%</a></h4>
						<div id="collapseAlerts" class="panel-collapse collapse in">
							<br />link: <a href="{{ URL::route('patients.alerts', array()) }}" target="_blank">{{ URL::route('patients.alerts', array()) }}</a><br /><br />
							<h4>Not Built <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/16">#16</a></h4>
						</div>

						<br />
						<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseAddPatient"><strong><i class="glyphicon glyphicon-list"></i> Add Patient</strong> - 85%</a></h4>
						<div id="collapseAddPatient" class="panel-collapse collapse in">
							<br />link: <a href="{{ URL::route('patients.demographics.show') }}" target="_blank">{{ URL::route('patients.demographics.show') }}</a><br /><br />
							<h5>Known Issues</h5>
							<ul>
								<li>Choose program - show only allowed programs, use disdplay_name <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/99">#99</a></li>
								<li>Add contact days <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/99">#99</a></li>
								<li>Populate locations based on program form. <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/99">#99</a></li>
								<li>Make Not Required: <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/99">#99</a> <br />
									email<br />
									street address<br />
									city<br />
									zip<br />
								</li>
								<li>Default birthdate to 01-01-1960 <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/99">#99</a></li>
								<li class="complete">New patient did not get care_plan_id set</li>
							</ul>
						</div>









						@if(($patient))
							<br /><br /><br />
							<h2>PATIENT:</h2>

							<strong>Links for patient: {{ $patient->fullNameWithID }}</strong>

							<br />
							<br />
							<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseCareTeam"><strong><i class="glyphicon glyphicon-list"></i> Care Team Setup</strong> - 95%</a></h4>
							<div id="collapseCareTeam" class="panel-collapse collapse in">
								<br />link: <a href="{{ empty($patient) ? '' : URL::route('patient.careteam.show', array('patient' => $patient->ID)) }}" target="_blank">{{ empty($patient) ? '' : URL::route('patient.careteam.show', array('patient' => $patient->ID)) }}</a><br /><br />
								<h5>Known Issues</h5>
								<ul>
									<li class="complete">"Are you sure?" confirmation modal missing green submit button <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/102">#102</a></li>
									<li class="complete">JS issue, navigation buttons disable when js click events are fired. <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/105">#105</a></li>
								</ul>
							</div>


							<br />
							<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseCarePlan"><strong><i class="glyphicon glyphicon-list"></i> Care Plan</strong> - 85%</a></h4>
							<div id="collapseCarePlan" class="panel-collapse collapse in">
								<br />link: <a href="{{ empty($patient) ? '' : URL::route('patient.careplan.show', array('patient' => $patient->ID, 'page' => 1)) }}" target="_blank">{{ empty($patient) ? '' : URL::route('patient.careplan.show', array('patient' => $patient->ID, 'page' => 1)) }}</a><br /><br />
								<h5>Known Issues</h5>
								<ul>
									<li>Remove "Care Plan" heading <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/106">#106</a></li>
									<li>Hide child items for "Track Care Transitions" parent item <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/106">#106</a></li>
									<li>Blue seperator bars between care sections <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/106">#106</a></li>
									<li>Swap the order (reverse order) sections are shown <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/106">#106</a></li>
									<li>Submitting page 3 of careplan should redirect to print cacre plan <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/106">#106</a></li>
									<li>Change "Instructions" to "Details" <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/105">#105</a></li>
									<li>"Details" should be dark grey color <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/105">#105</a></li>
									<li>"Details" modal textarea larger, button color green (same ui design as 2.8.8, blue heading ect) <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/105">#105</a></li>
									<li>Toggle show/hide of child items when parent item checked/unchecked. <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/105">#105</a></li>
								</ul>
							</div>


							<br />
							<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseAddObservation"><strong><i class="glyphicon glyphicon-list"></i> Add Observation</strong> - 95%</a></h4>
							<div id="collapseAddObservation" class="panel-collapse collapse in">
								<br />link: <a href="{{ empty($patient) ? '' : URL::route('patient.observation.create', array('patient' => $patient->ID)) }}" target="_blank">{{ empty($patient) ? '' : URL::route('patient.observation.create', array('patient' => $patient->ID)) }}</a><br /><br />
								<h5>Known Issues</h5>
								<ul>
									<li>Date input on firefox formats weird</li>
								</ul>
							</div>


							<br />
							<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapsePatientSummary"><strong><i class="glyphicon glyphicon-list"></i> Patient Summary</strong> - 70%</a></h4>
							<div id="collapsePatientSummary" class="panel-collapse collapse in">
								<br />link: <a href="{{ empty($patient) ? '' : URL::route('patient.summary', array('patient' => $patient->ID)) }}" target="_blank">{{ empty($patient) ? '' : URL::route('patient.summary', array('patient' => $patient->ID)) }}</a><br /><br />
								<h5>Known Issues</h5>
								<ul>
									<li>Observation labels are not correct <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/82">#82</a></li>
									<li>Missing link to biometric charts + actual biometric charts page. <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/108">#108</a></li>
								</ul>
							</div>
						@else
							No patient found to generate links from
						@endif


						<br />
						<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseProgressReport"><strong><i class="glyphicon glyphicon-list"></i> Progress Report</strong> - 80%</a></h4>
						<div id="collapseProgressReport" class="panel-collapse collapse in">
							<br />link: <a href="{{ empty($patient) ? '' : URL::route('patient.reports.progress', array('patient' => $patient->ID)) }}" target="_blank">{{ empty($patient) ? '' : URL::route('patient.reports.progress', array('patient' => $patient->ID)) }}</a><br /><br />
							<h5>Known Issues</h5>
							<ul>
								<li>Charts not perfectly/cleanly aligned</li>
							</ul>
						</div>


						<br />
						<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseNotes"><strong><i class="glyphicon glyphicon-list"></i> Notes</strong> - 90%</a></h4>
						<div id="collapseNotes" class="panel-collapse collapse in">
							<br />link: <a href="{{ empty($patient) ? '' : URL::route('patient.note.index', array('patient' => $patient->ID)) }}" target="_blank">{{ empty($patient) ? '' : URL::route('patient.note.index', array('patient' => $patient->ID)) }}</a><br /><br />
							<h5>Known Issues</h5>
							<ul>
								<li>Submit button should be centered underneath centered paragraph text <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/103">#103</a></li>
								<li>Notes listing is only showing 1 note per day per type <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/111">#111</a></li>
								<li>Log outgoing email messages in activity meta <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/112">#112</a></li>
							</ul>
						</div>


						<br />
						<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseRecActivity"><strong><i class="glyphicon glyphicon-list"></i> Record Activity</strong> - 80%</a></h4>
						<div id="collapseRecActivity" class="panel-collapse collapse in">
							<br />link: <a href="{{ empty($patient) ? '' : URL::route('patient.activity.create', array('patient' => $patient->ID)) }}" target="_blank">{{ empty($patient) ? '' : URL::route('patient.activity.create', array('patient' => $patient->ID)) }}</a><br /><br />
						</div>


						<br />
						<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseBillingReport"><strong><i class="glyphicon glyphicon-list"></i> Billing Report</strong> - 90%</a></h4>
						<div id="collapseBillingReport" class="panel-collapse collapse in">
							<br />link: <a href="{{ empty($patient) ? '' : URL::route('patient.reports.billing', array('patient' => $patient->ID)) }}" target="_blank">{{ empty($patient) ? '' : URL::route('patient.reports.billing', array('patient' => $patient->ID)) }}</a><br /><br />
						</div>


						<br />
						<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseActivityReport"><strong><i class="glyphicon glyphicon-list"></i> Activity Report</strong> - 80%</a></h4>
						<div id="collapseActivityReport" class="panel-collapse collapse in">
							<br />link: <a href="{{ empty($patient) ? '' : URL::route('patient.activity.providerUIIndex', array('patient' => $patient->ID)) }}" target="_blank">{{ empty($patient) ? '' : URL::route('patient.activity.providerUIIndex', array('patient' => $patient->ID)) }}</a><br /><br />
							<h5>Known Issues</h5>
							<ul>
								<li>Data doesnt match 2.8 <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/104">#104</a></li>
								<li>right margin/padding on top right "Go" button <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/104">#104</a></li>
								<li>Choosing 'Year' doesnt hold, resets to 2016 <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/104">#104</a></li>
							</ul>
						</div>


						<br />
						<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapsePrintCarePlan"><strong><i class="glyphicon glyphicon-list"></i> Print Care Plan</strong> - 30%</a></h4>
						<div id="collapsePrintCarePlan" class="panel-collapse collapse in">
							<br />link: <a href="{{ empty($patient) ? '' : URL::route('patient.careplan.print', array('patient' => $patient->ID)) }}" target="_blank">{{ empty($patient) ? '' : URL::route('patient.careplan.print', array('patient' => $patient->ID)) }}</a><br /><br />
							<h5>Known Issues</h5>
							<ul>
								<li>Not ready for testings</li>
							</ul>
						</div>


						</tbody>
						</table>
					</div>
				</div>
			</div>

		</div>
	</div>
@endsection
