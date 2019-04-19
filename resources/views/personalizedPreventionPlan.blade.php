@extends('surveysMaster')

@section('content')
    <link href="{{asset('css/providerReport.css')}}" rel="stylesheet">
    <div class="container report">
        <div class="report-title">
            <h3>Patient Info</h3>
            <hr>
        </div>
        <div>
            Patient Name: <span style="color: #50b2e2">{{$reportData['display_name']}}</span> <br>
            Date of Birth: <strong>{{$reportData['birth_date']}} </strong><br>
            Age: <strong>{{$age}}</strong> <br>
            Address: <strong>{{$reportData['address']}}</strong> <br>
            City, State, Zip: <strong>{{$reportData['city']}}, {{$reportData['state']}}, 'GET ZIP'</strong> <br>
            Provider: <strong>{{$reportData['billing_provider']}}</strong>
        </div>
        <div class="report-title">
            <br>
            <h3>Vitals</h3>
            <hr>
        </div>
        <div>
            Weight: <strong>160 </strong><br>
            Height: <strong>345 </strong><br>
            Body Mass Index (BMI): <strong>20</strong> <br>
            Blood Pressure: <strong>16/8</strong> <br>
        </div>
        <br>
        <div class="row">
            <div class="col">
                <div class="report-title">
                    <h3>Suggested CheckList</h3>
                </div>
            </div>
            <div class="col" style="padding-top: 1%">
                Ask your doctor about:
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col">
                <span style="color: #50b2e2">Task Recommendation</span>
            </div>
            <div class="col">
                <span style="color: #50b2e2">Follow-up Dates</span>
            </div>
            <div class="col">
                <span style="color: #50b2e2">Billing Code</span>
            </div>
        </div>
    </div>

    {{$reportData['recommendation_tasks']['nutrition_recommendations'][0]['task_body']}}
@endsection