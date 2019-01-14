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
                                           max="{{$dataIfNoDateSelected}}" required class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <input type="submit" value="Submit" class="btn btn-info">
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading text-center"><b>Nurse Weekly Report</b></div>
                            <div class="panel-body">
                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th scope="col">Id</th>
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
                                    @foreach ($data as $nurse)
                                    <tr>
                                        <th scope="row">{{$nurse['nurse_info_id']}}</th>
                                        <td> {{$nurse['name']}} {{$nurse['last_name']}}</td>
                                        <td>{{$nurse['scheduledCalls']}}</td>
                                        <td>{{$nurse['actualCalls']}}</td>
                                        <td> {{$nurse['successful']}}</td>
                                        <td> {{$nurse['unsuccessful']}}</td>
                                        <td> {{$nurse['committedHours']}}</td>
                                        <td>{{$nurse['actualCalls']}}</td>
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