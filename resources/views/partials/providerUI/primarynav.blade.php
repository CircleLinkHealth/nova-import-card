<?php
$noLiveCountTimeTracking = isset($noLiveCountTimeTracking) && $noLiveCountTimeTracking;
if (isset($patient)) {
    //$patient can be a User or Patient model.
    $seconds     = $patient->getCcmTime();
    $H           = floor($seconds / 3600);
    $i           = ($seconds / 60) % 60;
    $s           = $seconds % 60;
    $monthlyTime = sprintf('%02d:%02d:%02d', $H, $i, $s);
} else {
    $monthlyTime = '';
}
$user = auth()->user();
?>
@push('styles')
    <style>
        .full-width {
            width: 100%;
        }

        .margin-0 {
            margin-right: 0;
            margin-left: 0;
        }
    </style>
@endpush

<nav class="navbar primary-navbar">
    <div class="container-fluid full-width margin-0">
        <a class="navbar-brand" href="{{ url('/') }}" style="border: none"><img
                    src="{{mix('/img/ui/clh_logo_lt.png')}}"
                    alt="Care Plan Manager"
                    style="position:relative;top:-15px"
                    width="50px"/></a>

        <button type="button" class="navbar-toggle collapsed" style="border-color:white" data-toggle="collapse"
                data-target="#navbar-collapse" aria-expanded="false">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar" style="background-color:white"></span>
            <span class="icon-bar" style="background-color:white"></span>
            <span class="icon-bar" style="background-color:white"></span>
        </button>

        <div class="" id="search-bar-container">
            @include('partials.search')
        </div>

        <div class="col-lg-7 col-sm-12 col-xs-12">
            <div class="collapse navbar-collapse" id="navbar-collapse">
                <ul class="nav navbar-nav navbar-right">
                    @if (Route::getCurrentRoute()->getName() !== "patient.show.call.page" && $user->hasRole('care-center') && isset($patient) && optional($patient)->id && !$noLiveCountTimeTracking)
                        <li>
                            <time-tracker-call-mode ref="timeTrackerCallMode"
                                                    :twilio-enabled="@json(config('services.twilio.enabled') && ($patient->primaryPractice ? $patient->primaryPractice->isTwilioEnabled() : false))"
                                                    :patient-id="{{ $patient->id }}"></time-tracker-call-mode>
                        </li>
                    @endif
                    @if($user->hasRole('saas-admin') || $user->isAdmin() || $user->hasRole('saas-admin-view-only'))
                        <li class="dropdown-toggle">
                            <div class="dropdown-toggle" data-toggle="dropdown" role="button"
                                 aria-expanded="false"
                                 style="background: none !important;padding: 15px;line-height: 20px;cursor: pointer;">
                                Users <span class="caret" style="color: #fff"></span>
                            </div>
                            <ul class="dropdown-menu" role="menu" style="background: white !important;">
                                <li><a href="{{ route('saas-admin.users.create', []) }}">Add Internal User</a></li>
                                <li><a href="{{ route('saas-admin.practices.index', []) }}">Add Customer User</a></li>
                                <li><a href="{{ route('saas-admin.users.index', []) }}">View All</a></li>
                            </ul>
                        </li>

                        <li class="dropdown-toggle">
                            <div class="dropdown-toggle" data-toggle="dropdown" role="button"
                                 aria-expanded="false"
                                 style="background: none !important;padding: 15px;line-height: 20px;cursor: pointer;">
                                Practices <span class="caret" style="color: #fff"></span>
                            </div>
                            <ul class="dropdown-menu" role="menu" style="background: white !important;">
                                <li><a href="{{ route('saas-admin.practices.create')}}">Add New</a></li>
                                <li><a href="{{ route('saas-admin.practices.index')}}">Manage</a></li>
                                <li><a href="{{ route('saas-admin.practices.billing.create', []) }}">Billable Patient
                                        Report</a></li>
                                <li><a href="{{ route('saas-admin.monthly.billing.make', []) }}">Approve Billable
                                        Patients</a></li>
                            </ul>
                        </li>
                    @endif

                    @if ( ! auth()->guest()
                         && $user->isNotSaas()
                         && $user->hasRole('software-only'))
                        <li class="dropdown">
                            <div class="dropdown-toggle" data-toggle="dropdown" role="button"
                                 aria-expanded="false"
                                 style="background: none !important;padding: 15px;line-height: 20px;cursor: pointer;">
                                Admin
                                <span class="caret" style="color: #fff"></span>
                            </div>
                            <ul class="dropdown-menu" role="menu" style="background: white !important;">
                                <li>
                                    <a href="{{ route('admin.patientCallManagement.v2.index') }}">
                                        Patient Activity Management
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('monthly.billing.make', []) }}">
                                        Approve Billable Patients
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif

                    @if (!isset($patient))
                        <li data-monthly-time="{{$monthlyTime}}"
                            class="col-lg-1"
                            style="line-height: 20px; display: none">
                            <time-tracker ref="TimeTrackerApp" :info="timeTrackerInfo" :hide-tracker="true"
                                          :twilio-enabled="@json(config('services.twilio.enabled'))"
                                          :no-live-count="@json($noLiveCountTimeTracking)"
                                          :override-timeout="{{config('services.time-tracker.override-timeout')}}"></time-tracker>
                        </li>
                    @endif

                    <li>
                        <a href="{{ route('patients.dashboard') }}" style="color: #fff"><i class="icon--home--white"></i>Home</a>
                    </li>

                    <li>
                        <a href="{{ route('patients.listing') }}" style="color: #fff"><i class="icon--patients"></i> Patient List</a>
                    </li>

                    <li class="dropdown">
                        <div class="dropdown-toggle" data-toggle="dropdown" role="button"
                             aria-expanded="false"
                             style="background: none !important;padding: 15px;line-height: 20px;cursor: pointer;">
                            <i class="glyphicon glyphicon-list-alt"></i>
                            Reports
                            <span class="caret" style="color: #fff"></span>
                        </div>

                        <ul class="dropdown-menu" role="menu" style="background: white !important;">
                            @role('administrator')
                            <li>
                                <a href="{{ route('patients.careplan.printlist', []) }}">Care Plan Print List</a>
                            </li>
                            @endrole
                            <li>
                                <a href="{{ route('patient.note.listing') }}">Notes Report</a>
                            </li>
                            <li>
                                <a href="{{route('patient.reports.u20')}}">Under 20 Minutes Report</a>
                            </li>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <div class="dropdown-toggle" data-toggle="dropdown" role="button"
                             aria-expanded="false"
                             style="background: none !important;padding: 15px;line-height: 20px;cursor: pointer;">
                            <i class="glyphicon glyphicon glyphicon-cog"></i>
                            {{$user->getFullName()}}
                            <span class="caret" style="color: #fff"></span>
                        </div>
                        <ul class="dropdown-menu" role="menu" style="background: white !important;">

                            @impersonating
                            <li>
                                <a href="{{ route('impersonate.leave') }}">Leave impersonation</a>
                            </li>
                            @endImpersonating

                            @if(auth()->user()->hasRole(['care-center']) && auth()->user()->isNotSaas())
                                <li class="hidden-xs">
                                    <a href="{{ route('offline-activity-time-requests.index') }}"
                                       id="offline-activity-time-requests-index-link">
                                        Offline Activity Time Requests
                                    </a>
                                </li>
                                <li class="hidden-xs">
                                    <a href="{{ route('care.center.work.schedule.index') }}" id="work-schedule-link">
                                        Work Schedule
                                    </a>
                                </li>
                            @endif
                            @if ( ! auth()->guest() && $user->hasRole(['administrator', 'administrator-view-only']) && $user->isNotSaas())
                                <li><a style="color: #47beab"
                                       href="{{ empty($patient->id) ? route('admin.dashboard') : route('admin.users.edit', array('patient' => $patient->id)) }}">
                                        Admin Panel
                                    </a>
                                </li>
                            @endif
                            @if(isAllowedToSee2FA())
                                <li>
                                    <a href="{{ route('user.settings.manage') }}">
                                        Account Settings
                                    </a>
                                </li>
                            @endif
                            <li><a href="{{ route('user.logout') }}">
                                    Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

