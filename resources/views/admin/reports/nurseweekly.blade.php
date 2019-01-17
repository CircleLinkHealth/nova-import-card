@extends('partials.adminUI')
@section('content')
    @push('styles')
        <link rel="stylesheet" href="//cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css">
    @endpush

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                @include('errors.errors')
            </div>
        </div>

        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading text-center"><b>Nurse Weekly Report</b></div>
                <div class="panel-body">
                    @include('admin.reports.partials.dayFilterNav')
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>Nurse</th>
                           {{-- @foreach($days as $weekDay)
                                <th>{{$weekDay}}</th>
                                <th>{{$weekDay}}</th>
                                <th>{{$weekDay}}</th>
                                <th>{{$weekDay}}</th>
                                <th>{{$weekDay}}</th>
                                <th>{{$weekDay}}l</th>
                            @endforeach--}}
                            <th scope="col">Name</th>
                            <th scope="col">Scheduled Calls</th>
                            <th scope="col">Actual Calls</th>
                            <th scope="col">Successful Calls</th>
                            <th scope="col">Unsuccessful Calls</th>
                            <th scope="col">Committed Hours</th>
                            <th scope="col">Actual Calls</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($nurseData as $name => $dates)
                            <tr>
                                <td>{{$name}}</td>

                                @foreach($dates as $date)
                                    <td>{{$date->first()['actualCalls']}} </td>
                                    <td>{{$date->first()['committedHours']}} </td>
                                    <td>{{$date->first()['scheduledCalls']}} </td>
                                    <td>{{$date->first()['actualCalls']}} </td>
                                    <td>{{$date->first()['successful']}} </td>
                                    <td>{{$date->first()['unsuccessful']}} </td>
                                @endforeach
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <br>
            </div>
        </div>
    </div>
@endsection

