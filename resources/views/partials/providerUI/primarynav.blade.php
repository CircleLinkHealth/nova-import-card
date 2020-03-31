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
$user         = auth()->user();
$isTwoFaRoute = Route::is(['user.2fa.show.token.form', 'user.settings.manage']);
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

        .top-nav-item-icon {
            height: 19px;
            width: 20px;
            margin-right: 3px;
        }

        .top-nav-item {
            background: none !important;
            padding: 15px;
            line-height: 20px;
            cursor: pointer;
        }

        .text-white {
            color: #fff;
        }

        .search-bar {
            width: 90%;
        }
    </style>
@endpush

<nav class="navbar primary-navbar">
    <div class="container-fluid full-width margin-0">
        <div class="row">
            <div class="col-lg-4 col-sm-12 col-xs-12">
                <div class="row">
                    <div class="col-md-3 col-xs-12">
                        <a class="navbar-brand" href="{{ url('/') }}" style="padding: 5px 15px; border: none"><img
                                    src="{{mix('/img/logos/LogoHorizontal_White.svg')}}"
                                    alt="Care Plan Manager"
                                    style="position:relative;top:-7px"
                                    height="50"
                                    width="105"/></a>

                        <button type="button" class="navbar-toggle collapsed" style="border-color:white"
                                data-toggle="collapse"
                                data-target="#navbar-collapse" aria-expanded="false">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar" style="background-color:white"></span>
                            <span class="icon-bar" style="background-color:white"></span>
                            <span class="icon-bar" style="background-color:white"></span>
                        </button>
                    </div>

                    @if(!$isTwoFaRoute)
                        <div id="search-bar-container" class="col-md-9 col-xs-12">
                            @include('partials.search')
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-lg-8 col-sm-12 col-xs-12">
                <div class="collapse navbar-collapse" id="navbar-collapse">
                    <ul class="nav navbar-nav navbar-right">
                        @if(!$isTwoFaRoute)
                            @if (Route::getCurrentRoute()->getName() !== "patient.show.call.page" && $user->isCareCoach() && isset($patient) && optional($patient)->id && !$noLiveCountTimeTracking)
                                <li>
                                    <time-tracker-call-mode ref="timeTrackerCallMode"
                                                            :twilio-enabled="@json(config('services.twilio.enabled') && ($patient->primaryPractice ? $patient->primaryPractice->isTwilioEnabled() : false))"
                                                            :patient-id="{{ $patient->id }}"></time-tracker-call-mode>
                                </li>
                            @endif
                            @if($user->hasRole('saas-admin') || $user->isAdmin() || $user->hasRole('saas-admin-view-only'))
                                <li class="dropdown-toggle">
                                    <div class="dropdown-toggle top-nav-item" data-toggle="dropdown" role="button"
                                         aria-expanded="false">
                                        Users <span class="caret text-white"></span>
                                    </div>
                                    <ul class="dropdown-menu" role="menu" style="background: white !important;">
                                        <li><a href="{{ route('saas-admin.users.create') }}">Add Internal User</a></li>
                                        <li><a href="{{ route('saas-admin.practices.index') }}">Add Customer User</a>
                                        </li>
                                        <li><a href="{{ route('saas-admin.users.index') }}">View All</a></li>
                                    </ul>
                                </li>

                                <li class="dropdown-toggle">
                                    <div class="dropdown-toggle top-nav-item" data-toggle="dropdown" role="button"
                                         aria-expanded="false">
                                        Practices <span class="caret text-white"></span>
                                    </div>
                                    <ul class="dropdown-menu" role="menu" style="background: white !important;">
                                        <li><a href="{{ route('saas-admin.practices.create')}}">Add New</a></li>
                                        <li><a href="{{ route('saas-admin.practices.index')}}">Manage</a></li>
                                        <li><a href="{{ route('saas-admin.practices.billing.create') }}">Billable
                                                Patient
                                                Report</a></li>
                                        <li><a href="{{ route('saas-admin.monthly.billing.make') }}">Approve Billable
                                                Patients</a></li>
                                    </ul>
                                </li>
                            @endif

                            @if ( ! auth()->guest()
                                 && $user->isNotSaas()
                                 && $user->hasRole('software-only'))
                                <li class="dropdown">
                                    <div class="dropdown-toggle top-nav-item" data-toggle="dropdown" role="button"
                                         aria-expanded="false">
                                        Admin
                                        <span class="caret text-white"></span>
                                    </div>
                                    <ul class="dropdown-menu" role="menu" style="background: white !important;">
                                        <li>
                                            <a href="{{ route('admin.patientCallManagement.v2.index') }}">
                                                Patient Activity Management
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('monthly.billing.make') }}">
                                                Approve Billable Patients
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            @endif

                            @if (!isset($patient))
                                <li data-monthly-time="{{$monthlyTime}}"
                                    style="line-height: 20px;">
                                    <time-tracker ref="TimeTrackerApp" :info="timeTrackerInfo" :hide-tracker="true"
                                                  :twilio-enabled="@json(config('services.twilio.enabled'))"
                                                  :no-live-count="@json($noLiveCountTimeTracking)"
                                                  :override-timeout="{{config('services.time-tracker.override-timeout')}}"></time-tracker>
                                </li>
                            @endif

                            <li>
                                <a href="{{ route('patients.dashboard') }}" class="text-white"><i
                                            class="top-nav-item-icon glyphicon glyphicon-home"></i>Home</a>
                            </li>

                            <li>
                                <a href="{{ route('patients.listing') }}" class="text-white"><i
                                            class="top-nav-item-icon glyphicon glyphicon-user"></i>Patient List</a>
                            </li>

                            @if($user->isCareCoach())
                                <li>
                                    <a href="{{ route('patientCallList.index') }}" class="text-white"><i
                                                class="top-nav-item-icon glyphicon glyphicon-earphone"></i>Activities</a>
                                </li>
                            @endif

                            <li class="dropdown">
                                <div class="dropdown-toggle top-nav-item" data-toggle="dropdown" role="button"
                                     aria-expanded="false"><i
                                            class="top-nav-item-icon glyphicon glyphicon-list-alt"></i>Reports<span
                                            class="caret text-white"></span></div>

                                <ul class="dropdown-menu" role="menu" style="background: white !important;">
                                    @if($user->isAdmin())
                                        <li>
                                            <a href="{{ route('patients.careplan.printlist') }}">Care Plan Print
                                                List</a>
                                        </li>
                                    @endif
                                    <li>
                                        <a href="{{ route('patient.note.listing') }}">Notes Report</a>
                                    </li>
                                    <li>
                                        <a href="{{route('patient.reports.u20')}}">Under 20 Minutes Report</a>
                                    </li>
                                    @if($user->hasRole('developer'))
                                        <li>
                                            <a href="{{route('OpsDashboard.index')}}">Ops Dashboard</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('admin.reports.nurse.metrics') }}">
                                                Nurse Performance Report</a>
                                        </li>
                                    @endif
                                </ul>
                            </li>

                            {{--Live Notifications--}}
                            <li class="dropdown">
                                <div class="dropdown-toggle top-nav-item" data-toggle="dropdown" role="button"
                                     aria-expanded="false"><i class="glyphicon glyphicon-bell"></i> Notifications
                                    <a class="inline-block">

                                        <pusher-notifications
                                                :user-id="{{json_encode(auth()->id())}}"></pusher-notifications>
                                    </a>
                                </div>
                            </li>
                        @endif

                        @include('partials.user-account-dropdown')
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>

