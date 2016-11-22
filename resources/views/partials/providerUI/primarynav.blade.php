<nav class="navbar primary-navbar">
    <div class="container-fluid col-md-12" style="width: 100%;">

        <div class="navbar-header" style="width: 6%;">
            <a href="{{ URL::route('patients.dashboard') }}" style="border: none" class="navbar-brand"><img src="/img/ui/clh_logo_lt.png"
                                                                                       alt="Care Plan Manager"
                                                                                       style="position:relative;top:-15px"
                                                                                       width="50px"/></a>
            {{--<a href="{{ URL::route('patients.dashboard') }}" style="font-size: 19px;"--}}
               {{--class="navbar-title collapse navbar-collapse navbar-text navbar-left">CarePlan<span class="thin">Manager™</span></a>--}}

        </div>
        @include('partials.search')

        <div class="navbar-right hidden-xs" style="">
            <ul class="nav navbar-nav">
                <li></li>
                {{--URL::route('patients.dashboard', array())--}}
                <li><a href="{{ URL::route('patients.dashboard') }}"><i class="icon--home--white"></i> Home</a></li>
                {{--<li><a href="{{ URL::route('patients.search') }}"><i class="icon--search--white"></i> Search Patient</a>--}}
                {{--</li>--}}
                <li><a href="{{ URL::route('patients.listing') }}"><i class="icon--patients"></i> Patient List</a>
                </li>
                {{--<li><a href="{{ URL::route('patients.demographics.show') }}"><i class="icon--add-user"></i> Add--}}
                        {{--Patient</a></li>--}}

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
                        <li>
                            <a href="{{URL::route('patient.reports.billing')}}">
                                Patient Billing Report</a>
                        </li>
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

                        @if(auth()->user()->hasRole(['care-center']))
                            <li>
                                <a href="{{ route('care.center.work.schedule.index') }}" id="work-schedule-link">
                                    Work Schedule
                                </a>
                            </li>
                        @endif

                        @if ( !Auth::guest() && Auth::user()->can(['admin-access']))
                            <li><a style="color: #47beab" href="{{ empty($patient->id) ? URL::route('admin.dashboard') : URL::route('admin.users.edit', array('patient' => $patient->id)) }}">
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
