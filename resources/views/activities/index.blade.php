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
                <div class="panel panel-default">
                    <div class="panel-heading">Add/Edit Activity</div>
                    <div class="panel-body">


                        <form id="location-form" class="form-horizontal" role="form" method="POST" action="{{ action('ActivityController@store') }}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">

                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-4">
                                    <button type="submit" class="btn btn-primary" disabled="disabled">
                                        Add/Update
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">All Activities</div>

                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <td><strong>id</strong></td>
                            <td><strong>type</strong></td>
                            <td><strong>duration</strong></td>
                            <td><strong>patient_id</strong></td>
                            <td><strong>provider_id</strong></td>
                            <td><strong>logger_id</strong></td>
                            <td><strong>performed_at</strong></td>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach( $activities as $act )
                            <tr>
                                <td>{{ $act->id }}</td>
                                <td>{{ $act->type }}</td>
                                <td>{{ $act->duration }}</td>
                                <td>{{ $act->patient_id }}</td>
                                <td>{{ $act->provider_id }}</td>
                                <td>{{ $act->logger_id }}</td>
                                <td>{{ $act->performed_at }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
