@extends('cpm-admin::partials.adminUI')

@section('content')
    @push('styles')
        <style>
            .ops-dboard-title {
                background-color: #eee;
                padding: 2rem;
            }
        </style>
    @endpush
    <div class="panel panel-default">
        <div class="panel-heading">Patient List @if(!$to)for {{$date->toDateString()}} @elseif($to)
                from {{$date->toDateString()}} to {{$to->toDateString()}} @endif @if($practice) for
            Practice: {{$practice->display_name}} @endif</div>
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
                        <td>{{$patient->getPrimaryPracticeName()}}</td>
                        <td>{{$patient->patientInfo->ccm_status}}</td>
                        <td>{{$patient->getRegistrationDate()}}</td>
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

            {!! $patients->appends(Request::except('page'))->links() !!}
        </div>
    </div>

@endsection