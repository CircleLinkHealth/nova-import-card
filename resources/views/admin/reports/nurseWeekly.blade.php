@extends('partials.adminUI')
@section('content')
    @push('styles')
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
                <div class="dates">
                    {{$startOfWeek->format('l F jS')}} - {{max($days)->format('l F jS Y')}}
                </div>
                <div class="calendar-date">
                    @include('admin.reports.nursesWeeklyReportForm')
                </div>
                <div class="zui-wrapper">
                    <div class="zui-scroller">
                        <table class="zui-table">
                            <thead>
                            <tr>
                                <th class="zui-sticky-col"><br>Name</th>
                                @foreach($days as $weekDay)
                                    <th>{{$weekDay->format('D')}}<br>Assigned Calls</th>
                                    <th>{{$weekDay->format('D')}}<br>Actual Calls</th>
                                    <th>{{$weekDay->format('D')}}<br>Successful Calls</th>
                                    <th>{{$weekDay->format('D')}}<br>Unsuccessful Calls</th>
                                    <th>{{$weekDay->format('D')}}<br>Actual Hrs Worked</th>
                                    <th>{{$weekDay->format('D')}}<br>Committed Hrs</th>
                                    <th>{{$weekDay->format('D')}}<br>Attendance/Calls Completion Rate</th>
                                    <th>{{$weekDay->format('D')}}<br>Efficiency Index</th>
                                    <th>{{$weekDay->format('D')}}<br>Hours Behind</th>
                                @endforeach
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($data as $name => $report)
                                <tr>
                                    <td class="zui-sticky-col">{{$name}}</td>
                                    @foreach($report as $reportPerDay)
                                        <td>{{$reportPerDay['scheduledCalls']}} </td>
                                        <td>{{$reportPerDay['actualCalls']}} </td>
                                        <td>{{$reportPerDay['successful']}} </td>
                                        <td>{{$reportPerDay['unsuccessful']}} </td>
                                        <td>{{$reportPerDay['actualHours']}} </td>
                                        <td>{{$reportPerDay['committedHours']}} </td>
                                        <td>{{array_key_exists('completionRate', $reportPerDay) ? $reportPerDay['completionRate'] : 'N/A'}} %</td>
                                        <td>{{array_key_exists('efficiencyIndex', $reportPerDay) ? $reportPerDay['efficiencyIndex'] : 'N/A'}}</td>
                                        <td>{{array_key_exists('hoursBehind', $reportPerDay) ? $reportPerDay['hoursBehind'] : 'N/A'}}</td>
                                    @endforeach
                                    @empty
                                        <div class="no-data">
                                            <h4>There are no data for this week</h4>
                                        </div>
                                    @endforelse
                                </tr>
                                <tr>
                                    <td class="zui-sticky-col" style="font-weight: bolder">Totals:</td>
                                    @foreach ($totals as $total => $totalsPerDay)
                                        @foreach($totalsPerDay as $totalsForDay)
                                            <td style="font-weight: bolder">{{$totalsForDay['scheduledCallsSum']}}</td>
                                            <td style="font-weight: bolder">{{$totalsForDay['actualCallsSum']}}</td>
                                            <td style="font-weight: bolder">{{$totalsForDay['successfulCallsSum']}}</td>
                                            <td style="font-weight: bolder">{{$totalsForDay['unsuccessfulCallsSum']}}</td>
                                            <td style="font-weight: bolder">{{$totalsForDay['actualHoursSum']}}</td>
                                            <td style="font-weight: bolder">{{$totalsForDay['committedHoursSum']}}</td>
                                            <td style="font-weight: bolder">{{$totalsForDay->has('completionRate') ? $totalsForDay['completionRate'] : 'N/A'}} %</td>
                                            <td style="font-weight: bolder">{{$totalsForDay->has('efficiencyIndex') ? $totalsForDay['efficiencyIndex'] : 'N/A'}}</td>
                                            <td style="font-weight: bolder">{{$totalsForDay->has('hoursBehind') ? $totalsForDay['hoursBehind'] : 'N/A'}}</td>
                                        @endforeach
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
