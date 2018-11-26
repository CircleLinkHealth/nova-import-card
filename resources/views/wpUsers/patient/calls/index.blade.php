@extends('layouts.provider')

@section('title', 'Call Patient Page')
@section('activity', 'Call Patient Page')

@section('app')
    @push('scripts')
        <script src="https://media.twiliocdn.com/sdk/js/client/v1.6/twilio.min.js"></script>
    @endpush

    <div class="container">
        <div class="row">
            <div class="main-form-container col-lg-4 col-lg-offset-4">
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
                                    @if($patient->getAgentName())
                                        <li class="inline-block"><b>Alternate Contact</b>: <span
                                                    title="{{$patient->getAgentEmail()}}">({{$patient->getAgentRelationship()}}
                                                ) {{$patient->getAgentName()}} {{$patient->getAgentPhone()}}</span></li>
                                        <li class="inline-block"></li>
                                    @endif
                                </ul>
                            </div>

                            <div class="col-sm-12">
                                @if($phoneNumbers->isNotEmpty())
                                    <call-number
                                            :numbers="{{$phoneNumbers}}">

                                    </call-number>
                                @else
                                    <p>No phone numbers found</p>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection