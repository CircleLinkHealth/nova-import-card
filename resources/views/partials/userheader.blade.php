<div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12" style="padding-bottom:9px">
    <div class="row">
        <div class="col-sm-12 col-xs-12">
            <div class="col-sm-8" style="line-height: 22px;">
                <span style="font-size: 30px;"> <a
                            href="{{ route('patient.summary', array('patient' => $patient->id)) }}">
                    {{$patient->getFullName()}}
                    </a> </span>
                @if($ccm_complex)
                    <span id="complex_tag"
                          style="background-color: #ec683e;font-size: 15px; position: relative; top: -7px;"
                          class="label label-warning">Complex CCM</span>
                    @push('scripts')
                        <script>
                            (function () {
                                // subscribe to jQuery event to know whether the complex-cscm checkbox value has been changed or not
                                var $complexSpan = $("#complex_tag");
                                $(document).on("complex-ccm-form-submit", function (e, status) {
                                    if (status) $complexSpan.show();
                                    else $complexSpan.hide();
                                })
                            })()
                        </script>
                    @endpush
                @endif
                <a href="{{ route('patient.demographics.show', array('patient' => $patient->id)) }}"><span
                            class="glyphicon glyphicon-pencil" style="margin-right:3px;"></span></a><br/>

                <ul class="inline-block" style="margin-left: -40px; font-size: 16px">
                    <b>
                        <li class="inline-block">{{$patient->getBirthDate() ?? 'N/A'}} <span
                                    style="color: #4390b5">•</span>
                        </li>
                        <li class="inline-block">{{$patient->getGender() ?? 'N/A'}} <span style="color: #4390b5">•</span>
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
                        <li class="inline-block"><b>Alternate Contact</b>: <span
                                    title="{{$patient->getAgentEmail()}}">({{$patient->getAgentRelationship()}}
                                ) {{$patient->getAgentName()}} {{$patient->getAgentPhone()}}</span></li>
                        <li class="inline-block"></li>
                    @endif
                    <li>
                        <patient-next-call
                                :patient-id="{{json_encode($patient->id, JSON_HEX_QUOT)}}"
                                :patient-preferences="{{json_encode($patient->patientInfo()->exists() ? $patient->patientInfo->getPreferences() : new stdClass,JSON_HEX_QUOT)}}"
                                :is-care-center="{{json_encode(Auth::user()->hasRole('care-center')), JSON_HEX_QUOT}}">
                        </patient-next-call>
                    </li>
                </ul>

            </div>
            <div class="col-sm-4" style="line-height: 22px; text-align: right">

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
                                    <a href="{{ empty($patient->id) ?: route('patient.activity.providerUIIndex', ['patient' => $patient->id]) }}">
                                        {{$monthlyTime}}
                                    </a>
                                    <span style="display:none">
                                        <time-tracker ref="TimeTrackerApp"
                                                      :twilio-enabled="@json(config('services.twilio.enabled') && (isset($patient) && $patient->primaryPractice ? $patient->primaryPractice->isTwilioEnabled() : true))"
                                                      class-name="{{$noLiveCountTimeTracking ? 'color-grey' : ($ccmCountableUser ? '' : 'color-grey')}}"
                                                      :info="timeTrackerInfo"
                                                      :no-live-count="{{($noLiveCountTimeTracking ? true : ($ccmCountableUser ? false : true)) ? 1 : 0}}"
                                                      :override-timeout="{{config('services.time-tracker.override-timeout')}}"></time-tracker>
                                    </span>
                                </div>
                            @else
                                <time-tracker ref="TimeTrackerApp"
                                              class-name="{{$noLiveCountTimeTracking ? 'color-grey' : ($ccmCountableUser ? '' : 'color-grey')}}"
                                              :twilio-enabled="@json(config('services.twilio.enabled') && (isset($patient) && $patient->primaryPractice ? $patient->primaryPractice->isTwilioEnabled() : true))"
                                              :info="timeTrackerInfo"
                                              :no-live-count="{{($noLiveCountTimeTracking ? true : ($ccmCountableUser ? false : true)) ? 1 : 0}}"
                                              :override-timeout="{{config('services.time-tracker.override-timeout')}}">
                                        @include('partials.tt-loader')
                                </time-tracker>
                            @endif
                        @endif
                    </span>
                </span>

                <span style="font-size:15px"></span><br/>

                @if(Route::is('patient.note.create'))
                    <li class="inline-block">
                        <select id="status" name="status" class="selectpickerX dropdownValid form-control" data-size="2"
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
                                    value="paused" {{$patient->getCcmStatus() == 'paused' ? 'selected' : ''}}> Paused
                            </option>
                        </select>
                    </li>
                @else
                    <li style="font-size: 18px" id="status"
                        class="inline-block {{$patient->getCcmStatus()}}"><?= (empty($patient->getCcmStatus()))
                            ? 'N/A'
                            : ucwords($patient->getCcmStatus()); ?></li>
                @endif
                <br/>
                @if(auth()->user()->hasRole(['administrator']))
                    @include('partials.viewCcdaButton')
                @endif

            </div>

        </div>
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
            <ul id="user-header-problems-checkboxes" class="person-conditions-list inline-block text-medium"
                style="margin-top: -10px">
                @foreach($ccdMonitoredProblems as $problem)
                    @if($problem['name'] != 'Diabetes')
                        <li class="inline-block"><input type="checkbox" id="item27" name="condition27" value="Active"
                                                        checked="checked" disabled="disabled">
                            <label for="condition27"><span> </span>{{$problem['name']}}</label>
                        </li>
                    @endif
                @endforeach
            </ul>
        @endif
    </div>
</div>

<meta name="is_ccm_complex" content="{{$ccm_complex}}">

@push('scripts')
    <script>
        $(document).ready(function () {

            if ($('meta[name="is_ccm_complex"]').attr('content')) {
                $("#complex_tag").show();
            } else {
                $("#complex_tag").hide();
            }

        });

    </script>
@endpush

@push('styles')
    <style>
        .color-grey {
            color: #7b7d81;
        }
    </style>
@endpush



