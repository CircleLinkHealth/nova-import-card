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


						<h2>2/11 3.0 Walkthrough Raphs Notes</h2>

						<ul><li class="complete">Add conditions to user header <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/117">#117</a></li>
						<li>select patient blue box should be shorter
						</li><li class="complete">Patient Overview - too wide when zoomed out
						</li><li class="complete">Patient Overview - biometrics labels have underscore
						</li><li class="complete">Add patient - screen too wide, needs same scale as prior version
						</li><li>Add patient - location field eventually needs to be visible right away, populated after program selected <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/155">#155</a>
						</li><li>Add patient - can’t get to step 3 of care plan setup after entering care team <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/149">#149</a>
						</li><li>Edit care plan page 3 - instructions box “instructions” needs to be “details"
						</li><li class="complete">Edit care plan page 5 - swap order: have additional instructions be second/ at bottom
						</li><li>Add Note - add check boxes visible after "phone session" checked<br>"Patient in Hospital/ER" (if selected auto-send note to providers getting alerts when note saved)<br>"Patient Reached" <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/152">#152</a>
						</li><li>Under 20 minutes report - eliminate blank white space on right side
						</li><li class="complete">Under 20 minutes report - time in patient header should link to "patient activity report"
						</li><li class="complete">Patient Activity Report - missing 5mins vs. time summary/check time calcs <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/153">#153</a></li>
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
								<li class="complete">big* - switch to login with wp_users.username column vs. email <a
                                            class="btn-primary btn-xs" target="_blank"
                                            href="{{ $codebaseUrl }}/98">#98</a></li>
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
								<li>Build Page <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/113">#113</a></li>
							</ul>
						</div>

						<br />
						<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseAlerts"><strong><i class="glyphicon glyphicon-list"></i> Alerts</strong> - ON HOLD</a></h4>
						<div id="collapseAlerts" class="panel-collapse collapse in">
							<br />link: <a href="{{ URL::route('patients.alerts', array()) }}" target="_blank">{{ URL::route('patients.alerts', array()) }}</a><br /><br />
							<h5>Known Issues</h5>
							<ul>
								<li>Build Page - ON HOLD <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/16">#16</a></li>
							</ul>
						</div>

						<br />
						<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseAddPatient"><strong><i class="glyphicon glyphicon-list"></i> Add Patient</strong> - 95%</a></h4>
						<div id="collapseAddPatient" class="panel-collapse collapse in">
							<br />link: <a href="{{ URL::route('patients.demographics.show') }}" target="_blank">{{ URL::route('patients.demographics.show') }}</a><br /><br />
							<h5>Known Issues</h5>
							<ul>
								<li class="complete">Choose program - show only allowed programs, use display_name <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/99">#99</a></li>
								<li class="complete">Add contact days <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/99">#99</a></li>
								<li class="complete">Make Not Required: <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/99">#99</a> <br />
									email<br />
									street address<br />
									city<br />
									zip<br />
								</li>
								<li class="complete">Default birthdate to 01-01-1960 <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/99">#99</a></li>
								<li class="complete">New patient did not get care_plan_id set</li>
								<li class="complete">Change 'SMS' to 'Care Center'.</li>
								<li class="complete">Add choose careplan</li>
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
                                <br/>link: <a
                                        href="{{ empty($patient) ? '' : URL::route('patient.careteam.show', array('patient' => $patient->id)) }}"
                                        target="_blank">{{ empty($patient) ? '' : URL::route('patient.careteam.show', array('patient' => $patient->id)) }}</a><br/><br/>
								<h5>Known Issues</h5>
								<ul>
									<li class="complete">"Are you sure?" confirmation modal missing green submit button <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/102">#102</a></li>
									<li class="complete">JS issue, navigation buttons disable when js click events are fired. <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/102">#102</a></li>
								</ul>
							</div>


							<br />
							<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseCarePlan"><strong><i class="glyphicon glyphicon-list"></i> Care Plan</strong> - 95%</a></h4>
							<div id="collapseCarePlan" class="panel-collapse collapse in">
                                <br/>link: <a
                                        href="{{ empty($patient) ? '' : URL::route('patient.careplan.show', array('patient' => $patient->id, 'page' => 1)) }}"
                                        target="_blank">{{ empty($patient) ? '' : URL::route('patient.careplan.show', array('patient' => $patient->id, 'page' => 1)) }}</a><br/><br/>
								<h5>Known Issues</h5>
								<ul>
									<li class="complete">Remove "Care Plan" heading <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/106">#106</a></li>
									<li>Hide child items for "Track Care Transitions" parent item</li>
									<li class="complete">Blue seperator bars between care sections <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/106">#106</a></li>
									<li class="complete">Swap the order (reverse order) sections are shown <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/106">#106</a></li>
									<li class="complete">Submitting page 3 of careplan should redirect to print care plan <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/106">#106</a></li>
									<li class="complete">Change "Instructions" to "Details" <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/105">#105</a></li>
									<li class="complete">"Details" should be dark grey color <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/105">#105</a></li>
									<li class="complete">"Details" modal textarea larger, button color green (same ui design as 2.8.8, blue heading ect) <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/105">#105</a></li>
									<li class="complete">Toggle show/hide of child items when parent item checked/unchecked. <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/105">#105</a></li>
								</ul>
							</div>


							<br />
							<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseAddObservation"><strong><i class="glyphicon glyphicon-list"></i> Add Observation</strong> - 95%</a></h4>
							<div id="collapseAddObservation" class="panel-collapse collapse in">
                                <br/>link: <a
                                        href="{{ empty($patient) ? '' : URL::route('patient.observation.create', array('patient' => $patient->id)) }}"
                                        target="_blank">{{ empty($patient) ? '' : URL::route('patient.observation.create', array('patient' => $patient->id)) }}</a><br/><br/>
								<h5>Known Issues</h5>
								<ul>
									<li class="complete">Date input on firefox formats weird, switch to timepicker <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/146">#146</a></li>
								</ul>
							</div>


							<br />
							<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapsePatientSummary"><strong><i class="glyphicon glyphicon-list"></i> Patient Summary</strong> - 90%</a></h4>
							<div id="collapsePatientSummary" class="panel-collapse collapse in">
                                <br/>link: <a
                                        href="{{ empty($patient) ? '' : URL::route('patient.summary', array('patient' => $patient->id)) }}"
                                        target="_blank">{{ empty($patient) ? '' : URL::route('patient.summary', array('patient' => $patient->id)) }}</a><br/><br/>
								<h5>Known Issues</h5>
								<ul>
									<li class="complete">Observation labels are not correct <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/82">#82</a></li>
									<li class="complete">Missing link to biometric charts + actual biometric charts page. <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/108">#108</a></li>
									<li class="complete">Lifestyle observations not displaying properly. <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/114">#114</a></li>
									<li>Missing print and export icons / functionality <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/154">#154</a></li>
								</ul>
							</div>
						@else
							No patient found to generate links from
						@endif


						<br />
						<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseProgressReport"><strong><i class="glyphicon glyphicon-list"></i> Progress Report</strong> - 80%</a></h4>
						<div id="collapseProgressReport" class="panel-collapse collapse in">
                            <br/>link: <a
                                    href="{{ empty($patient) ? '' : URL::route('patient.reports.progress', array('patient' => $patient->id)) }}"
                                    target="_blank">{{ empty($patient) ? '' : URL::route('patient.reports.progress', array('patient' => $patient->id)) }}</a><br/><br/>
							<h5>Known Issues</h5>
							<ul>
								<li>Charts not perfectly/cleanly aligned</li>
							</ul>
						</div>


						<br />
						<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseNotes"><strong><i class="glyphicon glyphicon-list"></i> Notes</strong> - 90%</a></h4>
						<div id="collapseNotes" class="panel-collapse collapse in">
                            <br/>link: <a
                                    href="{{ empty($patient) ? '' : URL::route('patient.note.index', array('patient' => $patient->id)) }}"
                                    target="_blank">{{ empty($patient) ? '' : URL::route('patient.note.index', array('patient' => $patient->id)) }}</a><br/><br/>
							<h5>Known Issues</h5>
							<ul>
								<li class="complete">Submit button should be centered underneath centered paragraph text <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/103">#103</a></li>
								<li class="complete">Notes listing is only showing 1 note per day per type <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/111">#111</a></li>
								<li class="complete">Log outgoing email messages in activity meta <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/112">#112</a></li>
							</ul>
						</div>


						<br />
						<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseRecActivity"><strong><i class="glyphicon glyphicon-list"></i> Record Activity</strong> - 80%</a></h4>
						<div id="collapseRecActivity" class="panel-collapse collapse in">
                            <br/>link: <a
                                    href="{{ empty($patient) ? '' : URL::route('patient.activity.create', array('patient' => $patient->id)) }}"
                                    target="_blank">{{ empty($patient) ? '' : URL::route('patient.activity.create', array('patient' => $patient->id)) }}</a><br/><br/>
						</div>


						<br />
						<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseBillingReport"><strong><i class="glyphicon glyphicon-list"></i> Billing Report</strong> - 90%</a></h4>
						<div id="collapseBillingReport" class="panel-collapse collapse in">
                            <br/>link: <a
                                    href="{{ empty($patient) ? '' : URL::route('patient.reports.billing', array('patient' => $patient->id)) }}"
                                    target="_blank">{{ empty($patient) ? '' : URL::route('patient.reports.billing', array('patient' => $patient->id)) }}</a><br/><br/>
						</div>


						<br />
						<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseActivityReport"><strong><i class="glyphicon glyphicon-list"></i> Activity Report</strong> - 90%</a></h4>
						<div id="collapseActivityReport" class="panel-collapse collapse in">
                            <br/>link: <a
                                    href="{{ empty($patient) ? '' : URL::route('patient.activity.providerUIIndex', array('patient' => $patient->id)) }}"
                                    target="_blank">{{ empty($patient) ? '' : URL::route('patient.activity.providerUIIndex', array('patient' => $patient->id)) }}</a><br/><br/>
							<h5>Known Issues</h5>
							<ul>
								<li class="complete">Data doesnt match 2.8 <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/104">#104</a></li>
								<li class="complete">right margin/padding on top right "Go" button <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/104">#104</a></li>
								<li class="complete">Choosing 'Year' doesnt hold, resets to 2016 <a class="btn-primary btn-xs" target="_blank" href="{{ $codebaseUrl }}/104">#104</a></li>
							</ul>
						</div>


						<br />
						<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapsePrintCarePlan"><strong><i class="glyphicon glyphicon-list"></i> Print Care Plan</strong> - 90%</a></h4>
						<div id="collapsePrintCarePlan" class="panel-collapse collapse in">
                            <br/>link: <a
                                    href="{{ empty($patient) ? '' : URL::route('patient.careplan.print', array('patient' => $patient->id)) }}"
                                    target="_blank">{{ empty($patient) ? '' : URL::route('patient.careplan.print', array('patient' => $patient->id)) }}</a><br/><br/>
							<h5>Known Issues</h5>
							<ul>
								<li class="complete">Very slow page load</li>
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
