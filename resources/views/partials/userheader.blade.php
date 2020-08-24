<div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12" style="padding-bottom:15px">
    <div class="row">
        <div class="col-sm-12 col-xs-12">
            @push('scripts')
                <script>

                    function onStatusChange(e) {
                        let ccmStatus = document.getElementById("ccm_status");

                        if (ccmStatus && (ccmStatus.value === "withdrawn" || ccmStatus.value === "withdrawn_1st_call")) {
                            $('#header-withdrawn-reason').removeClass('hidden');
                            onReasonChange();
                        } else {
                            $('#header-withdrawn-reason').addClass('hidden');
                            $('#header-withdrawn-reason-other').addClass('hidden');
                        }
                    }

                    function onReasonChange(e) {
                        let reason = document.getElementById("withdrawn_reason");
                        let reasonOther = document.getElementById('withdrawn_reason_other');

                        if (reason && reason.value === "Other") {
                            $('#header-withdrawn-reason-other').removeClass('hidden');
                            reasonOther.setAttribute('required', '');
                        } else {
                            $('#header-withdrawn-reason-other').addClass('hidden');
                            reasonOther.removeAttribute('required');
                        }
                    }

                    $('document').ready(function () {

                        const statusSelectEl = $('#header-perform-status-select');
                        statusSelectEl.on('change', onStatusChange);
                        statusSelectEl.change();

                        const reasonSelectEl = $('#header-perform-reason-select');
                        reasonSelectEl.on('change', onReasonChange);
                        reasonSelectEl.change();
                    });


                </script>
            @endpush
            <div class="col-sm-8" style="line-height: 22px;">
                <span style="font-size: 30px;">
                    <a href="{{ route('patient.summary', [$patient->id]) }}">
                    {{$patient->getFullName()}}
                    </a>
                </span>

                <a href="{{ route('patient.demographics.show', [$patient->id]) }}"
                   style="padding-right: 5px; vertical-align: top">
                    <span class="glyphicon glyphicon-pencil" style="margin-right:3px;"></span>
                </a>

                <span style="font-size: 15px;">
                    (Patient ID: {{$patient->id}})
                </span>

                @if ($patient->shouldShowCcmPlusBadge())
                    <h4 style="display: inline">
                        <span class="label label-success with-tooltip"
                              data-placement="top"
                              title="This patient is eligible for additional CCM reimbursements if CCM time is over 40 and/or 60 minutes"
                              style="vertical-align: top; margin-right: 3px">
                            CCM+
                        </span>
                    </h4>
                @endif

                @if ($patient->shouldShowPcmBadge())
                    <h4 style="display: inline">
                        <span class="label label-success with-tooltip"
                              data-placement="top"
                              title="This patient is eligible for PCM reimbursement, therefore minimum time is 30 minutes (instead of 20)."
                              style="vertical-align: top; margin-right: 3px">
                            PCM
                        </span>
                    </h4>
                @endif

                {{-- red flag.indication patient is BHI eligible--}}
                @if(isset($patient) && auth()->check()
                && !isset($isPdf)
                && auth()->user()->shouldShowBhiFlagFor($patient))
                    <button type="button"
                            class="glyphicon glyphicon-flag red bounce with-tooltip"
                            style="color: red; position: absolute;"
                            data-placement="top"
                            title="Patient is eligible for BHI. Click me for more info">
                    </button>

                    <div class="load-hidden-bhi" id="bhi-modal">
                        @include('partials.providerUI.bhi-notification-banner', ['user' => $patient])
                    </div>
                @endif

                <br/>

                <ul class="inline-block" style="margin-left: -40px; font-size: 16px">
                    <b>
                        <li class="inline-block">{{$patient->getBirthDate() ?? 'N/A'}} <span
                                    style="color: #4390b5">•</span>
                        </li>
                        <li class="inline-block">{{$patient->getGender() ?? 'N/A'}} <span
                                    style="color: #4390b5">•</span>
                        </li>
                        <li class="inline-block">{{$patient->getAge() ?? 'N/A'}} yrs <span
                                    style="color: #4390b5">•</span>
                        </li>
                        <li class="inline-block">{{formatPhoneNumber($patient->getPhone()) ?? 'N/A'}} </li>
                    </b>
                    <li style="margin-bottom: 10px">
                        <patient-spouse :patient-id="{{json_encode($patient->id, JSON_HEX_QUOT)}}"></patient-spouse>
                    </li>
                    <li><span> <b>Billing Dr.</b>: {{$provider}}  </span></li>
                    @if($regularDoctor)
                        <li><span> <b>Regular Dr.</b>: {{$regularDoctor->getFullName()}}  </span></li>
                    @endif
                    <li><span> <b>Practice</b>: {{$patient->primaryProgramName()}} </span></li>
                    @if($patient->getAgentName())
                        <li><b>Alternate Contact</b>: <span
                                    title="{{$patient->getAgentEmail()}}">({{$patient->getAgentRelationship()}}
                                ) {{$patient->getAgentName()}} {{$patient->getAgentPhone()}}</span></li>
                    @endif
                    @if (!empty($patient->patientInfo->general_comment))
                        <li>
                            <b>General comment</b>: {{$patient->patientInfo->general_comment}}
                        </li>
                    @endif
                    <li>
                        <patient-next-call
                                :patient-id="{{json_encode($patient->id, JSON_HEX_QUOT)}}"
                                :patient-preferences="{{json_encode($patient->patientInfo()->exists() ? $patient->patientInfo->getPreferences() : new stdClass,JSON_HEX_QUOT)}}"
                                :is-care-center="{{json_encode(Auth::user()->isCareCoach()), JSON_HEX_QUOT}}">
                        </patient-next-call>
                        <attest-call-conditions-modal
                                patient-id="{{$patient->id}}"
                                @if(isset($attestationRequirements)) :attestation-requirements="{{json_encode($attestationRequirements)}}" @endif
                        ></attest-call-conditions-modal>
                    </li>
                </ul>
                <?php

                $ccdProblemService = app(App\Services\CCD\CcdProblemService::class);

                $ccdProblems = $ccdProblemService->getPatientProblems($patient);

                $enableBhiAttestation = auth()->user()->isCareCoach() && (isset($patientIsBhiEligible) && true === $patientIsBhiEligible);
                $ccdMonitoredProblems = $ccdProblems
                    ->where('is_monitored', 1)
                    ->unique('name')
                    ->values();
                ?>
                @if(!empty($ccdMonitoredProblems))
                    <div style="clear:both"></div>
                    <ul id="user-header-problems-checkboxes"
                        class="person-conditions-list inline-block text-medium col-lg-12 col-md-10 col-xs-8"
                        style="margin-top: -8px; margin-bottom: 20px !important; margin-left: -20px !important;">
                        @foreach($ccdMonitoredProblems as $problem)
                            @if($problem['name'] != 'Diabetes')
                                <li
                                        @if(($problem['is_behavioral'] ?? false) && $enableBhiAttestation)
                                        title="BHI Condition: Switch to BHI timer when discussing with patient"
                                        class="with-bhi-tooltip bhi-problem inline-block"
                                                @else
                                                class="inline-block"
                                                @endif
                                ><input type="checkbox" id="item-{{$problem['id']}}"
                                                                name="item-{{$problem['id']}}"
                                                                value="Active"
                                                                checked="checked" disabled="disabled">
                                    <label @if(($problem['is_behavioral'] ?? false) && $enableBhiAttestation) class="bhi-problem" @endif for="item-{{$problem['id']}}"><span> </span>{{$problem['name']}}</label>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                @endif
            </div>
            <div class="col-lg-push-0 col-sm-4 col-sm-push-0 col-xs-4 col-xs-push-1"
                 style="line-height: 22px; text-align: right">

                <span style="font-size: 27px;{{$ccm_above ? 'color: #47beab;' : ''}}">
                    <span data-monthly-time="{{$monthlyTime}}" style="color: inherit">
                        @if (isset($disableTimeTracking) && $disableTimeTracking)
                            <div class="color-grey">
                                <a href="{{ empty($patient->id) ?: route('patient.activity.providerUIIndex', [$patient->id]) }}">
                                    <server-time-display url="{{config('services.ws.server-url')}}"
                                                         patient-id="{{$patient->id}}"
                                                         provider-id="{{auth()->id()}}"
                                                         value="{{$monthlyTime}}"></server-time-display>
                                </a>
                            </div>
                        @else
                            <?php
                            $noLiveCountTimeTracking = (isset($noLiveCountTimeTracking) && $noLiveCountTimeTracking);
                            $ccmCountableUser        = auth()->user()->isCCMCountable();
                            ?>
                            @if ($noLiveCountTimeTracking)
                                <div class="color-grey">
                                    <div>
                                        <div class="{{$monthlyBhiTime === '00:00:00' ? '' : 'col-md-6'}}">
                                            <div>
                                                <small>CCM</small>
                                            </div>
                                            <div>
                                                 <a id="monthly-time-static"
                                                    href="{{ empty($patient->id) ?: route('patient.activity.providerUIIndex', [$patient->id]) }}">
                                                    {{$monthlyTime}}
                                                </a>
                                            </div>
                                        </div>
                                        @if ($monthlyBhiTime !== '00:00:00')
                                            <div class="col-md-6">
                                                <div>
                                                    <small>BHI</small>
                                                </div>
                                                <div>
                                                     <a id="monthly-bhi-time-static"
                                                        href="{{ empty($patient->id) ?: route('patient.activity.providerUIIndex', [$patient->id]) }}">
                                                        {{$monthlyBhiTime}}
                                                     </a>
                                                </div>
                                        </div>
                                        @endif
                                    </div>

                                    <span style="display:none">
                                        <time-tracker ref="TimeTrackerApp"
                                                      :twilio-enabled="@json(config('services.twilio.enabled') && (isset($patient) && $patient->primaryPractice ? $patient->primaryPractice->isTwilioEnabled() : true))"
                                                      class-name="{{$noLiveCountTimeTracking ? 'color-grey' : ($ccmCountableUser ? '' : 'color-grey')}}"
                                                      :info="timeTrackerInfo"
                                                      :no-live-count="@json(($noLiveCountTimeTracking ? true : ($ccmCountableUser ? false : true)) ? true : false)"
                                                      :override-timeout="{{config('services.time-tracker.override-timeout')}}"></time-tracker>
                                    </span>
                                </div>
                            @else
                                <time-tracker ref="TimeTrackerApp"
                                              class-name="{{$noLiveCountTimeTracking ? 'color-grey' : ($ccmCountableUser ? '' : 'color-grey')}}"
                                              :twilio-enabled="@json(config('services.twilio.enabled') && (isset($patient) && $patient->primaryPractice ? $patient->primaryPractice->isTwilioEnabled() : true))"
                                              :info="timeTrackerInfo"
                                              :no-live-count="@json(($noLiveCountTimeTracking ? true : ($ccmCountableUser ? false : true)) ? true : false)"
                                              :override-timeout="{{config('services.time-tracker.override-timeout')}}">
                                        @include('partials.tt-loader')
                                </time-tracker>
                            @endif
                        @endif
                    </span>
                </span>

                <span class="sometimes-hidden" style="font-size:15px"></span>

                <div id="header-perform-status-select" class="ccm-status col-xs-offset-3">
                    @include('partials.patient.ccm-status')
                    <br/>
                </div>
                @include('partials.patient.withdrawn-reason')

                @if(auth()->user()->isAdmin())
                    @include('partials.viewCcdaButton')
                @endif
            </div>

        </div>
    </div>
