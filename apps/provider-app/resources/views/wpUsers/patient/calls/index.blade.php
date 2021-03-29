@extends('layouts.provider')

@section('title', 'Initiate Call Page')
@section('activity', 'Initiate Call Page')

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
                                @include('partials.providerUItimerComponent')
                            </div>

                            <div class="col-sm-12">
                                <call-number
                                        :debug="@json($allowNonUsPhones)"
                                        :allow911-calls="@json(config('twilio-notification-channel.allow-911-calls'))"
                                        cpm-caller-url="{{config('twilio-notification-channel.cpm-caller-url')}}"
                                        cpm-token="{{$cpmToken}}"
                                        from-number="{{$patient->primaryProgramPhoneE164()}}"
                                        :allow-conference="@json(config('twilio-notification-channel.allow-conference'))"
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
