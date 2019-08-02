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
    <link href="{{asset('css/PersonalizedPreventionPlan.css')}}" rel="stylesheet">
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
            <div class="report-title col-md-6">
                <h3>Suggested CheckList</h3>
            </div>
            <div class="side-title col-md-6">
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
                            <td class="suggested-list-body">{{$table['body']}}</td>
                            <td>{{$table['time_frame']}}</td>
                            <td style="font-weight: 500">{{$table['code']}}</td>
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
                            <span>{{$tasks['title']}}</span>
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
                                        <div style="font-weight: 400"><i>{{$recBodyItem}}</i>
                                        </div>
                                    @endforeach
                                @else
                                    <div style="font-weight: 400"><i>{{$recommendations['recommendation_body']}}</i>
                                    </div>
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

