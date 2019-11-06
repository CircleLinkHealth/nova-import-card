<div class="collapse navbar-collapse" id="navbar-collapse">
    <ul class="nav navbar-nav navbar-right">
        <li class="dropdown">
            <div class="dropdown-toggle top-nav-item" data-toggle="dropdown" role="button"
                 aria-expanded="false">
                <i class="top-nav-item-icon glyphicon glyphicon glyphicon-cog"></i>

                {{$user->getFullName()}}
                <span class="caret text-white"></span>
            </div>
            <ul class="dropdown-menu" role="menu" style="background: white !important;">

                @include('partials.last-login')

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
                    @if(!isProductionEnv() || (isProductionEnv() && Carbon\Carbon::now()->gte(Carbon\Carbon::create(2019,6,1,1,0,0))))
                        <li class="hidden-xs">
                            <a href="{{ route('care.center.invoice.review') }}"
                               id="offline-activity-time-requests-index-link">
                                Hours/Pay Invoice
                            </a>
                        </li>
                    @endif
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