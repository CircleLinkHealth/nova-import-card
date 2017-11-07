@extends('partials.adminUI')

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
                                <td><strong>user_id</strong></td>
                                <td><strong>author</strong></td>
                                <td><strong>date</strong></td>
                                <td><strong>type</strong></td>
                                <td><strong>program</strong></td>
                                <td><strong>actions</strong></td>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td><a href="{{ URL::route('admin.users.edit', array('id' => $comment->user_id)) }}" class="btn btn-orange btn-xs">{{ $comment->user_id }}</a></td>
                                <td>{{ $comment->comment_author }}</td>
                                <td>{{ $comment->comment_date }}</td>
                                <td>{{ $comment->comment_type }}</td>
                                <td><a href="{{ URL::route('admin.programs.show', array('id' => $comment->program_id)) }}" class="btn btn-orange btn-xs">{{ $comment->program_id }}</a></td>
                                <td>
                                @if(Cerberus::can('observations-edit'))
                                    <a href="{{ URL::route('admin.comments.edit', array('id' => $comment->id)) }}" class="btn btn-primary">Edit</a> <a href="{{ URL::route('admin.comments.destroy', array('id' => $comment->id)) }}" class="btn btn-warning">Remove</a>
                                @endif
                                </td>
                            </tr>
                            </tbody>
                        </table>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Comment Content:</strong><br>
                            {{ $comment->comment_content }}
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Legacy Comment Id:</strong><br>
                            {{ $comment->legacy_comment_id }}
                        </div>

                    </div>
                </div>
            </div>
        </div>


    </div>
@stop
