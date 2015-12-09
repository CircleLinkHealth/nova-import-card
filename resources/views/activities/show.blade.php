@extends('app')

@section('content')
    <div class="container-fluid">
        {{-- Create a new key --}}
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                @include('errors.errors')
            </div>
        </div>
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Activity ID: {{ $activity->id }}</div>
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <td><strong>id</strong></td>
                                <td><strong>type</strong></td>
                                <td><strong>duration</strong></td>
                                <td><strong>participant</strong></td>
                                <td><strong>provider_id</strong></td>
                                <td><strong>logger_id</strong></td>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>{{ $activity->id }} <a href="{{ URL::route('admin.activities.show', array('id' => $activity->id)) }}">DETAILS</a></td>
                                <td>{{ $activity->type }}</td>
                                <td>{{ $activity->duration }} ({{ $activity->duration_unit }})</td>
                                <td>{{ $activity->patient_id }}</td>
                                <td>{{ $activity->provider_id }}</td>
                                <td>{{ $activity->logger_id }}</td>
                            </tr>
                            </tbody>
                        </table>
                        <p>More detailed info to go here.....</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop