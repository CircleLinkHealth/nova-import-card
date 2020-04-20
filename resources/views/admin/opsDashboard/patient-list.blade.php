@extends('partials.adminUI')

@section('content')
    @push('styles')
        <style>
            .ops-dboard-title {
                background-color: #eee;
                padding: 2rem;
            }
            .nav-tabs > li, .nav-pills > li {
                float:none;
                display:inline-block;
                *display:inline; /* ie7 fix */
                zoom:1; /* hasLayout ie7 trigger */
            }

            .nav-tabs, .nav-pills {
                text-align:center;
            }

            /*.table td {*/
                /*text-align: center;*/
            /*}*/
        </style>
    @endpush
    <div class="container">
        <div class="col-md-12">
            @include('admin.opsDashboard.panel')
        </div>
        <div class="col-md-6">
            <div class="input-group">
                <div>
                    <form action="{{route('OpsDashboard.patientList')}}" method="GET">
                        <div class="form-group">
                            <p>Time frame for Added/Paused/Withdrawn/DELTA:</p>
                            From:
                            <input type="date" name="fromDate" value="{{$fromDate->toDateString()}}" max="{{$maxDate->copy()->subDay(1)->toDateString()}}"required>
                            To:
                            <input type="date" name="toDate" value="{{$toDate->toDateString()}}" max="{{$maxDate->toDateString()}}" required>
                        </div>
                        <div class="form-group">
                            Filter by Status:
                            <select name="status">
                                <option name="status" value="all">All</option>
                                <option name="status" value="enrolled">Added</option>
                                <option name="status" value="paused">Paused</option>
                                <option name="status" value="withdrawn">Withdrawn</option>
                            </select>
                        </div>
                        <div class="form-group">
                            Filter by Practice:
                            <select name="practice_id">
                                <option name="practice_id" value="all">All</option>
                                @foreach($practices as $practice)
                                <option name="practice_id" value="{{$practice->id}}">{{$practice->display_name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <input align="center" type="submit" value="Submit" class="btn btn-info">
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="input-group">
                <div>
                    <form action="{{route('OpsDashboard.makeExcel')}}" method="POST">
                        <br>
                        {{ csrf_field() }}
                        <input type="hidden" name="fromDate" value="{{$fromDate->toDateString()}}">
                        <input type="hidden" name="toDate" value="{{$toDate->toDateString()}}">
                        <input type="hidden" name="status" value="{{$status}}">
                        <input type="hidden" name="practice_id" value="{{$practiceId}}">
                        <input align="center" type="submit" value="Make Excel File" class="btn btn-info">
                        <br>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="panel panel-default">
            <div class="panel-heading">
                Patient List from {{$fromDate->toDateString()}} to {{$toDate->toDateString()}}.
            </div>
            <div class="panel-body">
                <table class="table table-striped table-bordered table-curved table-condensed table-hover">
                    <tr>
                        <th>Name</th>
                        <th>DOB</th>
                        <th>Practice Name</th>
                        <th>Status</th>
                        <th>Date Registered</th>
                        <th>Date Paused/Withdrawn</th>
                        <th>Enroller</th>
                    </tr>
                    @foreach($patients as $patient)
                        <tr>
                            <td>{{$patient->display_name}}</td>
                            <td>{{$patient->patientInfo->birth_date}}</td>
                            <td>{{$patient->getPrimaryPracticeName()}}</td>
                            <td>@if($patient->patientInfo->registration_date >= $fromDate->toDateTimeString() && $patient->patientInfo->registration_date <= $toDate->toDateTimeString() && $patient->patientInfo->ccm_status != 'enrolled')added - @endif {{$patient->patientInfo->ccm_status}} </td>
                            <td>{{$patient->patientInfo->registration_date}}</td>
                            @if($patient->patientInfo->ccm_status == 'paused')
                                <td>Paused: {{$patient->patientInfo->date_paused}}</td>
                            @elseif($patient->patientInfo->ccm_status == 'withdrawn')
                                <td> Withdrawn: {{$patient->patientInfo->date_withdrawn}}</td>
                            @else
                                <td> --</td>
                            @endif
                            <td> - </td>

                        </tr>

                    @endforeach

                </table>

                {!! $patients->appends(Request::except('page'))->links() !!}
            </div>
        </div>
    </div>


@endsection