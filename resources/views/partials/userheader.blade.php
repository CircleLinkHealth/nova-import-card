<div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12" style="padding-bottom:15px">
    <div class="row">
        <div class="col-sm-12 col-xs-12">
            @push('scripts')
                <script>

                    function onStatusChange(e) {

                        let ccmStatus = document.getElementById("ccm_status");

                        if (ccmStatus.value === "withdrawn") {
                            $('#header-withdrawn-reason').removeClass('hidden');
                            onReasonChange();
                            console.log('test');
                        } else {
                            $('#header-withdrawn-reason').addClass('hidden');
                            $('#header-withdrawn-reason-other').addClass('hidden');
                        }

                    }

                    function onReasonChange(e) {

                        let reason = document.getElementById("withdrawn_reason");
                        let reasonOther = document.getElementById('withdrawn_reason_other');

                        if (reason.value === "Other") {
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
                    <a href="{{ route('patient.summary', array('patient' => $patient->id)) }}">
                    {{$patient->getFullName()}}
                    </a> </span>

                <a href="{{ route('patient.demographics.show', array('patient' => $patient->id)) }}" style="padding-right: 2%;"><span
                            class="glyphicon glyphicon-pencil" style="margin-right:3px;"></span></a>

                {{-- red flag.indication patient is BHI eligible--}}
                @if(isset($patient) && auth()->check() && !isset($isPdf) && auth()->user()->shouldShowBhiFlagFor($patient))
                    <button type="button"
                            class="glyphicon glyphicon-flag red bounce"
                            style="color: red; position: absolute;"
                            data-toggle="tooltip"
                            data-placement="top"
                            title="Patient is eligible for BHI. Click me for more info">
                    </button>

                    <div class="load-hidden-bhi" id="bhi-modal">
                        @include('partials.providerUI.bhi-notification-banner', ['user' => $patient])
                    </div>
                @endif
                {{--If today is scheduled call day then just show banner--}}
                @if(isset($patient) && auth()->check() && !isset($isPdf) && auth()->user()->shouldShowBhiBannerIfPatientHasScheduledCallToday($patient))
                    @include('partials.providerUI.bhi-notification-banner', ['user' => $patient])
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
                    </li>
                </ul>
                <?php
                $ccdProblemService = app(App\Services\CCD\CcdProblemService::class);

                $ccdProblems = $ccdProblemService->getPatientProblems($patient);

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
                                <li class="inline-block"><input type="checkbox" id="item27" name="condition27"
                                                                value="Active"
                                                                checked="checked" disabled="disabled">
                                    <label for="condition27"><span> </span>{{$problem['name']}}</label>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                @endif
            </div>
            <div class="col-lg-push-0 col-sm-4 col-sm-push-0 col-xs-4 col-xs-push-3"
                 style="line-height: 22px; text-align: right">

                <span style="font-size: 27px;{{$ccm_above ? 'color: #47beab;' : ''}}">
                    <span data-monthly-time="{{$monthlyTime}}" style="color: inherit">
                        @if (isset($disableTimeTracking) && $disableTimeTracking)
                            <div class="color-grey">
                                <a href="{{ empty($patient->id) ?: route('patient.activity.providerUIIndex', ['patient' => $patient->id]) }}">
                                    <server-time-display url="{{config('services.ws.server-url')}}"
                                                         patient-id="{{$patient->id}}"
                                                         provider-id="{{auth()->id()}}"
                                                         value="{{$monthlyTime}}"></server-time-display>
                                </a>
                            </div>
                        @else
                            <?php
                            $noLiveCountTimeTracking = $useOldTimeTracker
                                ? true
                                : (isset($noLiveCountTimeTracking) && $noLiveCountTimeTracking);
                            $ccmCountableUser = auth()->user()->isCCMCountable();
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
                                                    href="{{ empty($patient->id) ?: route('patient.activity.providerUIIndex', ['patient' => $patient->id]) }}">
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
                                                        href="{{ empty($patient->id) ?: route('patient.activity.providerUIIndex', ['patient' => $patient->id]) }}">
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

                @if(auth()->user()->isAdmin())
                    @include('partials.viewCcdaButton')
                @endif
                <div id="header-perform-status-select" class="ccm-status col-xs-offset-3">
                    @if(Route::is('patient.note.create') || Route::is('patient.note.edit'))
                        <li class="inline-block">
                            <select id="ccm_status" name="ccm_status" class="selectpickerX dropdownValid form-control"
                                    data-size="2"
                                    style="width: 135px">
                                <option style="color: #47beab"
                                        value="enrolled" {{$patient->getCcmStatus() == 'enrolled' ? 'selected' : ''}}>
                                    Enrolled
                                </option>
                                <option class="withdrawn"
                                        value="withdrawn" {{$patient->getCcmStatus() == 'withdrawn' ? 'selected' : ''}}>
                                    Withdrawn
                                </option>
                                <option class="paused"
                                        value="paused" {{$patient->getCcmStatus() == 'paused' ? 'selected' : ''}}>
                                    Paused
                                </option>
                            </select>
                        </li>
                    @else
                        <li style="font-size: 18px" id="ccm_status"
                            class="inline-block col-xs-pull-1 {{$patient->getCcmStatus()}}"><?= (empty($patient->getCcmStatus()))
                                ? 'N/A'
                                : ucwords($patient->getCcmStatus()); ?></li>
                    @endif
                    <br/>
                </div>
                @if(Route::is('patient.note.create') || Route::is('patient.note.edit'))
                    <div id="header-withdrawn-reason" class="hidden" style="padding-top: 10px;">

                        <div class="col-md-12"
                             style="padding-right: 0 !important;">{!! Form::label('withdrawn_reason', 'Withdrawn Reason: ') !!}</div>
                        <div class="col-sm-12" style="padding-right: 0 !important;">
                            <div id="header-perform-reason-select">

                                {!! Form::select('withdrawn_reason', $withdrawnReasons, ! array_key_exists($patientWithdrawnReason, $withdrawnReasons) && $patientWithdrawnReason != null ? 'Other' : $patientWithdrawnReason, ['class' => 'selectpickerX dropdownValid form-control', 'style' => 'width:100%;']) !!}

                            </div>
                        </div>
                    </div>
                    <div id="header-withdrawn-reason-other" class="form-group hidden">
                        <div class="col-md-12" style="padding-right: 0 !important;">
                            <div>
                                <textarea id="withdrawn_reason_other" rows="1" cols="100" style="resize: none;"
                                          placeholder="Enter Reason..." name="withdrawn_reason_other"
                                          required="required"
                                          class="form-control">{{! array_key_exists($patientWithdrawnReason, $withdrawnReasons) ? $patientWithdrawnReason : null}}</textarea>
                            </div>
                        </div>
                    </div>
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

    </style>

@endpush

@push('scripts')
    <script>
        (function ($) {
            $('.glyphicon-flag').click(function (e) {
                $(".load-hidden-bhi, .modal-mask").show();
            });

        })(jQuery);
    </script>
@endpush



