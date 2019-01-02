@if(!isset($isPdf))
    @extends('partials.providerUI')
@endif

<?php

if (isset($patient) && ! empty($patient)) {
    $today = \Carbon\Carbon::now()->toFormattedDateString();

    $alreadyShown = [];
}
?>

@if(!isset($isPdf))
    @section('title', 'Care Plan View/Print')
@section('activity', 'Care Plan View/Print')
@endif

@section('content')
    @push('styles')
        <style>
            [v-cloak] > * {
                display: none
            }

            [v-cloak]::before {
                content: "loading…"
            }
        </style>
    @endpush
    @if(isset($patient) && !empty($patient))
        <div id="v-pdf-careplans" class="container" v-cloak>
            <section class="patient-summary">
                <div class="patient-info__main">
                    @if(!isset($isPdf))
                        <div class="row">
                            <div class="col-xs-12 text-right hidden-print">
                                <div class="text-center">
                                    <span style="font-size: 27px;{{$ccm_above ? 'color: #47beab;' : ''}}">
                                        <span data-monthly-time="{{$monthlyTime}}" style="color: inherit"
                                              data-href="{{ empty($patient->id) ? route('patients.search') : route('patient.activity.providerUIIndex', array('patient' => $patient->id)) }}">
                                            <time-tracker ref="TimeTrackerApp" :info="timeTrackerInfo"
                                                          :twilio-enabled="@json(config('services.twilio.enabled') && (isset($patient) && $patient->primaryPractice ? $patient->primaryPractice->isTwilioEnabled() : true))"
                                                          :hide-tracker="true"
                                                          :override-timeout="{{config('services.time-tracker.override-timeout')}}"></time-tracker>
                                        </span>
                                    </span>
                                </div>
                                @if(! empty(optional($errors)->messages()))
                                    <div>
                                        <div class="alert alert-danger text-left" style="line-height: 2">
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
                                    <div class="alert alert-danger text-left" role="alert">
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


                                <div class="col-xs-12 text-left">
                                    @if ($recentSubmission || $skippedAssessment)
                                        <div class="text-right">
                                            <a class="btn btn-success btn-lg inline-block" aria-label="..."
                                               role="button" target="_blank" onclick="finalStepClick()"
                                               href="{{ route('patients.careplan.multi') }}?users={{ $patient->id }}&final=true">FINAL
                                                STEP:
                                                Print for Patient</a>
                                        </div>
                                        @push ('scripts')
                                            <script>
                                                function finalStepClick() {
                                                    setTimeout(function () {
                                                        location.href = "{{ route('patient.careplan.print', [ 'patientId' => $patient->id ]) }}"
                                                    }, 3000)
                                                }
                                            </script>
                                        @endpush
                                    @else
                                        <pdf-careplans v-cloak>
                                            <template slot="buttons">
                                            <?php
                                            $patientCarePlan = isset($patient)
                                                ? $patient->carePlan
                                                : null;
                                            $patientCarePlanPdfs = isset($patientCarePlan)
                                                ? $patientCarePlan->pdfs
                                                : null;
                                            $patientCarePlanPdfsHasItems = isset($patientCarePlanPdfs)
                                                ? $patientCarePlanPdfs->count() > 0
                                                : false;
                                            ?>
                                            @if ($patientCarePlanPdfsHasItems)
                                                <!--href="{{route('patient.pdf.careplan.print', ['patientId' => $patient->id])}}"-->
                                                    <a href="{{route('switch.to.pdf.careplan', ['carePlanId' => optional($patientCarePlan)->id])}}"
                                                       class="btn btn-info btn-sm inline-block">PDF CarePlans</a>
                                                @endif
                                            </template>

                                            @if ( ($patient->getCarePlanStatus() == 'qa_approved' && auth()->user()->canApproveCarePlans()) || ($patient->getCarePlanStatus() == 'draft' && auth()->user()->canQAApproveCarePlans()) )
                                                <a style="margin-right:10px;"
                                                   class="btn btn-info btn-sm inline-block"
                                                   aria-label="..."
                                                   role="button"
                                                   href="{{ route('patient.careplan.approve', ['patientId' => $patient->id]) }}">Approve</a>

                                                @if(auth()->user()->hasRole('provider'))
                                                    <a style="margin-right:10px;"
                                                       class="btn btn-success btn-sm inline-block"
                                                       aria-label="..."
                                                       role="button"
                                                       href="{{ route('patient.careplan.approve', ['patientId' => $patient->id, 'viewNext' => true]) }}">Approve
                                                        and View Next</a>

                                                    <form action="{{route('patient.careplan.not.eligible', ['patientId' => $patient->id, 'viewNext' => true])}}"
                                                          method="POST" id="not-eligible-form" style="display: inline">
                                                        {{ csrf_field() }}
                                                        <button type="button" style="margin-right:10px;"
                                                                onclick="notEligibleClick()"
                                                                class="btn btn-danger btn-sm text-right">Not Eligible
                                                        </button>

                                                        <script>
                                                            function notEligibleClick() {
                                                                if (confirm('CAUTION: Clicking "confirm" will delete this patient’s entire record from Care Plan Manager. This action cannot be undone. Do you want to delete this patients entire record?')) {
                                                                    document.getElementById('not-eligible-form').submit();
                                                                }
                                                            }
                                                        </script>
                                                    </form>

                                                    @if(auth()->user()->providerInfo)
                                                        <form class="inline-block" action="{{route('provider.update-approve-own')}}"
                                                              method="POST">
                                                            {{csrf_field()}}
                                                            <input class="btn btn-sm btn-default" aria-label="..."
                                                                   type="submit"
                                                                   value="@if(auth()->user()->providerInfo->approve_own_care_plans)Approve all practice patients @else Approve my patients only @endif">
                                                        </form>
                                                    @endif
                                                @endif
                                            @endif

                                            <a class="btn btn-info btn-sm inline-block" aria-label="..."
                                               role="button"
                                               href="{{ route('patients.careplan.multi') }}?users={{ $patient->id }}">Print
                                                This Page</a>

                                            <form class="lang" action="#" method="POST" id="form">
                                                {{ csrf_field() }}
                                                <input type="hidden" name="lang" value="es"/>
                                                <!-- <button type="submit" class="btn btn-info btn-sm text-right" aria-label="..." value="">Translate to Spanish</button>
                                -->       </form>
                                        </pdf-careplans>
                                    @endif
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
                        <div class="col-xs-5 print-row text-bold">{{$patient->getFullName()}}
                            (DOB: {{$patient->patientInfo->dob()}})
                        </div>
                        <div class="col-xs-3 print-row">{{$patient->getPhone()}}</div>
                        <div class="col-xs-4 print-row text-right">{{$today}}</div>
                    </div>

                    <div class="row gutter">
                        @if($billingDoctor)
                            <div class="col-xs-5 print-row text-bold">
                                {!! $regularDoctor->getDoctorFullNameWithSpecialty() !!}
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
                        <div class="col-xs-4 print-row text-bold text-right">{{$patient->getPreferredLocationName()}}</div>
                    </div>


                    @if($regularDoctor)
                        <div class="row gutter">
                            <div class="col-xs-5 print-row text-bold">
                                {!! $regularDoctor->getDoctorFullNameWithSpecialty() !!}
                            </div>
                            <div class="col-xs-3 print-row">
                                {{$regularDoctor->getPhone()}}
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
                <div id="care-team" class="patient-info__subareas">
                    <div class="row">
                        <div class="col-xs-12">
                            <h2 id="care-team-label"
                                class="patient-summary__subtitles patient-summary--careplan-background">Care Team:</h2>
                        </div>
                        <div class="col-xs-12">
                            @include('wpUsers.patient.careplan.print.careteam')
                        </div>
                    </div>
                </div>
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
        @include('partials.confirm-modal')

        @if(!isset($isPdf))
            @push('styles')
                <script>
                    var careplan = (<?php
                        echo json_encode($careplan);
                        ?>) || {};
                </script>
            @endpush
        @endif

        @if ($recentSubmission)
            @push('scripts')

                <script type="text/html" name="ccm-enrollment-submission">
                    <ol type="1">
                        <li>You must go over careplan with patient, then print it and hand to patient</li>
                        <li>To edit plan, click "Edit Care Plan" top center button.</li>
                    </ol>
                    <style>
                        #confirm-modal ul {
                            margin-bottom: 30px;
                        }

                        #confirm-modal li {
                            list-style-type: circle;
                            line-height: 30px;
                            margin-bottom: 10px;
                            font-size: 18px;
                        }
                    </style>
                </script>
                <script>
                    $.showConfirmModal({
                        title: 'Remember:',
                        body: document.querySelector('[name="ccm-enrollment-submission"]').innerHTML,
                        confirmText: 'Got it',
                        noCancel: true
                    }).then((patientHasConsented) => {

                    })
                </script>
            @endpush
        @endif
    @endif
@stop