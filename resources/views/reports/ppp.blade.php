@extends('layouts.surveysMaster')
<?php
function getStringValue($val, $default = '')
{
    if (empty($val)) {
        return $default;
    }

    if (is_string($val)) {
        return $val;
    }

    if (is_array($val)) {

        if (array_key_exists('name', $val)) {
            return getStringValue($val['name']);
        }

        if (array_key_exists('value', $val)) {
            return getStringValue($val['value']);
        }

        return getStringValue($val[0]);
    }

    return $val;
}
?>
@section('content')

    @if (isset($isPdf) && $isPdf)
        <!-- found in surveysMaster but for some reason dompdf has issues with it -->
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
        <link href="{{ asset('css/pdf.css') }}" rel="stylesheet">
    @endif

    <link href="{{ asset('css/pppReport.css') }}" rel="stylesheet">

    <div class="container report">

        <div class="report-title">
            <div>Patient Info</div>
            <hr>
        </div>
        <div class="report-data">
            Patient Name: <strong><span style="color: #50b2e2">{{$patient->display_name}}</span></strong> <br>
            Date of Birth (records): <strong>{{$patient->patientInfo->birth_date}}</strong><br>
            Age (self-reported): <strong>{{getStringValue($patientPppData->answers_for_eval['age'])}}</strong> <br>
            Address: <strong>{{$patient->address}}</strong> <br>
            City, State, Zip: <strong>{{$patient->city}}, {{$patient->state}}, {{$patient->zip}}</strong> <br>
            Provider: <strong>{{$patient->getBillingProviderName()}}</strong>
        </div>

        <div class="report-title">
            <div>Vitals</div>
            <hr>
        </div>
        <div class="report-data">
            Weight: <strong>{{getStringValue($patientPppData->answers_for_eval['weight'])}} </strong><br>
            Height: <strong>{{getStringValue($patientPppData->answers_for_eval['height']['feet'])}}
                ' {{getStringValue($patientPppData->answers_for_eval['height']['inches'])}}" </strong><br>
            Body Mass Index (BMI): <strong>{{getStringValue($patientPppData->answers_for_eval['bmi'])}}</strong> <br>
            Blood Pressure:
            <strong>{{getStringValue($patientPppData->answers_for_eval['blood_pressure']['first_metric'])}}
                / {{getStringValue($patientPppData->answers_for_eval['blood_pressure']['second_metric'])}}</strong><br>
        </div>

        <div class="suggested-list">
            <span class="report-title">
                Suggested CheckList
            </span>
            <span class="side-title">
                Ask your doctor about:
            </span>
        </div>

        <hr>

        <table class="table table-borderless">
            <thead>
            <tr>
                <th scope="col"><span style="color: #50b2e2">Task Recommendation</span></th>
                <th scope="col"><span style="color: #50b2e2">Time Frame</span></th>
                <th scope="col"><span style="color: #50b2e2">Billing Code</span></th>
            </tr>
            </thead>
            <tbody>

            @foreach($personalizedHealthAdvices as $key => $tasks)
                @if(! empty($tasks['tasks']))
                    @foreach($tasks['table_data'] as $table)
                        <tr>
                            <td class="suggested-list-body">{{$table[0]['body']}}</td>
                            <td>{{$table[0]['time_frame']}}</td>
                            <td style="font-weight: 500">{{$table[0]['code']}}</td>
                        </tr>
                    @endforeach
                @endif
            @endforeach
            </tbody>
        </table>
        <br>
        <div class="row">
            <div class="col">
                @foreach($personalizedHealthAdvices as $key => $tasks)
                    <div class="avoid-page-break">

                        <!-- keeping this here so it sticks with the first item in case it goes over a page break -->
                        @if($loop->index === 0)
                            <div class="health-advice-title">
                                <div>Personalized Health Advice</div>
                            </div>
                            <hr>
                        @endif

                        @if(array_filter($tasks['tasks']))
                            <br>
                            <div class="recommendation-title">
                                <div class="image {{$tasks['image']}}"></div> {{$tasks['title']}}
                            </div>
                        @endif
                        <br>
                        @foreach($tasks['tasks'] as $key => $recommendations)
                            @if(! empty($recommendations) && isset($recommendations['qualitative_trigger']))
                                <div class="recommendations-area">
                                    <div><strong>{{$recommendations['qualitative_trigger']}}</strong></div>
                                    <div>{{$recommendations['task_body']}}</div>
                                    <br>
                                    @if (is_array($recommendations['recommendation_body']))
                                        @foreach($recommendations['recommendation_body'] as $recBodyItem)
                                            <ul>
                                                <li style="font-weight: 400; margin-left: 7%;"><i>{{$recBodyItem}}</i>
                                                </li>
                                            </ul>
                                        @endforeach
                                    @else
                                        <ul>
                                            <li style="font-weight: 400; margin-left: 7%;">
                                                <i>{{$recommendations['recommendation_body']}}</i>
                                            </li>
                                        </ul>
                                    @endif
                                    <br>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </div>

@endsection

<style type="text/css" media="all">
    .image.carrot {
        background: url({{asset('/images/carrot.svg')}}) no-repeat;
        margin-top: 1px;
    }

    .image.cigarette {
        background: url({{asset('/images/cigarette.svg')}}) no-repeat;
        margin-top: 11px;
    }

    .image.wine {
        background: url({{asset('/images/wine@3x.png')}}) no-repeat;
    }

    .image.flower-3 {
        background: url({{asset('/images/flower-3@3x.png')}}) no-repeat;
    }

    .image.shape {
        background: url({{asset('/images/shape@3x.png')}}) no-repeat;
    }

    .image.dumbell {
        background: url({{asset('/images/dumbell@3x.png')}}) no-repeat;
    }

    .image.weight-scale {
        background: url({{asset('/images/weight-scale.svg')}}) no-repeat;
    }

    .image.hearts {
        background: url({{asset('/images/hearts.svg')}}) no-repeat;
    }

    .image.happy-face {
        background: url({{asset('/images/happy-face.svg')}}) no-repeat;
    }

    .image.patch {
        background: url({{asset('/images/patch@3x.png')}}) no-repeat;
    }

    .image.volume-half {
        background: url({{asset('/images/volume-half@3x.png')}}) no-repeat;
        margin-top: 1px;
    }

    .image.thought-bubble {
        background: url({{asset('/images/thought-bubble@3x.png')}}) no-repeat;
    }

    .image.raised-hand {
        background: url({{asset('/images/raised-hand.svg')}}) no-repeat;
    }

    .image.syringe {
        background: url({{asset('/images/syringe.svg')}}) no-repeat;
    }

    .image.clipboard-list {
        background: url({{asset('/images/clipboard-list@3x.png')}}) no-repeat;
    }

    .image.layout-4-blocks {
        background: url({{asset('/images/layout-4-blocks@3x.png')}}) no-repeat;
        margin-top: 3px;
    }
</style>
