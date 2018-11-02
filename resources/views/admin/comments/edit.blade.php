@extends('partials.adminUI')

@section('content')
    @push('styles')
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    @endpush
    {!! Form::open(array('url' => route('admin.comments.update', array('id' => $comment->qid)), 'class' => 'form-horizontal')) !!}
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h2>Edit Comment</h2>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Edit Comment: {{ $comment->id }}</div>
                    <div class="panel-body">
                        @include('errors.errors')
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <td></td>
                                    <td><strong>user_id</strong></td>
                                    <td><strong>author</strong></td>
                                    <td><strong>date</strong></td>
                                    <td><strong>type</strong></td>
                                    <td><strong>program</strong></td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><a href="{{ route('admin.comments.show', array('id' => $comment->id)) }}" class="btn btn-primary">{{ $comment->qid }} Detail</a></td>
                                    <td><a href="{{ route('admin.users.edit', array('id' => $comment->user_id)) }}" class="btn btn-orange btn-xs">{{ $comment->user_id }}</a></td>
                                    <td>{{ $comment->comment_author }}</td>
                                    <td>{{ $comment->comment_date }}</td>
                                    <td>{{ $comment->comment_type }}</td>
                                    <td>
                                        <a href="{{ route('provider.dashboard.manage.notifications', [$wpUser->primaryPractice->name]) }}"
                                           class="btn btn-orange btn-xs">{{ $comment->program_id }}</a></td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('comment_content', 'Comment Content:') !!}</div>
                            <div class="col-sm-12">{!! Form::textarea('comment_content',$comment->comment_content,['class'=>'form-control', 'rows' => 4, 'cols' => 10]) !!}</div>
                        </div>


                        <div class="row" style="margin-top:50px;">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    <a href="{{ route('admin.comments.index', array()) }}" class="btn btn-danger">Cancel</a>
                                    {!! Form::submit('Update Comment', array('class' => 'btn btn-success', 'disabled' => 'disabled')) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
@stop
