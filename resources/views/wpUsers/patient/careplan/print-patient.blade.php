@extends('layouts.provider')

@section('app')
<?php
if (isset($patient)) {
    $today = \Carbon\Carbon::now()->toFormattedDateString();

    $alreadyShown = [];
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
        <div class="col-lg-1 col-sm-1 col-xs-1">
            <a class="navbar-brand" href="{{ url('/') }}" style="border: none"><img
                        src="{{mix('/img/logos/LogoHorizontal_White.svg')}}"
                        alt="Care Plan Manager"
                        style="top:-7px"
                        width="105px"/></a>
        </div>

        <div class="col-lg-11 col-sm-11 col-xs-11">
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
        </div>
    </div>
</nav>
@yield('content')
<?php
/**
 * Sometimes, $patient is an instance of User::class,
 * other times, it is an instance of \CircleLinkHealth\Customer\Entities\Patient::class
 * We have to make sure that $user is always an instance of User::class by deriving it from $patient.
 */
$user = null;
if (isset($patient)) {
    if (is_a($patient, \CircleLinkHealth\Customer\Entities\Patient::class)) {
        $user = $patient->user;
    } else {
        $user = $patient;
    }
}
?>
@endsection


@section('title', 'Care Plan View/Print')
@section('activity', 'Care Plan View/Print')

@section('content')
    @push('styles')
        <style>
            [v-cloak] > * {
                display: none
            }

            [v-cloak]::before {
                content: "loading…"
            }

            .patient-summary__subtitles span.glyphicon {
                margin-top: -7px;
            }

        </style>
    @endpush
    TEST
    <div id="v-pdf-careplans" class="container" v-cloak>
        <section class="patient-summary">
            <div class="patient-info__main">
                <div class="row">
                    <div class="col-xs-12 text-right hidden-print">
                        <div class="col-sm-12" style="text-align: center">
                            <br/>
                            <span style="font-size: 27px;{{$ccm_above ? 'color: #47beab;' : ''}}">
                                        <span data-monthly-time="{{$monthlyTime}}" style="color: inherit">
                                                <div class="color-grey">
                                                        <a href="{{ empty($patient->id) ?: route('patient.activity.providerUIIndex', ['patient' => $patient->id]) }}">
                                                            {{$monthlyTime}}
                                                        </a>
                                                </div>
                                        </span>
                                    </span>
                        </div>
                        @if(! empty(optional($errors)->messages()))
                            <div>
                                <div class="col-sm-12 alert alert-danger text-left"

                                     style="line-height: 2; margin-top: 3px">
                                    <h4>CarePlan cannot be approved because:</h4>
                                    <ul class="list-group">
                                        @foreach ($errors->all() as $error)
                                            <li>
                                                <span class="glyphicon glyphicon-exclamation-sign"></span> {!! $error !!}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="row" style="margin-bottom: 5%;">
                                    @include('errors.incompatibleBrowser')
                                </div>
                            </div>
                        @endif

                        @if($showInsuranceReviewFlag)
                            <div class="col-sm-12 alert alert-danger text-left" role="alert"
                                 style="margin-top: 3px">
                                <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                                <span class="sr-only">Error:</span>
                                Insurance plans in record may be expired.
                                <a class="alert-link"
                                   href="{{ route('patient.demographics.show', [
                                           'patientId' => $patient->id,
                                           'scrollTo' => 'insurance-policies'
                                           ]) }}">
                                    Click to edit
                                </a>
                            </div>
                        @endif
                    </div>

                </div>
                <div class="row gutter">
                    <div class="col-lg-12 col-lg-offset-0 col-xs-12 col-xs-offset-2">
                        <h1 class="patient-summary__title patient-summary__title_9 patient-summary--careplan">Care
                            Plan</h1>
                    </div>
                </div>

                <br>

                <div class="row gutter">
                    <div class="col-lg-5 col-xs-6 print-row text-bold">{{$patient->getFullName()}}
                        (DOB: {{$patient->patientInfo->dob()}})
                    </div>
                    <div class="col-lg-4 col-xs-4 print-row">{{$patient->getPhone()}}</div>
                    <div class="col-lg-3 col-xs-6 print-row text-right">{{$today}}</div>
                </div>

                <div class="row gutter">
                    @if($billingDoctor)
                        <div class="col-xs-5 print-row text-bold">
                            {{ $billingDoctor->getDoctorFullNameWithSpecialty() }}
                        </div>
                        <div class="col-xs-3 print-row">
                            {{$billingDoctor->getPhone()}}
                        </div>
                    @else
                        <div class="col-xs-5 print-row text-bold">
                            <em>No Billing Dr. Selected</em>
                        </div>
                        <div class="col-xs-3 print-row">
                        </div>
                    @endif
                    <div class="col-lg-4 col-xs-4 print-row text-bold text-right">{{$patient->getPreferredLocationName()}}</div>
                </div>

                @if($regularDoctor)
                    <div class="row gutter">
                        <div class="col-xs-5 print-row text-bold">
                            {{ $regularDoctor->getDoctorFullNameWithSpecialty() }}
                        </div>
                        <div class="col-xs-3 print-row">
                            {{$regularDoctor->getPhone()}}
                        </div>
                    </div>
                @endif

                @if(!isset($isPdf) && !empty($patient->patientInfo->general_comment))
                    <div class="row"></div>
                    <div class="row gutter">
                        <div class="col-xs-12 print-row">
                            <b>General comment</b>: {{$patient->patientInfo->general_comment}}
                        </div>
                    </div>
                @endif
            </div>
            <!-- CARE AREAS -->
            <care-areas ref="careAreasComponent" patient-id="{{$patient->id}}">
                <template>
                    @if($problemNames)
                        <ul class="subareas__list">
                            @foreach($problemNames as $prob)
                                <li class='subareas__item inline-block col-xs-6 col-sm-3 print-row'>{{$prob}}</li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center">No Problems at this time</div>
                    @endif
                </template>
            </care-areas>
            <!-- /CARE AREAS -->
            <!-- BIOMETRICS -->
            <health-goals ref="healthGoalsComponent" patient-id="{{$patient->id}}">
                @if($biometrics)
                    <ul class="subareas__list">
                        <li class="subareas__item subareas__item--wide col-sm-12">
                            @foreach(array_reverse($biometrics) as $key => $value)
                                @if ($key == 'Blood Pressure')

                                    <div class="col-xs-5 print-row text-bold">{{ $value['verb'] }} {{$key}}</div>
                                    <div class="col-xs-4 print-row text-bold">{{($value['verb'] == 'Regulate') ? 'keep under' :  'to' }} {{$value['target']}}</div>
                                    <div class="col-xs-3 print-row">
                                        from {{$value['starting']}}</div>

                                @else

                                    <div class="col-xs-5 print-row text-bold">{{ $value['verb'] }} {{$key}}</div>
                                    <div class="col-xs-4 print-row text-bold">{{($value['verb'] == 'Maintain') ? 'at' :  'to' }} {{$value['target']}}</div>
                                    <div class="col-xs-3 print-row">
                                        from {{$value['starting']}}</div>

                                @endif
                            @endforeach
                        </li>
                    </ul>
                @endif
            </health-goals>
            <!-- /BIOMETRICS -->

            <!-- MEDICATIONS -->
            <medications ref="medicationsComponent" patient-id="{{$patient->id}}">

                <div class="col-xs-10">
                    @if(!empty($taking_medications))
                        @if(is_array($taking_medications))
                            <ul>
                                @foreach($taking_medications as $medi)
                                    <li class='top-10'>
                                        <h4>{!! $medi !!}</h4>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            {{$taking_medications}}
                        @endif
                    @endif
                </div>
            </medications>
            <!-- /MEDICATIONS -->

            <!-- SYMPTOMS -->
            <symptoms ref="symptomsComponent" patient-id="{{$patient->id}}">
                <ul class="subareas__list">
                    @foreach($symptoms as $s)
                        @if($symptoms)
                            <li class='subareas__item inline-block col-xs-6 col-sm-4 print-row'>{{$s}}</li>
                        @endif
                    @endforeach
                </ul>
            </symptoms>
            <!-- /SYMPTOMS -->

            <!-- LIFESTYLES -->
            <lifestyles ref="lifestylesComponent" patient-id="{{$patient->id}}">
                <ul class="subareas__list">
                    @if($lifestyle)
                        @foreach($lifestyle as $style)
                            <li class='subareas__item inline-block col-xs-6 col-sm-3 print-row'>{{$style}}</li>
                        @endforeach
                    @endif
                </ul>
            </lifestyles>
            <!-- /LIFESTYLES -->


            <div class="patient-info__subareas pb-before">
                <div class="row">
                    <div class="col-xs-12 print-only">
                        <h1 class="patient-summary__title patient-summary__title_9 patient-summary--careplan">Care
                            Plan
                            Part 2</h1>
                    </div>

                    <div class="col-xs-12">
                        <h2 class="patient-summary__subtitles patient-summary--careplan-background">Check In
                            Plan</h2>
                    </div>

                    <div class="col-xs-12">
                        <p>Your care team will check in with you at {{$patient->getPhone()}} periodically.</p>
                    </div>
                </div>
            </div>

            <!-- INSTRUCTIONS -->
            <instructions ref="instructionsComponent" patient-id="{{$patient->id}}"></instructions>
            <!-- /INSTRUCTIONS -->

            <!-- OTHER INFORMATION -->
            <div class="row pb-before">
                <div class="col-xs-12 print-only">
                    <h1 class="patient-summary__title patient-summary__title_9  patient-summary--careplan">Care Plan
                        Part 3</h1>
                </div>
                {{--  <div class="col-xs-12">
                    <h1 class="patient-summary__title--secondary patient-summary--careplan"><p>Other information</p>
                    </h1>
                </div>  --}}
            </div>

            <!-- ALLERGIES -->
            <allergies ref="allergiesComponent" patient-id="{{$patient->id}}">
                <div class="col-xs-12">
                    @if($allergies)
                        <p><?= nl2br($allergies); ?></p>
                    @else
                        <p>No allergies at this time</p>
                    @endif
                </div>
            </allergies>
            <!-- /ALLERGIES -->

            <!-- SOCIALSERVICES -->
            <social-services ref="socialServicesComponent" patient-id="{{$patient->id}}">
                @if($social)
                    <p><?= nl2br($social); ?></p>
                @else
                    <p>No instructions at this time</p>
                @endif
            </social-services>
            <misc-modal ref="miscModal" :patient-id="{{$patient->id}}"></misc-modal>
            <!-- /SOCIAL AND OTHER SERVICES -->

            <!-- CARE TEAM -->
            <care-team ref="careTeamComponent"></care-team>
            <!-- /CARE TEAM -->

            <!-- Appointments -->
            <appointments ref="appointmentsComponent" patient-id="{{$patient->id}}">
                @if(isset($appointments['upcoming'] ))
                    <h3 class="patient-summary__subtitles--subareas patient-summary--careplan">
                        Upcoming: </h3>
                    <ul style="line-height: 30px">
                        @foreach($appointments['upcoming'] as $upcoming)
                            <li style="list-style: dash">

                                - {{$upcoming['type']}}
                                <strong>{{$upcoming['specialty']}} </strong>
                                on {{$upcoming['date']}}
                                at {{$upcoming['time']}} with
                                <strong>{{$upcoming['name']}}</strong>; {{$upcoming['address']}} {{$upcoming['phone']}}

                            </li>
                        @endforeach
                    </ul>
                @endif
                @if(isset($appointments['past'] ))
                    <h3 class="patient-summary__subtitles--subareas patient-summary--careplan">
                        Past:</h3>
                    <ul style="line-height: 30px">
                        @foreach($appointments['past'] as $past)
                            <li style="list-style: dash">

                                - {{$past['type']}}
                                <strong>{{$past['specialty']}} </strong>
                                on {{$past['date']}}
                                at {{$past['time']}} with
                                <strong>{{$past['name']}}</strong>; {{$past['address']}} {{$past['phone']}}

                            </li>
                        @endforeach
                    </ul>
                @endif
            </appointments>
            <!-- /Appointments -->

            <!-- OTHER NOTES -->
            <others ref="othersComponent" patient-id="{{$patient->id}}">
                @if($other)
                    <p><?= nl2br($other); ?></p>
                @else
                    <p>No instructions at this time</p>
                @endif
            </others>
            <!-- /OTHER NOTES -->
            <!-- /OTHER INFORMATION -->
        </section>
    </div>
@stop
