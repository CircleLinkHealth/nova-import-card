@extends('layouts.provider')

@section('title', 'Call Patient Page')
@section('activity', 'Call Patient Page')

@section('app')

    <div class="container">
        <div class="row">
            <div class="main-form-container col-lg-8 col-lg-offset-2">
                <div class="row">
                    <div class="main-form-title col-lg-12">
                        Patient Call Page
                    </div>

                    <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12"
                         style="padding-bottom:9px">
                        <div class="row">
                            <div class="col-sm-12" style="line-height: 22px;">
                                <div class="col-sm-12"
                                     style="margin-left: -20px; font-size: 30px;"> {{$patient->getFullName()}}
                                </div>

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
                                </ul>
                            </div>

                            <div class="col-sm-12">
                                <?php
                                $noLiveCountTimeTracking = isset($noLiveCountTimeTracking) && $noLiveCountTimeTracking;
                                $ccmCountableUser        = auth()->user()->isCCMCountable();
                                ?>
                                <time-tracker ref="TimeTrackerApp"
                                              :twilio-enabled="true"
                                              class-name="{{$noLiveCountTimeTracking ? 'color-grey' : ($ccmCountableUser ? '' : 'color-grey')}}"
                                              :info="timeTrackerInfo"
                                              :no-live-count="@json(($noLiveCountTimeTracking ? true : ($ccmCountableUser ? false : true)) ? true : false)"
                                              :override-timeout="{{config('services.time-tracker.override-timeout')}}"></time-tracker>

                            </div>

                            <div class="col-sm-12">
                                <call-number
                                        :debug="@json(!isProductionEnv())"
                                        cpm-caller-url="{{config('services.twilio.cpm-caller-url')}}"
                                        cpm-token="{{$cpmToken}}"
                                        from-number="{{$patient->primaryProgramPhoneE164()}}"
                                        :allow-conference="@json(config('services.twilio.allow-conference'))"
                                        inbound-user-id="{{$patient->id}}"
                                        outbound-user-id="{{auth()->id()}}"
                                        source="patient-call-page"
                                        clinical-escalation-number="{{$clinicalEscalationNumber}}">
                                </call-number>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
