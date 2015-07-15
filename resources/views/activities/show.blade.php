@extends('app')

@section('content')
    <div class="container-fluid">
        {{-- Create a new key --}}
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                @if (isset($error))
                    <div class="alert alert-danger">
                        <ul>
                            <li>{{ $error }}</li>
                        </ul>
                    </div>
                @endif

                @if (isset($success))
                    <div class="alert alert-success">
                        <ul>
                            <li>{{ $success }}</li>
                        </ul>
                    </div>
                @endif
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
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>{{ $activity->id }} <a href="{{ url('activities/'.$activity->id.'') }}">DETAILS</a></td>
                                <td>{{ $activity->type }}</td>
                                <td>{{ $activity->duration }} ({{ $activity->duration_unit }})</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop