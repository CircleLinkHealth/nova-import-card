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
                        @include('core::partials.errors.errors')
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
                                @if(auth()->user()->isAdmin())
                                    <th scope="col">Approve</th>
                                    <th scope="col">Reject</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($requests as $r)
                                <tr class="{{$r->getStatusCssClass()}}">
                                    <td><b>{{$r->status()}}</b></td>
                                    <th scope="row">{{$r->id}}</th>
                                    <td>{{$r->durationInMinutes()}}</td>
                                    <td>
                                        @if($r->patient)
                                            <a href="{{route('patient.careplan.print', [$r->patient->id])}}">{{$r->patient->getFullName()}}</a>
                                        @else
                                            Deleted patient [{{$r->patient_id}}]
                                        @endif
                                    </td>
                                    <td>{{$r->performed_at}}</td>
                                    <td>{{$r->comment}}</td>
                                    @if(auth()->user()->isAdmin())
                                        <td>
                                            {!! Form::open([
    'url' => route('admin.offline-activity-time-requests.respond'),
    'method' => 'post'
]) !!}
                                            <input type="hidden" name="offline_time_request_id" value="{{$r->id}}">
                                            <input type="hidden" name="approved" value="1">

                                            <input class="btn btn-success" type="submit" value="Approve" onclick="var result = confirm('Are you sure you want to approve this request?');if (!result) {event.preventDefault();}">
                                            {!! Form::close() !!}
                                        </td>

                                        <td>
                                            {!! Form::open([
    'url' => route('admin.offline-activity-time-requests.respond'),
    'method' => 'post'
]) !!}
                                            <input type="hidden" name="offline_time_request_id" value="{{$r->id}}">
                                            <input type="hidden" name="approved" value="0">

                                            <input class="btn btn-danger" type="submit" value="Reject" onclick="var result = confirm('Are you sure you want to reject this request?');if (!result) {event.preventDefault();}">
                                            {!! Form::close() !!}
                                        </td>
                                    @endif
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