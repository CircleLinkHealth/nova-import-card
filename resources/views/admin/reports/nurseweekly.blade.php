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
                    @include('admin.reports.nursesWeeklyReportForm')
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            {{-- @foreach($days as $weekDay)
                                 <th>{{$weekDay}}</th>
                                 <th>{{$weekDay}}</th>
                                 <th>{{$weekDay}}</th>
                                 <th>{{$weekDay}}</th>
                                 <th>{{$weekDay}}</th>
                                 <th>{{$weekDay}}l</th>
                             @endforeach--}}

                            <th scope="col">Name</th>

                            @foreach($days as $weekDay)
                                <th scope="col">{{$weekDay->format('D')}} Scheduled Calls</th>
                                <th scope="col">{{$weekDay->format('D')}} Actual Calls</th>
                                <th scope="col">{{$weekDay->format('D')}} Successful Calls</th>
                                <th scope="col">{{$weekDay->format('D')}} Unsuccessful Calls</th>
                                <th scope="col">{{$weekDay->format('D')}} Committed Hours</th>
                            @endforeach

                        </tr>
                        </thead>
                        <tbody>

                        @foreach($data as $name => $report)

                            <tr>
                                <td>{{$name}} </td>
                                @foreach($report as $reportPerDay)
                                    <td>{{$reportPerDay['scheduledCalls']}} </td>
                                    <td>{{$reportPerDay['actualCalls']}} </td>
                                    <td>{{$reportPerDay['successful']}} </td>
                                    <td>{{$reportPerDay['unsuccessful']}} </td>
                                    <td>{{$reportPerDay['committedHours']}} </td>
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

