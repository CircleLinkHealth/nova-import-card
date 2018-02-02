<?php
if (isset($patient)) {
    $seconds     = optional($patient->patientInfo)->cur_month_activity_time ?? 0;
    $H           = floor($seconds / 3600);
    $i           = ($seconds / 60) % 60;
    $s           = $seconds % 60;
    $monthlyTime = sprintf("%02d:%02d:%02d", $H, $i, $s);
} else {
    $monthlyTime = "";
}
?>
<nav class="navbar primary-navbar">
    <div class="container-fluid col-md-12" style="width: 100%;">

        <div class="navbar-header" style="width: 6%;">
            <a href="{{ url('/') }}" style="border: none" class="navbar-brand"><img
                        src="/img/ui/clh_logo_lt.png"
                        alt="Care Plan Manager"
                        style="position:relative;top:-15px"
                        width="50px"/></a>
            {{--<a href="{{ route('patients.dashboard') }}" style="font-size: 19px;"--}}
            {{--class="navbar-title collapse navbar-collapse navbar-text navbar-left">CarePlan<span class="thin">Manager™</span></a>--}}

        </div>

        <div class="col-md-5" id="search-bar-container">
            @include('partials.search')
        </div>

        <div class="navbar-right hidden-xs" style="">
            <ul class="nav navbar-nav">
                @if(auth()->user()->hasRole('saas-admin'))
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
                            <li><a href="{{ route('saas-admin.practices.create', []) }}">Add New</a></li>
                            <li><a href="{{ route('saas-admin.practices.index', []) }}">Manage Active</a></li>
                            <li><a href="{{ route('saas-admin.practices.billing.create', []) }}">Billable Patient Report</a></li>
                            <li><a href="{{ route('saas-admin.monthly.billing.make', []) }}">Approve Billable Patients</a></li>
                        </ul>
                    </li>
                @endif

                @if (!isset($patient))
                    <li data-monthly-time="{{$monthlyTime}}"
                        style="padding-top: 15px; padding-bottom: 15px; line-height: 20px;">
                        <time-tracker ref="TimeTrackerApp" :info="timeTrackerInfo" :hide-tracker="true"
                                      :no-live-count="{{$noLiveCountTimeTracking ?? true}}"></time-tracker>
                    </li>
                @endif
                <li>
                    <a href="{{ url('/') }}"><i class="icon--home--white"></i> Home</a>
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
                        <li>
                            <a href="{{ URL::route('patient.note.listing') }}">
                                Notes Report</a>
                        </li>
                        <li>
                            <a href="{{URL::route('patient.reports.u20')}}">
                                Under 20 Minute Report</a>
                        </li>
                        {{--<li>--}}
                        {{--<a href="{{URL::route('patient.reports.billing')}}">--}}
                        {{--Patient Billing Report</a>--}}
                        {{--</li>--}}
                        <li>
                            <a href="{{ URL::route('patients.careplan.printlist', array()) }}">
                                Patient Care Plan Print
                                List</a>
                        </li>
                    </ul>
                </li>

                <li class="dropdown">
                    <div class="dropdown-toggle" data-toggle="dropdown" role="button"
                         aria-expanded="false"
                         style="background: none !important;padding: 15px;line-height: 20px;cursor: pointer;">
                        <i class="glyphicon glyphicon glyphicon-cog"></i>
                        {{auth()->user()->fullName}}
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

                        @if ( !Auth::guest() && Auth::user()->hasRole(['administrator']) && auth()->user()->isNotSaas())
                            <li><a style="color: #47beab"
                                   href="{{ empty($patient->id) ? route('admin.dashboard') : route('admin.users.edit', array('patient' => $patient->id)) }}">
                                    Admin Panel
                                </a>
                            </li>
                        @endif

                        <li><a href="{{ url('/auth/logout') }}">
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
