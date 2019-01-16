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

        <div class="col-md-4">
            <form action="{{route('admin.reports.nurse.weekly')}}" method="GET">
                <div class="form-group">
                    <div class="col-md-12">
                        {{-- <article>Active Patients as of @if($dataIfNoDateSelected){{$dataIfNoDateSelected->toDateString()}}@else 23:30
                             ET @endif on:
                         </article>--}}
                    </div>
                    <div class="col-md-8">
                        <input id="date" type="date" name="date" value="{{$date}}"
                               max="{{$yesterdayDate}}" required class="form-control">
                    </div>
                    <div class="col-md-4">
                        <input type="submit" value="Submit" class="btn btn-info">
                    </div>
                </div>
            </form>
        </div>

        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading text-center"><b>Nurse Weekly Report</b> <br> {{$date->toDateString()}}</div>
                <div class="panel-body">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>Nurse</th>
                            @foreach($days as $weekDay)
                                <th>{{$weekDay}}</th>
                                @endforeach
                            {{--<th scope="col">Name</th>--}}
                            {{--<th scope="col">Scheduled Calls</th>--}}
                            {{--<th scope="col">Actual Calls</th>--}}
                            {{--<th scope="col">Successful Calls</th>--}}
                            {{--<th scope="col">Unsuccessful Calls</th>--}}
                            {{--<th scope="col">Committed Hours</th>--}}
                            {{--<th scope="col">Actual Calls</th>--}}
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($data as $nurse)
                            <tr>
                                <td>{{$nurse['name']}}</td>
                                @foreach($days as $weekDay)
                                    @if(array_key_exists($weekDay, $nurse['scheduledCalls']->toArray()))
                                        <td> {{$nurse['scheduledCalls']->get($weekDay)}}</td>
                                        @else
                                        <td>  0 </td>
                                        @endif
                                    {{--@foreach($nurse['scheduledCalls'] as $day => $scheduledCalls)--}}
                                        {{--@if ($day == $weekDay)--}}
                                            {{--{{$weekDay}}--}}
                                            {{--<td> {{$day}} . {{$scheduledCalls}}</td>--}}
                                        {{--@else--}}
                                            {{--{{$weekDay}}--}}
                                            {{--0--}}
                                        {{--@endif--}}
                                    {{--@endforeach--}}
                                @endforeach
                            </tr>
                        {{--@empty--}}
                            {{--<div class="well well-sm">--}}
                                {{--<div class="alert alert-danger" role="alert">--}}
                                    {{--<article>--}}
                                        {{--No reports found for {{$date->toDateString()}}. Please select another date--}}
                                    {{--</article>--}}
                                {{--</div>--}}
                            {{--</div>--}}

                        @endforeach

                        </tbody>
                    </table>
                </div>
                <br>
            </div>
        </div>
    </div>
@endsection
