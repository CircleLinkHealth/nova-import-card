@extends('partials.adminUI')

@section('content')
    @push('styles')
        <style>
            .ops-dboard-title {
                background-color: #eee;
                padding: 2rem;
            }
        </style>
    @endpush
    <div class="col-md-12">
        @include('admin.opsDashboard.panel')
    </div>
    <div class="input-group input-group-sm">
        <div>
            <form action="">
                <br>
                <p>Time frame for Added/Paused/Withdrawn/DELTA:</p>
                From:
                <input type="date" name="fromDate" value="{{$fromDate->toDateString()}}" required>
                To:
                <input type="date" name="toDate" value="{{$toDate->toDateString()}}" required>

                <br>
                <input align="center" type="submit" value="Submit">
            </form>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            Patient List from {{$fromDate->toDateString()}} to {{$toDate->toDateString()}}.
        </div>
        <div class="panel-body">
            <table class="table">
                <tr>
                    <th>Name</th>
                    <th>DOB</th>
                    <th>Practice Name</th>
                    <th>Status</th>
                    <th>Date Registered</th>
                    <th>Date Paused/Withdrawn</th>
                </tr>
                @foreach($patients as $patient)
                    <tr>
                        <td>{{$patient->display_name}}</td>
                        <td>{{$patient->patientInfo->birth_date}}</td>
                        <td>{{$patient->getPrimaryPracticeNameAttribute()}}</td>
                        <td>{{$patient->patientInfo->ccm_status}} @if($patient->carePlan) @if($patient->carePlan->status == \App\CarePlan::TO_ENROLL)  (G0506 Hold) @endif @endif</td>
                        <td>{{$patient->registration_date}}</td>
                        @if($patient->patientInfo->ccm_status == 'paused')
                            <td>Paused: {{$patient->patientInfo->date_paused}}</td>
                        @elseif($patient->patientInfo->ccm_status == 'withdrawn')
                            <td> Withdrawn: {{$patient->patientInfo->date_withdrawn}}</td>
                        @else
                            <td> --</td>
                        @endif

                    </tr>

                @endforeach

            </table>

            {!! $patients->appends(Input::except('page'))->links() !!}
        </div>
    </div>

@endsection