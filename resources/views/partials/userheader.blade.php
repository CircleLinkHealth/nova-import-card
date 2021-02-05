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
            <div class="col-sm-7" style="line-height: 22px;">
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

                @if ($patient->shouldShowRpmBadge())
                    <h4 style="display: inline">
                        <span class="label label-success with-tooltip"
                              data-placement="top"
                              title="This patient is eligible for RPM reimbursement."
                              style="vertical-align: top; margin-right: 3px">
                            RPM
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
                    @if (isset($consecutiveUnsuccessfulCallCount) && isset($consecutiveUnsuccessfulCallLimit) && isset($consecutiveUnsuccessfulCallColor))
                    <li style="color: {{$consecutiveUnsuccessfulCallColor}};">
                        <b>Consecutive Unsuccessful Calls</b>
                        <div data-tooltip="The patient’s status will turn to unreachable and they will be removed from your list after the {{$consecutiveUnsuccessfulCallLimit}}th consecutive unsuccessful call." style="display: inline;">
                            <span class="glyphicon glyphicon-info-sign"></span>
                        </div>: {{$consecutiveUnsuccessfulCallCount}}/{{$consecutiveUnsuccessfulCallLimit}}
                    </li>
                    @endif
                    <li>
                        @if($isAdminOrPatientsAssignedNurse)
                            <patient-next-call
                                    :patient-id="{{json_encode($patient->id, JSON_HEX_QUOT)}}"
                                    :patient-preferences="{{json_encode($patient->patientInfo ? $patient->patientInfo->getPreferences() : new stdClass,JSON_HEX_QUOT)}}"
                                    :is-care-center="{{json_encode(Auth::user()->isCareCoach()), JSON_HEX_QUOT}}">
                            </patient-next-call>
                            <attest-call-conditions-modal
                                    patient-id="{{$patient->id}}"
                                    @if(isset($attestationRequirements)) :attestation-requirements="{{json_encode($attestationRequirements)}}" @endif
                            ></attest-call-conditions-modal>
                        @endif
                    </li>
                </ul>
                <?php

                $ccdProblemService = app(CircleLinkHealth\SharedModels\Services\CCD\CcdProblemService::class);

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
                                    <label @if(($problem['is_behavioral'] ?? false) && $enableBhiAttestation) class="bhi-problem"
                                           @endif for="item-{{$problem['id']}}"><span> </span>{{$problem['name']}}
                                    </label>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                @endif
            </div>
            <div class="col-lg-push-0 col-sm-5 col-sm-push-0 col-xs-5 col-xs-push-1"
                 style="line-height: 22px; text-align: right">

                <span style="font-size: 27px;">
                    @include('partials.providerUItimerComponent')
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



