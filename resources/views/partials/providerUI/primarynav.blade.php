<?php
if (isset($patient)) {
    //$patient can be a User or Patient model.
    $seconds     = $patient->getCcmTime();
    $H           = floor($seconds / 3600);
    $i           = ($seconds / 60) % 60;
    $s           = $seconds % 60;
    $monthlyTime = sprintf("%02d:%02d:%02d", $H, $i, $s);
} else {
    $monthlyTime = "";
}
?>
@push('styles')
    <style>
        .full-width {
            width: 100%;
        }

        .margin-0 {
            margin-right: 0px;
            margin-left: 0px;
        }
    </style>
@endpush
<nav class="navbar primary-navbar">
    <div class="row container-fluid full-width margin-0">

        <div class="navbar-header col-lg-1 col-sm-2 col-xs-2">
            <a href="{{ url('/') }}" style="border: none" class="navbar-brand"><img
                        src="{{mix('/img/ui/clh_logo_lt.png')}}"
                        alt="Care Plan Manager"
                        style="position:relative;top:-15px"
                        width="50px"/></a>
            {{--<a href="{{ route('patients.dashboard') }}" style="font-size: 19px;"--}}
            {{--class="navbar-title collapse navbar-collapse navbar-text navbar-left">CarePlan<span class="thin">Manager™</span></a>--}}

        </div>

        <div class="col-lg-4 col-sm-10 col-xs-10" id="search-bar-container">
            @include('partials.search')
        </div>

        <div class="hidden-xs col-lg-7 col-sm-12">
            <ul class="nav navbar-nav navbar-right">
                @if (Route::getCurrentRoute()->getName() !== "patient.show.call.page" && auth()->user()->hasRole('care-center') && isset($patient) && optional($patient)->id && (!isset($noLiveCountTimeTracking)))
                    <li>
                        <time-tracker-call-mode ref="timeTrackerCallMode"
                                                :twilio-enabled="{{ $patient->primaryPractice->cpmSettings()->twilio_enabled }}"
                                                :patient-id="{{ $patient->id }}"></time-tracker-call-mode>
                    </li>
                @endif
                @if(auth()->user()->hasRole('saas-admin') || auth()->user()->isAdmin() || auth()->user()->hasRole('saas-admin-view-only'))
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

                @if (!isset($patient))
                    <li data-monthly-time="{{$monthlyTime}}"
                        style="line-height: 20px;">
                        <time-tracker ref="TimeTrackerApp" :info="timeTrackerInfo" :hide-tracker="true"
                                      :no-live-count="{{$noLiveCountTimeTracking ?? true}}"
                                      :override-timeout="{{config('services.time-tracker.override-timeout')}}"></time-tracker>
                    </li>
                @endif
                <li>
                    <a href="{{ route('patients.dashboard') }}"><i class="icon--home--white"></i> Home</a>
                </li>
                <li>
                    <a href="{{ route('patients.listing') }}"><i class="icon--patients"></i> Patient List</a>
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
                        {{auth()->user()->getFullName()}}
                        <span class="caret" style="color: #fff"></span>
                    </div>
                    <ul class="dropdown-menu" role="menu" style="background: white !important;">

                        @impersonating
                        <li>
                            <a href="{{ route('impersonate.leave') }}">Leave impersonation</a>
                        </li>
                        @endImpersonating

                        @if(auth()->user()->hasRole(['care-center']) && auth()->user()->isNotSaas())
                            <li>
                                <a href="{{ route('care.center.work.schedule.index') }}" id="work-schedule-link">
                                    Work Schedule
                                </a>
                            </li>
                        @endif

                        @if ( ! auth()->guest() && auth()->user()->hasRole(['administrator', 'administrator-view-only']) && auth()->user()->isNotSaas())
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
        <!-- /navbar-collapse -->
    </div>
    <!-- /container-fluid -->

</nav><!-- /navbar -->
