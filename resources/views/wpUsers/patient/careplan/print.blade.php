@if(!isset($isPdf))
    @extends('partials.providerUI')
@endif

<?php

if (isset($patient) && !empty($patient)) {
    $billing = null;
    $lead = null;
    if (!empty($patient->getBillingProviderIDAttribute())) $billing = App\User::find($patient->getBillingProviderIDAttribute());
    if (!empty($patient->getLeadContactIDAttribute())) $lead = App\User::find($patient->getLeadContactIDAttribute());

    $today = \Carbon\Carbon::now()->toFormattedDateString();
// $provider = App\User::find($patient->getLeadContactIDAttribute());
    $alreadyShown = [];
}
?>

@if(!isset($isPdf))
    @section('title', 'Care Plan View/Print')
    @section('activity', 'Care Plan View/Print')
@endif

@section('content')
    @if(isset($patient) && !empty($patient))
        <div id="v-pdf-careplans" class="container">
            <section class="patient-summary">
                <div class="patient-info__main">
                    @if(!isset($isPdf))

                        <div class="row">
                            <div class="col-xs-12 text-right hidden-print">

                                @if($showInsuranceReviewFlag)
                                    <div class="alert alert-danger text-left" role="alert">
                                        <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                                        <span class="sr-only">Error:</span>
                                        Insurance plans in record may be expired.
                                        <a class="alert-link"
                                           href="{{ URL::route('patient.demographics.show', [
                                           'patientId' => $patient->id,
                                           'scrollTo' => 'insurance-policies'
                                           ]) }}">
                                            Click to edit
                                        </a>
                                    </div>
                                @endif


                                <div class="col-xs-12 text-left">
                                    <pdf-careplans v-cloak>
                                        <span class="btn btn-group text-right">
                                        @if ( ($patient->carePlanStatus == 'qa_approved' && auth()->user()->canApproveCarePlans()) || ($patient->carePlanStatus == 'draft' && auth()->user()->hasPermission('care-plan-qa-approve')) )
                                                <a style="margin-right:10px;" class="btn btn-info btn-sm inline-block"
                                                   aria-label="..."
                                                   role="button"
                                                   href="{{ URL::route('patient.careplan.approve', ['patientId' => $patient->id]) }}">Approve</a>

                                                @if(auth()->user()->hasRole('provider'))
                                                    <a style="margin-right:10px;"
                                                       class="btn btn-success btn-sm inline-block"
                                                       aria-label="..."
                                                       role="button"
                                                       href="{{ route('patient.careplan.approve', ['patientId' => $patient->id, 'viewNext' => true]) }}">Approve and View Next</a>
                                                @endif
                                            @endif

                                            <a class="btn btn-info btn-sm inline-block" aria-label="..." role="button"
                                               href="{{ URL::route('patients.careplan.multi') }}?users={{ $patient->id }}">Print This Page</a>

                                        <form class="lang" action="#" method="POST" id="form">
                                        {{ csrf_field() }}
                                            <input type="hidden" name="lang" value="es"/>
                                            <!-- <button type="submit" class="btn btn-info btn-sm text-right" aria-label="..." value="">Translate to Spanish</button>
                                  -->       </form>
                                    </span>
                                    </pdf-careplans>
                                </div>
                            </div>

                        </div>
                    @endif
                    <div class="row gutter">
                        <div class="col-xs-12">
                            <h1 class="patient-summary__title patient-summary__title_9 patient-summary--careplan">Care
                                Plan</h1>
                        </div>

                        @include('partials.carePlans.approval-box')
                    </div>

                    <br>

                    <div class="row gutter">
                        <div class="col-xs-4 col-md-4 print-row text-bold">{{$patient->fullName}}</div>
                        <div class="col-xs-4 col-md-4 print-row">{{$patient->phone}}</div>
                        <div class="col-xs-4 col-md-4 print-row text-right">{{$today}}</div>
                    </div>
                    <div class="row gutter">
                        <div class="col-xs-4 col-md-4 print-row text-bold">
                            @if($billing)
                                {{$billing->fullName}} {!! ($billing->getSpecialtyAttribute() == '')? '' :  "<br> {$billing->getSpecialtyAttribute()}"!!}
                            @else
                                <em>No Billing Provider Selected</em>
                            @endif
                        </div>
                        <div class="col-xs-4 col-md-4 print-row">
                            @if($billing)
                                {{$billing->phone}}
                            @endif
                        </div>
                        <div class="col-xs-4 col-md-4 print-row text-bold text-right">{{$patient->getPreferredLocationName()}}</div>
                    </div>
                </div>
                <!-- CARE AREAS -->
                <div class="patient-info__subareas">
                    <div class="row">
                        <div class="col-xs-12">
                            <h2 class="patient-summary__subtitles patient-summary--careplan-background">We Are
                                Managing</h2>
                        </div>
                    </div>
                    <div class="row gutter">
                        <div class="col-xs-12">
                            <ul class="subareas__list">
                                @if($problemNames)
                                    @foreach($problemNames as $prob)
                                        <li class='subareas__item inline-block col-xs-6 col-sm-3 print-row'>{{$prob}}</li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- /CARE AREAS -->
                <!-- BIOMETRICS -->
                @if($biometrics)
                    <div class="patient-info__subareas">
                        <div class="row">
                            <div class="col-xs-12">
                                <h2 class="patient-summary__subtitles patient-summary--careplan-background">Your Health
                                    Goals</h2>
                            </div>
                        </div>
                        <div class="row">
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
                        </div>
                    </div>
                @endif
            <!-- /BIOMETRICS -->

                <!-- MEDICATIONS -->
                <div class="patient-info__subareas">
                    <div class="row">
                        <div class="col-xs-12">
                            <h2 class="patient-summary__subtitles patient-summary--careplan-background">Medications <a
                                        class="btn btn-primary"
                                        href="{{ URL::route('patient.careplan.show', array('patient' => $patient->id, 'page' => '1')) }}"><span
                                            class="glyphicon glyphicon-edit" aria-hidden="true"></span></a></h2>
                        </div>
                        <div class="col-xs-10">
                            <ul><strong>Monitoring these Medications</strong><BR>
                                @if(!empty($medications_monitor))
                                    @if(is_array($medications_monitor))
                                        @foreach($medications_monitor as $medi)
                                            <li style="margin-top:14px;">{!! $medi !!}</li>
                                        @endforeach
                                    @else
                                        {{$medications_monitor}}
                                    @endif
                                @endif
                            </ul>
                        </div>
                        <div class="col-xs-10">
                            <ul><strong>Taking these Medications</strong><BR>
                                @if(!empty($taking_medications))
                                    @if(is_array($taking_medications))
                                        @foreach($taking_medications as $medi)
                                            <li style="margin:14px 0px 0px 0px;">{!! $medi !!}</li>
                                        @endforeach
                                    @else
                                        {{$taking_medications}}
                                    @endif
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- /MEDICATIONS -->

                <!-- SYMPTOMS -->
                <div class="patient-info__subareas">
                    <div class="row">
                        <div class="col-xs-12">
                            <h2 class="patient-summary__subtitles patient-summary--careplan-background">Watch out
                                for:</h2>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12">
                            <ul class="subareas__list">
                                @foreach($symptoms as $s)
                                    @if($symptoms)
                                        <li class='subareas__item inline-block col-xs-6 col-sm-4 print-row'>{{$s}}</li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- /SYMPTOMS -->

                <!-- LIFESTYLES -->
                <div class="patient-info__subareas">
                    <div class="row">
                        <div class="col-xs-12">
                            <h2 class="patient-summary__subtitles patient-summary--careplan-background">We Are Informing
                                You
                                About</h2>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12">
                            <ul class="subareas__list">
                                @if($lifestyle)
                                    @foreach($lifestyle as $style)
                                        <li class='subareas__item inline-block col-xs-6 col-sm-3 print-row'>{{$style}}</li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- /LIFESTYLES -->


                <!-- INSTRUCTIONS -->
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
                            <p>Your care team will check in with you at {{$patient->phone}} periodically.</p>
                        </div>
                        <div class="col-xs-12">
                            <h2 class="patient-summary__subtitles patient-summary--careplan-background">Follow these
                                Instructions:</h2>
                        </div>
                        <div class="col-xs-12">
                            <p></p>
                        </div>
                    </div>
                </div>

                @include('partials.view-care-plan.followTheseInstructions')

            <!-- OTHER INFORMATION -->
                <div class="row pb-before">
                    <div class="col-xs-12 print-only">
                        <h1 class="patient-summary__title patient-summary__title_9  patient-summary--careplan">Care Plan
                            Part 3</h1>
                    </div>
                    <div class="col-xs-12">
                        <h1 class="patient-summary__title--secondary patient-summary--careplan"><p>Other information</p>
                        </h1>
                    </div>
                </div>

                <!-- ALLERGIES -->
                <div class="patient-info__subareas">
                    <div class="row">
                        <div class="col-xs-12">
                            <h2 class="patient-summary__subtitles patient-summary--careplan-background">Allergies:</h2>
                        </div>
                        <div class="col-xs-12">
                            @if($allergies)
                                <p><?= nl2br($allergies) ?></p>
                            @else
                                <p>No instructions at this time</p>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- /ALLERGIES -->

                <!-- SOCIALSERVICES -->
                <div class="patient-info__subareas">
                    <div class="row">
                        <div class="col-xs-12">
                            <h2 class="patient-summary__subtitles patient-summary--careplan-background">Social
                                Services:</h2>
                        </div>
                        <div class="col-xs-12">
                            @if($social)
                                <p><?= nl2br($social) ?></p>
                            @else
                                <p>No instructions at this time</p>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- /SOCIAL AND OTHER SERVICES -->

                <!-- CARE TEAM -->
                <div id="care-team" class="patient-info__subareas">
                    <div class="row">
                        <div class="col-xs-12">
                            <h2 id="care-team-label" class="patient-summary__subtitles patient-summary--careplan-background">Care Team:</h2>
                        </div>
                        <div class="col-xs-12">
                            @include('wpUsers.patient.careplan.print.careteam')
                        </div>
                    </div>
                </div>
                <!-- /CARE TEAM -->

                <!-- Appointments -->
                @if(isset($appointments['upcoming'] ) || isset($appointments['past'] ))

                    <div class="patient-info__subareas">
                        <div class="row">
                            <div class="col-xs-12">
                                <h2 class="patient-summary__subtitles patient-summary--careplan-background">
                                    Appointments</h2>
                            </div>
                            <div class="col-xs-12">

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
                            </div>
                        </div>
                    </div>
            @endif
            <!-- /Appointments -->

                <!-- OTHER NOTES -->
                <div class="patient-info__subareas">
                    <div class="row">
                        <div class="col-xs-12">
                            <h2 class="patient-summary__subtitles patient-summary--careplan-background">Other
                                Notes:</h2>
                        </div>
                        <div class="col-xs-12">
                            @if($other)
                                <p><?= nl2br($other) ?></p>
                            @else
                                <p>No instructions at this time</p>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- /OTHER NOTES -->
                <!-- /OTHER INFORMATION -->
            </section>
        </div>
    @endif
@stop