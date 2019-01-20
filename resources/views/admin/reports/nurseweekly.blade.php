@extends('partials.adminUI')
@section('content')
    @push('styles')
        {{--<link rel="stylesheet" href="//cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css">--}}
        @include('admin.reports.partials.nursesWeeklyreportTableStyles')
    @endpush

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                @include('errors.errors')
            </div>
        </div>

        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Nurses and States Weekly Report</div>
                <div class="panel-body">
                    <div class="dates">
                        From: {{$startOfWeek->format('l F jS')}} - {{max($days)->format('l F jS Y')}}
                    </div>
                    <div class="calendar-date">
                        @include('admin.reports.nursesWeeklyReportForm')
                    </div>

                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th class="title" scope="col">Name</th>
                            @foreach($days as $weekDay)
                                <th class="title" scope="col">{{$weekDay->format('D')}}<br>Assigned Calls</th>
                                <th class="title" scope="col">{{$weekDay->format('D')}}<br>Actual Calls</th>
                                <th class="title" scope="col">{{$weekDay->format('D')}}<br>Successful Calls</th>
                                <th class="title" scope="col">{{$weekDay->format('D')}}<br>Unsuccessful Calls</th>
                                <th class="title" scope="col">{{$weekDay->format('D')}}<br>Actual Hours Worked</th>
                                <th class="title" scope="col">{{$weekDay->format('D')}}<br>Committed Hours</th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>

                        @forelse($data as $name => $report)
                            <tr>
                                <td class="fixed-left">{{$name}} </td> {{--make this column static--}}
                                @foreach($report as $reportPerDay)
                                    <td class="data">{{$reportPerDay['scheduledCalls']}} </td>
                                    <td class="data">{{$reportPerDay['actualCalls']}} </td>
                                    <td class="data">{{$reportPerDay['successful']}} </td>
                                    <td class="data">{{$reportPerDay['unsuccessful']}} </td>
                                    <td class="data">{{$reportPerDay['actualHours']}} </td>
                                    <td class="data-highlight">{{$reportPerDay['committedHours']}} </td>
                                @endforeach
                            </tr>
                        @empty
                            <div class="no-data">
                                <h3>There are no data for this week</h3>
                            </div>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <br>
            </div>
        </div>
    </div>
@endsection

