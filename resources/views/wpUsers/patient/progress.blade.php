@extends('partials.providerUI')

@section('title', 'Progress Report Review/Print')
@section('activity', 'Progress Report Review/Print')

<?php
$today = \Carbon\Carbon::now()->toFormattedDateString();
$provider = App\User::find($patient->getBillingProviderIDAttribute());
?>
@section('content')
    <div class="container">
        <section class="patient-summary">
            <div class="row" style="margin-top:60px;">
                <div class="patient-info__main" style="padding-left: 51px;">
                    <div class="row">
                        <div class="col-xs-12 text-right hidden-print">
					<span class="btn btn-group text-right">
					<A class="btn btn-info btn-sm inline-block" aria-label="..." role="button"
                       HREF="javascript:window.print()">Print This Page</A>
				</span></div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12">
                            <h1 class="patient-summary__title patient-summary__title_16 patient-summary--careplan patient-summary--progress-report">
                                Progress Report:</h1>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-md-4 print-row text-bold">{{$patient->fullName}}</div>
                        <div class="col-xs-12 col-md-4 print-row">{{$patient->phone}}</div>
                        <div class="col-xs-12 col-md-3 print-row">{{$today}}</div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-md-4 print-row text-bold">
                            @if($provider)
                                {{$provider->fullName}}{{($provider->getSpecialtyAttribute() == '')? '' : ', '. $provider->getSpecialtyAttribute() }}
                            @else
                                <em>no lead contact</em>
                            @endif
                        </div>
                        <div class="col-xs-12 col-md-4 print-row">
                            @if($provider)
                                {{$provider->phone}}
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
                    if ($key == 'Blood_Sugar') {
                        $yaxis_start = 'start:40,';
                        $yaxis_step = 'step:20,';
                    } else if ($key == 'Blood_Pressure') {
                        $yaxis_start = 'start:80,';
                    } else if ($key == 'Weight') {
                        $yaxis_start = 'start:80,';
                        $yaxis_step = 'step: ' . round(($value['max'] - 80) / 4, -1) . ',';
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
                                            <span><i class="icon--{{strtolower($value['status'])}}"> </i></span>{{abs($value['change'])}}
                                            <span class="patient-summary__metrics">{{trim($value['unit'])}}</span>
                                        </div>
                                        <div class="patient-summary__info__legend">
                                            Change  <!-- Wks. -->
                                        </div>
                                    </div>
                                    <div class="col-xs-3 text-center" style="Zoom:75%">
                                        <div class="patient-summary__info">
                                            {{abs($value['lastWeekAvg'])}} <span
                                                    class="patient-summary__metrics">{{trim($value['unit'])}}</span>
                                        </div>
                                        <div class="patient-summary__info__legend">
                                            Latest Weekly Avg.
                                        </div>
                                    </div>

                                    <div class="col-xs-3  text-center" style="Zoom:75%">
                                        <div class="patient-summary__info">
                                            @if($value['target'] != '')
                                                {{$value['target']}}
                                            @else
                                                N/A
                                            @endif
                                        </div>
                                            <div class="patient-summary__info__legend">
                                                Goal
                                            </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-4 col-sm-pull-2 col-xs-pull-2">
                                <div class="patient-summary__info__graph">
                                    <div id="chartDiv-mg/dL" style="width:360px;height:160px;margin:1px;"></div>
                                    <script src="http://sbcf.cpm.com/wp-content/themes/CLH_Provider/respo/webix/codebase/webix.js"
                                            type="text/javascript"></script>

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