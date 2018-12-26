@extends('partials.providerUI')

@section('title', 'View Offline Activity Time Requests')
@section('activity', 'View Offline Activity Time Requests')

@section('content')
    <div class="container-fluid" style="padding-top: 60px;">
        <div class="panel panel-info">
            <div class="panel-heading">Offline Activity Time Requests</div>
            <div class="panel-body">
                <div class="row" style="margin-top:60px;">
                    <div class="col-md-12">
                        @include('errors.errors')
                    </div>

                    <div class="col-md-12">
                        <table class="table">
                            <thead>
                            <tr>
                                <th scope="col">Status</th>
                                <th scope="col">Request ID</th>
                                <th scope="col">Duration (Minutes)</th>
                                <th scope="col">Patient</th>
                                <th scope="col">Performed At</th>
                                <th scope="col">Comment</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($requests as $r)
                                <tr class="{{$r->getStatusCssClass()}}">
                                    <td><b>{{$r->status()}}</b></td>
                                    <th scope="row">{{$r->id}}</th>
                                    <td>{{$r->durationInMinutes()}}</td>
                                    <td><a href="{{route('patient.careplan.print', [$r->patient->id])}}">{{$r->patient->getFullName()}}</a> </td>
                                    <td>{{$r->performed_at}}</td>
                                    <td>{{$r->comment}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        @if ($requests->isEmpty())
                            There are no Offline Activity Time Requests to display at this time.
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection