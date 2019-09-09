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
    @endif

    <div class="container report">
        <div class="report-title">
            <h3>Patient Info</h3>
            <hr>
        </div>
        <div>
            Patient Name: <span style="color: #50b2e2">{{$patient->display_name}}</span> <br>
            Date of Birth: <strong>{{$patient->patientInfo->birth_date}}</strong><br>
            Age: <strong>{{getStringValue($patientPppData->answers_for_eval['age'])}}</strong> <br>
            Address: <strong>{{$patient->address}}</strong> <br>
            City, State, Zip: <strong>{{$patient->city}}, {{$patient->state}}, {{$patient->zip}}</strong> <br>
            Provider: <strong>{{$patient->getBillingProviderName()}}</strong>
        </div>
        <div class="report-title">
            <br>
            <h3>Vitals</h3>
            <hr>
        </div>
        <div>
            Weight: <strong>{{getStringValue($patientPppData->answers_for_eval['weight'])}} </strong><br>
            Height: <strong>{{getStringValue($patientPppData->answers_for_eval['height']['feet'])}}
                ' {{getStringValue($patientPppData->answers_for_eval['height']['inches'])}}' </strong><br>
            Body Mass Index (BMI): <strong>{{getStringValue($patientPppData->answers_for_eval['bmi'])}}</strong> <br>
            Blood Pressure:
            <strong>{{getStringValue($patientPppData->answers_for_eval['blood_pressure']['first_metric'])}}
                / {{getStringValue($patientPppData->answers_for_eval['blood_pressure']['second_metric'])}}</strong><br>
        </div>
        <br>
        <div class="suggested-list">
            <div class="report-title col-md-7 no-padding">
                <h3>Suggested CheckList</h3>
            </div>
            <div class="side-title col-md-5 no-padding">
                Ask your doctor about:
            </div>
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
                <div class="health-advice-title">
                    <h3>Personalized Health Advice</h3>
                </div>
                <hr>

                @foreach($personalizedHealthAdvices as $key => $tasks)
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
                                <div style="font-weight: 600">{{$recommendations['qualitative_trigger']}}</div>
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
                @endforeach
            </div>
        </div>
    </div>

@endsection

<style type="text/css" media="all">

    .section-title {
        font-family: Poppins;
        background-color: #50b2e2;
        font-size: 20px;
        font-weight: 600;
        font-style: normal;
        font-stretch: normal;
        line-height: normal;
        letter-spacing: 1.1px;
        padding-left: 25px;
        padding-top: 10px;
        padding-bottom: 5px;
        color: #ffffff;
    }

    .section-body {
        font-family: Poppins;
        padding-top: 20px;
    }

    .health-advice-title {
        font-family: Poppins;
        font-size: 24px;
        font-weight: 600;
        font-style: normal;
        font-stretch: normal;
        line-height: normal;
        letter-spacing: 1.3px;
        color: #1a1a1a;
    }

    .report {
        font-family: Poppins;
        padding-left: 5%;
        padding-right: 5%;
        padding-top: 5%;
        padding-bottom: 5%;
    }

    .recommendation-title {
        font-family: Poppins;
        font-size: 18px;
        font-weight: 600;
        font-style: normal;
        font-stretch: normal;
        line-height: normal;
        letter-spacing: 1px;
        color: #ffffff;
        padding: 1% 20px;
        background-color: #50b2e2;
    }

    .table.table td, table.table th {
        padding-top: 0.4rem;
        padding-bottom: 0.2rem;
        align-content: center;
    }

    .table.table thead th {
        padding-left: 1%;
        width: 250px;

    }

    .suggested-list {
        display: flex;
    }

    .report-title {

    }

    .side-title {
        padding-top: 1%;
    }

    .suggested-list-body {
        font-weight: 500;
    }

    .image {
        width: 20px;
        height: 20px;
        float: left;
        margin-top: 3px;
        margin-right: 10px;
        background-size: contain !important;
    }

    .image.carrot {
        background: url({{asset('/images/carrot@3x.png')}}) no-repeat;
        margin-top: 1px;
    }

    .image.cigarette {
        background: url({{asset('/images/cigarette@3x.png')}}) no-repeat;
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
        background: url({{asset('/images/weight-scale@3x.png')}}) no-repeat;
    }

    .image.hearts {
        background: url({{asset('/images/hearts@3x.png')}}) no-repeat;
    }

    .image.happy-face {
        background: url({{asset('/images/happy-face@3x.png')}}) no-repeat;
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
        background: url({{asset('/images/raised-hand@3x.png')}}) no-repeat;
    }

    .image.syringe {
        background: url({{asset('/images/syringe@3x.png')}}) no-repeat;
    }

    .image.clipboard-list {
        background: url({{asset('/images/clipboard-list@3x.png')}}) no-repeat;
    }

    .image.layout-4-blocks {
        background: url({{asset('/images/layout-4-blocks@3x.png')}}) no-repeat;
        margin-top: 3px;
    }
</style>