</div>


@push('styles')
    <style>

        input[type=checkbox]:disabled + label,
        input[type=radio]:disabled + label {
            cursor: default;
            color: #5b5b5b
        }

        input[type=radio]:checked:disabled + label span {
            cursor: default;
            background: url(../img/ui/radio-active-disabled.png) left top no-repeat;
        }

        input[type=checkbox]:checked:disabled + label span {
            cursor: default;
            background: url(../img/ui/checkbox-active-disabled.png) left top no-repeat;
        }

        .color-grey {
            color: #7b7d81;
        }

        .load-hidden-bhi {
            display: none;
        }

        @-webkit-keyframes bounce {
            0% {
                transform: scale(1, 1) translate(0px, 0px);
            }

            30% {
                transform: scale(1, 0.8) translate(0px, 10px);
            }

            75% {
                transform: scale(1, 1.1) translate(0px, -25px);
            }

            100% {
                transform: scale(1, 1) translate(0px, 0px);
            }
        }

        .bounce {
            -webkit-animation: bounce 0.65s 4;
        }

        .bhi-tooltip {
            display: none;
            z-index: 9999999;
            position: absolute;
            border: 1px solid #333;
            background-color: #5cc0dd;
            border-radius: 5px;
            padding: 10px;
            color: #fff;
            font-size: 12px;
        }

        .bhi-problem {
            color: #5cc0dd !important;
            font-weight: bolder;
        }

    </style>

@endpush

@push('scripts')
    <script>
        window.enableBhiAttestation = @json([
    'patientIsBhiEligible' => $enableBhiAttestation
    ]);
        (function ($) {
            $('.glyphicon-flag').click(function (e) {
                $(".load-hidden-bhi, .modal-mask").show();
            });

            $('.with-bhi-tooltip')
                .hover(function () {
                    // Hover over code
                    var title = $(this).attr('title');

                    $(this)
                        .data('tipText', title)
                        .removeAttr('title');

                    $('<p class="bhi-tooltip"></p>')
                        .text(title)
                        .appendTo('body')
                        .fadeIn('slow');

                }, function () {
                    // Hover out code
                    $(this).attr('title', $(this).data('tipText'));
                    $('.bhi-tooltip').remove();
                })
                .mousemove(function (e) {
                    var mousex = e.pageX + 20; //Get X coordinates
                    var mousey = e.pageY + 10; //Get Y coordinates
                    $('.bhi-tooltip').css({top: mousey, left: mousex})
                });

        })(jQuery);
    </script>
@endpush



