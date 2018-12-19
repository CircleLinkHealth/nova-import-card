@extends('partials.providerUI')

@section('title', 'Progress Report Review/Print')
@section('activity', 'Progress Report Review/Print')

<?php
$today = \Carbon\Carbon::now()->toFormattedDateString();
$provider = App\User::find($patient->getBillingProviderId());

function trim_bp($bp)
{
    $bp_ = explode('/', $bp);
    echo $bp_[0];
}
if (isset($patient)) {
    $seconds     = $patient->getCcmTime();
    $H           = floor($seconds / 3600);
    $i           = ($seconds / 60) % 60;
    $s           = $seconds % 60;
    $monthlyTime = sprintf('%02d:%02d:%02d', $H, $i, $s);
} else {
    $monthlyTime = '';
}
?>
@section('content')
    @push('styles')
        <style>
            div.pad-right-20 {
                padding-right: 25px;
            }
        </style>
    @endpush
    <div class="container">
        <section class="patient-summary">
            <div class="row" style="margin-top:60px;">
                <div class="patient-info__main" style="padding-left: 51px;">
                    @if(auth()->user()->hasRole(['administrator', 'med_assistant', 'provider']))
                        <div class="row">
                            <div class="col-xs-12 text-right pad-right-20">
                                <span style="font-size: 27px;">
                                    <span data-monthly-time="{{$monthlyTime}}" style="color: inherit">
                                        @if (isset($disableTimeTracking) && $disableTimeTracking)
                                            <div class="color-grey">
                                                <a href="{{ empty($patient->id) ?: route('patient.activity.providerUIIndex', ['patient' => $patient->id]) }}">
                                                    <server-time-display url="{{config('services.ws.server-url')}}"
                                                                         patient-id="{{$patient->id}}"
                                                                         provider-id="{{Auth::user()->id}}"
                                                                         value="{{$monthlyTime}}"></server-time-display>
                                                </a>
                                            </div>
                                        @else
                                            <?php
                                            $noLiveCountTimeTracking = isset($noLiveCountTimeTracking) && $noLiveCountTimeTracking;
                                            $ccmCountableUser = auth()->user()->isCCMCountable();
                                            ?>
                                            <time-tracker ref="TimeTrackerApp"
                                                          :twilio-enabled="@json(config('services.twilio.enabled') && (isset($patient) && $patient->primaryPractice ? $patient->primaryPractice->isTwilioEnabled() : true))"
                                                          class-name="{{$noLiveCountTimeTracking ? 'color-grey' : ($ccmCountableUser ? '' : 'color-grey')}}"
                                                          :info="timeTrackerInfo"
                                                          :no-live-count="{{($noLiveCountTimeTracking ? true : ($ccmCountableUser ? false : true)) ? 1 : 0}}"
                                                          :override-timeout="{{config('services.time-tracker.override-timeout')}}"></time-tracker>
                                        @endif
                                    </span>
                                </span>
                            </div>
                            <div class="col-xs-12 text-right hidden-print">
                                <span class="btn btn-group text-right">
                                    <a class="btn btn-info btn-sm inline-block" aria-label="..." role="button"
                                       href="javascript:window.print()">Print This Page</a>
                                </span>
                            </div>
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-xs-12">
                            <h1 class="patient-summary__title patient-summary__title_16 patient-summary--careplan patient-summary--progress-report">
                                Progress Report:</h1>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-md-4 print-row text-bold">{{$patient->getFullName()}}</div>
                        <div class="col-xs-12 col-md-4 print-row">{{$patient->getPhone()}}</div>
                        <div class="col-xs-12 col-md-3 print-row">{{$today}}</div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-md-4 print-row text-bold">
                            @if($provider)
                                {{$provider->getFullName()}}{{($provider->getSpecialty() == '')? '' : ', '. $provider->getSpecialty() }}
                            @else
                                <em>no lead contact</em>
                            @endif
                        </div>
                        <div class="col-xs-12 col-md-4 print-row">
                            @if($provider)
                                {{$provider->getPhone()}}
                            @endif
                        </div>
                        <div class="col-xs-12 col-md-4 print-row text-bold">{{$patient->getPreferredLocationName()}}</div>
                    </div>


                </div>
            </div>

            <div class="patient-info__subareas">
                <div class="row">
                    <div class="col-xs-12">
                        <h2 class="patient-summary__subtitles patient-summary--progress-report-background">We Are
                            Treating</h2>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <ul class="subareas__list">
                            @foreach($treating as $treat)
                                <li class='subareas__item inline-block col-xs-6 col-sm-3 print-row text-bold'>{{$treat}}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            <!-- /CARE AREAS -->
            <!-- TRACKING CHANGES -->
            <div class="patient-info__subareas">
                <div class="row">
                    <div class="col-xs-12">
                        <h2 class="patient-summary__subtitles patient-summary--progress-report-background">Tracking
                            Changes</h2>
                    </div>
                </div>
            </div>
            <!-- /TRACKING CHANGES -->
            @foreach($tracking_biometrics as $key => $value)
                @if($value['data'] != '')
                    <?php

                    $read = explode('/', $value['reading']);
                    $goal = explode('/', $key);
                    $yaxis_start = '';
                    $yaxis_end = '';
                    $yaxis_step = 'step:10,';
                    if ('Blood_Sugar' == $key) {
                        $yaxis_start = 'start:40,';
                        $yaxis_step  = 'step:20,';
                    } elseif ('Blood_Pressure' == $key) {
                        $yaxis_start = 'start:80,';
                    } elseif ('Weight' == $key) {
                        $yaxis_start = 'start:80,';
                        $yaxis_step  = 'step: ' . round(($value['max'] - 80) / 4, -1) . ',';
                    }
                    ?>

                    <div class="row">
                        <div class="col-xs-12 col-lg-12">
                            <div class="col-xs-12 col-sm-8 print-column">
                                <h4 class="patient-summary__info__title"><span
                                            class="{{strtolower($value['status'])}}">{{ucwords($value['status'])}}</span><span
                                            class=""> </span> {{str_replace('_',' ',$key)}}</h4>

                                <div class="row">
                                    <div class="col-xs-3 text-center" style="Zoom:75%">
                                        <div class="patient-summary__info {{strtolower($value['status'])}}">
                            <span><i class="icon--<?php if ('Weight' == $key) {
                                    if ('increased' == strtolower($value['status'])) {
                                        echo trim('grey-up');
                                    } elseif ('decreased' == strtolower($value['status'])) {
                                        echo trim('grey-down');
                                    } else {
                                        echo trim('unchanged');
                                    }
                                } else {
                                    echo trim(strtolower($value['status']));
                                } ?>"> </i></span>{{abs($value['change'])}}
                                            <span class="patient-summary__metrics">{{trim($value['unit'])}}</span>
                                        </div>
                                        <div class="patient-summary__info__legend">
                                            Change  <!-- Wks. -->
                                        </div>
                                    </div>
                                    <div class="col-xs-3 text-center" style="Zoom:75%">
                                        <div class="patient-summary__info">
                                            {{abs($value['lastWeekAvg'])}}
                                            <span class="patient-summary__metrics">{{trim($value['unit'])}}</span>
                                        </div>
                                        <div class="patient-summary__info__legend">Latest Weekly Avg.</div>
                                    </div>

                                    <div class="col-xs-3  text-center" style="Zoom:75%">
                                        <div class="patient-summary__info">
                                            @if($value['target'] != ''){{trim_bp($value['target'])}}
                                            <span class="patient-summary__metrics">{{trim($value['unit'])}}</span>
                                            @else
                                                N/A
                                            @endif
                                        </div>
                                        <div class="patient-summary__info__legend">Goal</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-4 col-sm-pull-2 col-xs-pull-2">
                                <div class="patient-summary__info__graph">
                                    <div id="chartDiv-mg/dL" style="width:360px;height:160px;margin:1px;"></div>

                                    <script>

                                        webix.ui({
                                            view: "chart",
                                            container: "chartDiv-mg/dL",
                                            type: "line",
                                            value: "#Reading#",
                                            radius: 0,
                                            borderless: true,
                                            padding: {
                                                left: 40,
                                                top: 0,
                                                bottom: 45,
                                                right: 0
                                            },
                                            preset: 'simple',
                                            xAxis: {
                                                template: "#Week#",
                                                step: 2,
                                                title: "Week"
                                            },
                                            yAxis: {
                                                start: 40, step: 20,                                                // title: "Reading",
                                                template: function (obj) {
                                                    return (obj % 10 ? "" : obj)
                                                }
                                            },
                                            tooltip: {
                                                template: "#Reading#"
                                            },
                                            eventRadius: 10,
                                            data: [
                                                {!! $value['data'] !!}

                                            ]
                                        });
                                    </script>
                                </div>
                            </div>
                        </div>
                    </div>

            @endif
        @endforeach
        <!-- MEDICATIONS -->
            <div class="patient-info__subareas ">
                <div class="row">
                    <div class="col-xs-12">
                        <h2 class="patient-summary__subtitles patient-summary--progress-report-background">
                            Taking Your Medications?</h2>
                    </div>
                </div>
                <div class="row medication-rating">
                    <div class="col-xs-12 col-sm-4">
                        <i class="icon--face icon--face--good">
                        </i>
                        <ul>
                            <li class='text-bold medication-rating__title'>Good</li>
                            @foreach($medications as $key => $value)
                                @if($value['Section'] == 'Better')
                                    @foreach($value['name'] as $section)
                                        <li>{{$section}}</li>
                                    @endforeach
                                @endif
                            @endforeach
                        </ul>
                    </div>
                    <div class="col-xs-12 col-sm-4">
                        <i class="icon--face icon--face--work">
                        </i>
                        <ul>
                            <li class='text-bold medication-rating__title'>Needs Work</li>
                            @foreach($medications as $key => $value)
                                @if($value['Section'] == 'Needs Work')
                                    @foreach($value['name'] as $section)
                                        <li>{{$section}}</li>
                                    @endforeach
                                @endif
                            @endforeach
                        </ul>
                    </div>
                    <div class="col-xs-12 col-sm-4">
                        <i class="icon--face icon--face--bad">
                        </i>
                        <ul>
                            <li class='text-bold medication-rating__title'>Bad</li>
                            @foreach($medications as $key => $value)
                                @if($value['Section'] == 'Worse')
                                    @foreach($value['name'] as $section)
                                        <li>{{$section}}</li>
                                    @endforeach
                                @endif
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <!-- /MEDICATIONS -->
        </section>
    </div>
    </div>
@stop