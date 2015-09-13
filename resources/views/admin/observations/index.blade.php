@extends('app')

@section('content')
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                @include('errors.errors')
            </div>
        </div>
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h1>Observations</h1>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                            <a href="{{ URL::route('admin.observations.create', array()) }}" class="btn btn-success" disabled="disabled">Input Observation</a>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">All Observations</div>
                    <div class="panel-body">
                        @include('errors.errors')
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <td></td>
                                <td><strong>msg id</strong></td>
                                <td><strong>user_id</strong></td>
                                <td><strong>sequence</strong></td>
                                <td><strong>obs_date</strong></td>
                                <td><strong>obs_key</strong></td>
                                <td><strong>obs method</strong></td>
                                <td><strong>value</strong></td>
                                <td><strong>unit</strong></td>
                                <td><strong>program</strong></td>
                                <td></td>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach( $observations as $observation )
                                <tr>
                                    <td><a href="{{ URL::route('admin.observations.show', array('id' => $observation->id)) }}" class="btn btn-primary">{{ $observation->id }}</a></td>
                                    <td><a href="{{ URL::route('admin.questions.show', array('id' => $observation->question->qid)) }}" class="btn btn-orange btn-xs">{{ $observation->obs_message_id }}</a></td>
                                    <td><a href="{{ URL::route('usersEdit', array('id' => $observation->user_id)) }}" class="btn btn-orange btn-xs">{{ $observation->user_id }}</a></td>
                                    <td>{{ $observation->sequence_id }}</td>
                                    <td>{{ $observation->obs_date }}</td>
                                    <td>{{ $observation->obs_key }}</td>
                                    <td>{{ $observation->obs_method }}</td>
                                    <td>{{ $observation->obs_value }}</td>
                                    <td>{{ $observation->obs_unit }}</td>
                                    <td><a href="{{ URL::route('admin.programs.show', array('id' => $observation->program_id)) }}" class="btn btn-orange btn-xs">{{ $observation->program_id }}</a></td>
                                    <td><a href="{{ URL::route('admin.observations.edit', array('id' => $observation->id)) }}" class="btn btn-primary">Edit</a> <a href="{{ URL::route('admin.observations.destroy', array('id' => $observation->id)) }}" class="btn btn-warning">Remove</a></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
