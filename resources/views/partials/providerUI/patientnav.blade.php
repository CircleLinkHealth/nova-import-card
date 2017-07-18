<nav class="navbar secondary-navbar hidden-xs">
    <div class="patient__actions text-center">
        <ul class="navbar-nav nav">
            <li class="inline-block">
                <a href="{{ empty($patient->id) ? URL::route('patients.search') : URL::route('patient.note.index', array('patient' => $patient->id)) }}">
                    Notes and Activities
                </a>
            </li>

            <li class="inline-block">
                <a href="{{ empty($patient->id) ? URL::route('patients.search') : URL::route('patient.summary', array('patient' => $patient->id)) }}"
                   role="button">Patient Overview</a>
            </li>

            @if ($patient->primaryPractice->settings->first()->careplan_mode == 'web')
                <li class="inline-block">
                    <a href="{{ empty($patient->id) ? URL::route('patients.search') : URL::route('patient.careplan.show', array('patient' => $patient->id, 'page' => '1')) }}"
                       role="button">Edit Care Plan</a>
                </li>
            @endif

            @if ($patient->primaryPractice->settings->first()->careplan_mode == 'pdf')
                <li class="inline-block">
                    <a href="{{ URL::route('patient.demographics.show', array('patientId' => $patient->id)) }}"
                       role="button">Patient Profile</a>
                </li>
            @endif


            <li class="inline-block dropdown">
                <a href="" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"
                   omitsubmit="yes">Patient Reports <span class="caret"></span></a>
                <ul class="dropdown-menu" role="menu">
                    <li>
                        <a href="{{ empty($patient->id) ? URL::route('patients.search') : URL::route('patient.activity.providerUIIndex', array('patient' => $patient->id)) }}">Patient
                            Activity Report</a>
                    </li>
                    <li>
                        <a href="{{ empty($patient->id) ? URL::route('patients.search') : URL::route('patient.reports.progress', array('patient' => $patient->id)) }}">Progress
                            Report</a>
                    </li>
                </ul>
            </li>

            <li class="inline-block">
                <a href="{{ empty($patient->id) ? URL::route('patients.search') : URL::route('patient.careplan.print', array('patient' => $patient->id)) }}"
                   role="button">View Care Plan</a>
            </li>
        </ul>
    </div>
</nav><!-- /navbar -->
