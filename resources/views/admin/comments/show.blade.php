@extends('app')

@section('content')
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h2>View Comment</h2>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">View Comment: {{ $comment->id }}</div>
                    <div class="panel-body">
                        @include('errors.errors')
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <td><strong>obs_msg_id</strong></td>
                                <td><strong>user_id</strong></td>
                                <td><strong>sequence_id</strong></td>
                                <td><strong>obs_date</strong></td>
                                <td><strong>obs_key</strong></td>
                                <td><strong>obs_method</strong></td>
                                <td><strong>obs_value</strong></td>
                                <td><strong>obs_unit</strong></td>
                                <td><strong>program_id</strong></td>
                                <td></td>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td><div class="btn btn-orange btn-xs">{{ $comment->obs_message_id }}</div></td>
                                <td>{{ $comment->user_id }}</td>
                                <td>{{ $comment->sequence_id }}</td>
                                <td>{{ $comment->obs_date }}</td>
                                <td>{{ $comment->obs_key }}</td>
                                <td>{{ $comment->obs_method }}</td>
                                <td>{{ $comment->obs_value }}</td>
                                <td>{{ $comment->obs_unit }}</td>
                                <td>{{ $comment->program_id }}</td>
                                <td><a href="{{ URL::route('admin.comments.edit', array('id' => $comment->id)) }}" class="btn btn-primary">Edit</a></td>
                            </tr>
                            </tbody>
                        </table>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Legacy Obs Id:</strong><br>
                            {{ $comment->legacy_obs_id }}
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Msg ID:</strong><br>
                            {{ $comment->obs_message_id }}
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Comment Id:</strong><br>
                            {{ $comment->comment_id }}
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Obs Key:</strong><br>
                            <p>{{ $comment->obs_key }}</p>
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Legacy Obs Id:</strong><br>
                            {{ $comment->legacy_obs_id }}
                        </div>

                    </div>
                </div>
            </div>
        </div>


    </div>
@stop
