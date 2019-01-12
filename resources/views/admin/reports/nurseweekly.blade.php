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


        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">Nurse Weekly Report</div>
                            <div class="panel-body">

                                @foreach ($x as $n)
                                {{$n['provider_id']}}
                                @endforeach
                                {{--@foreach ($nurses as $nurse)
                                    NAme: {{$nurse->first_name}}
                                    Scheduled : {{$nurse->countScheduledCallsFor($dayCounter)}}
                                    Completed: {{$nurse->countCompletedCallsFor($dayCounter)}}
                                    Successful: {{$nurse->countSuccessfulCallsFor($dayCounter)}}
                                    Unsucceful: {{$nurse->countUnSuccessfulCallsFor($dayCounter)}}<br>
                               @endforeach
--}}
                                {{--<table class="table table-striped" id="nurse_daily">
                                    <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Scheduled calls</th>
                                        <th>Actual Calls</th>
                                        <th>Successfull Calls</th>
                                        <th>Unsuccessful Calls</th>
                                        <th>Actual Hours Worked</th>
                                        <th>Hours Commited</th>
                                    </tr>
                                    </thead>
                                    @foreach ($calls as $call)
                                        <tbody>
                                        <tr>
                                            <td>{{$call}}</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>

                                        </tr>
                                        @endforeach
                                        </tbody>
                                </table>--}}
                            </div>

                            <br>


                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>



@endsection