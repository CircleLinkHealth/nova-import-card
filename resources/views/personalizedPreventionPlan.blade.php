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
        <br>


        <div class="row">
            <div class="col">
                <div class="report-title">
                    <h3>Personalized Health Advice</h3>
                </div>
            </div>
        </div>


        {{--Recommendations Section--}}
        @foreach($recommendationTasks as $key => $recommendations)
            @foreach($recommendationTasks[$key] as $recommendations)
            <div class="col" style="padding-top: 1%">

            </div>
            <div class="recommendations-area">
                @if(! empty($recommendations))

                    {{$recommendations['task_body']}}
                @endif
                   @endforeach
                @endforeach

            </div>
    </div>

@endsection

<style>
    .recommendations-area {
        font-family: Poppins;
        font-size: 14px;
        font-weight: normal;
        font-style: normal;
        font-stretch: normal;
        line-height: normal;
        letter-spacing: 0.8px;
        text-align: justify;
        color: #1a1a1a;
    }
</style>