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
                                    <th>{{$weekDay->format('D')}}<br>Efficiency</th>
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
                                        <td>{{$reportPerDay['efficiency']}} %</td>
                                    @endforeach
                                </tr>
                            @empty
                                <div class="no-data">
                                    <h4>There are no data for this week</h4>
                                </div>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                {{--           <div class="panel-body">
                               <table class="table table-hover">
                                   <thead>
                                   <tr>
                                       <th class="fixed-left" scope="col">Name</th>
                                       @foreach($days as $weekDay)
                                           <th class="title" scope="col">{{$weekDay->format('D')}}<br>Assigned Calls</th>
                                           <th class="title" scope="col">{{$weekDay->format('D')}}<br>Actual Calls</th>
                                           <th class="title" scope="col">{{$weekDay->format('D')}}<br>Successful Calls</th>
                                           <th class="title" scope="col">{{$weekDay->format('D')}}<br>Unsuccessful Calls</th>
                                           <th class="title" scope="col">{{$weekDay->format('D')}}<br>Actual Hrs Worked</th>
                                           <th class="title" scope="col">{{$weekDay->format('D')}}<br>Committed Hrs</th>
                                           <th class="title" scope="col">{{$weekDay->format('D')}}<br>Efficiency</th>
                                       @endforeach
                                   </tr>
                                   </thead>
                                   <tbody>
                                   @forelse($data as $name => $report)
                                       <tr>
                                           <td class="fixed-left">{{$name}} </td> --}}{{--make this column static--}}{{--
                                           @foreach($report as $reportPerDay)
                                               <td class="data">{{$reportPerDay['scheduledCalls']}} </td>
                                               <td class="data">{{$reportPerDay['actualCalls']}} </td>
                                               <td class="data">{{$reportPerDay['successful']}} </td>
                                               <td class="data">{{$reportPerDay['unsuccessful']}} </td>
                                               <td class="data">{{$reportPerDay['actualHours']}} </td>
                                               <td class="data-highlight">{{$reportPerDay['committedHours']}} </td>
                                               <td class="data-highlight">{{$reportPerDay['efficiency']}} %</td>
                                           @endforeach
                                       </tr>

                                   @empty
                                       <div class="no-data">
                                           <h4>There are no data for this week</h4>
                                       </div>
                                   @endforelse
                                   </tbody>
                               </table>
                           </div>--}}
            </div>
        </div>
    </div>

@endsection

