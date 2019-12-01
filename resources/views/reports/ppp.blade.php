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
        <link href="https://fonts.googleapis.com/css?family=Poppins" rel="stylesheet" type="text/css">
        <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">
        <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet" type="text/css">

        <!-- found in surveysMaster but for some reason dompdf has issues with it -->
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
        <link href="{{ asset('css/pdf.css') }}" rel="stylesheet">
    @endif

    <link href="{{ asset('css/pppReport.css') }}" rel="stylesheet">

    <div class="container report">

        <!-- this magic line here is needed for the pdf generation -->
        <!-- it turns out that if the first character is <strong>, the rest of the document is <strong> -->
        <!-- so I added this before "Patient Info" which is <strong> -->
        <div>&nbsp;</div>
        <div class="report-title">
            <div><strong>Patient Info</strong></div>
            <hr/>
        </div>
        <div class="report-data">
            Patient Name: <strong><span style="color: #50b2e2">{{$patient->display_name}}</span></strong> <br>
            Date of Birth (records): <strong>{{$patient->patientInfo->dob()}}</strong><br>
            Age (self-reported): <strong>{{getStringValue($patientPppData->answers_for_eval['age'])}}</strong> <br>
            Address: <strong>{{$patient->address}}</strong> <br>
            City, State, Zip: <strong>{{$patient->city}}, {{$patient->state}}, {{$patient->zip}}</strong> <br>
            Provider: <strong>{{$patient->getBillingProviderName()}}</strong>
        </div>

        <div class="report-title">
            <div><strong>Vitals</strong></div>
            <hr/>
        </div>
        <div class="report-data">
            Weight: <strong>{{getStringValue($patientPppData->answers_for_eval['weight'])}} </strong><br>
            Height: <strong>{{getStringValue($patientPppData->answers_for_eval['height']['feet'])}}
                ' {{getStringValue($patientPppData->answers_for_eval['height']['inches'])}}" </strong><br>
            Body Mass Index (BMI): <strong>{{getStringValue($patientPppData->answers_for_eval['bmi'])}}</strong> <br>
            Blood Pressure:
            <strong>
                {{getStringValue($patientPppData->answers_for_eval['blood_pressure']['first_metric'])}}
                &nbsp;/&nbsp;
                {{getStringValue($patientPppData->answers_for_eval['blood_pressure']['second_metric'])}}
            </strong><br>
        </div>

        <div class="suggested-list">
            <span class="report-title no-border no-margin">
                <strong>Suggested Checklist</strong>
            </span>
            <span class="side-title">
                Ask your doctor about:
            </span>
            <hr/>
        </div>

        <table class="table table-borderless">
            <thead>
            <tr>
                <th class="table-col-1"><span style="color: #50b2e2">Task Recommendation</span></th>
                <th class="table-col-2"><span style="color: #50b2e2">Time Frame</span></th>
                <th class="table-col-3"><span style="color: #50b2e2">Billing Code</span></th>
            </tr>
            </thead>
            <tbody>

            <?php
            $codeWithText = '99498 (if same day as AWV, bill w/ mod. 33 on same claim and Dr. as AWV)';
//            $emphasizedCodeText = '(if same day as AWV, bill w/ mod. 33 on same claim and Dr. as AWV)';
            $emphasisedBody = '(NOTE: $0 co-pay if done during AWV)';
            ?>

            @foreach($personalizedHealthAdvices as $key => $tasks)
                @if(! empty($tasks['tasks']))
                    @foreach($tasks['table_data'] as $table)
                        <tr>
                            {{--Emphasize code 99498 and the related body --}}
                            @if($table[0]['emphasize_code'])
                                <td class="suggested-list-body">
                                    <?php
                                    echo $table[0]['body']
                                    ?>
                                </td>

                                <td>{{$table[0]['time_frame']}}</td>

                                <td style="font-weight: 500">
                                    <?php
                                    echo $table[0]['code']
                                    ?>
                                </td>
                            @else
                                <td class="suggested-list-body">
                                    <strong>{{$table[0]['body']}}</strong>
                                </td>
                                <td>{{$table[0]['time_frame']}}</td>
                                <td style="font-weight: 500">{{$table[0]['code']}}</td>
                            @endif
                        </tr>
                    @endforeach
                @endif
            @endforeach
            </tbody>
        </table>
        <br>
        <div class="row">
            <div class="col">

                <div class="report-title page-break-before">
                    <strong>
                        Personalized Health Advice
                    </strong>
                    <hr/>
                </div>

                @foreach($personalizedHealthAdvices as $key => $tasks)
                    @empty(array_filter($tasks['tasks']))
                        @continue
                    @else
                        <div class="avoid-page-break">
                            <div class="recommendation-title avoid-page-break">
                                <div class="image {{$tasks['image']}}"></div>
                                <strong>
                                    {{$tasks['title']}}
                                </strong>
                            </div>
                            <br>
                            @foreach($tasks['tasks'] as $key => $recommendations)
                                @if(! empty($recommendations) && isset($recommendations['qualitative_trigger']))
                                    <div class="recommendations-area">
                                        <div><strong>{{$recommendations['qualitative_trigger']}}</strong></div>
                                        <div>{{$recommendations['task_body']}}</div>
                                        <br>
                                        @if (is_array($recommendations['recommendation_body']))
                                            <ul>
                                                @foreach($recommendations['recommendation_body'] as $recBodyItem)
                                                    <li style="font-weight: 400; margin-left: 7%;">
                                                        <i>{{$recBodyItem}}</i>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <ul>
                                                <li style="font-weight: 400; margin-left: 7%;">
                                                    <i>{{$recommendations['recommendation_body']}}</i>
                                                </li>
                                            </ul>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endempty
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
        margin-top: 4px;
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
