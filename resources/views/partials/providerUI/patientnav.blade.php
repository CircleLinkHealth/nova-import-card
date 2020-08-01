<nav class="navbar secondary-navbar">
    <div class="patient__actions text-center col-lg-12 col-lg-offset-0 col-sm-4 col-sm-offset-4 col-xs-12 col-xs-offset-0">
        <ul class="navbar-nav nav">

            <li class="inline-block">
                <a href="{{ empty($patient->id) ? route('patients.search') : route('patient.note.index', [$patient->id]) }}">
                    Notes and Activities
                </a>
            </li>

            <li class="inline-block">
                <a href="{{ empty($patient->id) ? route('patients.search') : route('patient.summary', [$patient->id]) }}"
                   role="button">Patient Overview</a>
            </li>

            <li class="inline-block">
                <a href="{{ route('patient.demographics.show', [$patient->id]) }}"
                   role="button">Patient Profile</a>
            </li>


            <li class="inline-block dropdown">
                <a href="" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"
                   omitsubmit="yes">Patient Reports <span class="caret"></span></a>
                <ul class="dropdown-menu" role="menu">
                    <li>
                        <a href="{{ empty($patient->id) ? route('patients.search') : route('patient.activity.providerUIIndex', [$patient->id]) }}">Patient
                            Activity Report</a>
                    </li>
{{--                    @if(auth()->user()->isNotSaas())--}}
{{--                        <li>--}}
{{--                            <a href="{{ empty($patient->id) ? route('patients.search') : route('patient.reports.progress', array('patient' => $patient->id)) }}">Progress--}}
{{--                                Report</a>--}}
{{--                        </li>--}}
{{--                    @endif--}}
                    <li>
                        <a href="{{ empty($patient->id) ? route('patients.search') : route('patient.care-docs', [$patient->id]) }}">Wellness Visit Docs</a>
                    </li>
                    @if(auth()->user()->isAdmin() && $patient->hasCcda())
                        <li>
                            <a target="_blank" href="{{route('get.CCDViewerController.showByUserId', [$patient->id])}}">CCDA</a>
                        </li>
                    @endif
                </ul>
            </li>

            <li class="inline-block">
                <a href="{{ empty($patient->id) ? route('patients.search') : route('patient.careplan.print', [$patient->id]) }}"
                   role="button">View Care Plan</a>
            </li>
        </ul>
    </div>
</nav><!-- /navbar -->
