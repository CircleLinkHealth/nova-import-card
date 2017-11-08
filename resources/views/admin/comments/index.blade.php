@extends('partials.adminUI')

@section('content')
    @push('styles')
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
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
                    <div class="col-sm-8">
                        <h1>Comments</h1>
                    </div>
                    @if(Entrust::can('comments-create'))
                        <div class="col-sm-4">
                            <div class="pull-right" style="margin:20px;">
                                <a href="{{ URL::route('admin.comments.create', array()) }}" class="btn btn-success" disabled="disabled">Input Comment</a>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">All Comments</div>
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
                                <td><strong>actions</strong></td>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach( $comments as $comment )
                                <tr>
                                    <td><a href="{{ URL::route('admin.comments.show', array('id' => $comment->id)) }}" class="btn btn-primary">{{ $comment->id }}</a></td>
                                    <td><a href="{{ URL::route('admin.users.edit', array('id' => $comment->user_id)) }}" class="btn btn-orange btn-xs">{{ $comment->user_id }}</a></td>
                                    <td>{{ $comment->comment_author }}</td>
                                    <td>{{ $comment->comment_date }}</td>
                                    <td>{{ $comment->comment_type }}</td>
                                    <td><a href="{{ URL::route('admin.programs.show', array('id' => $comment->program_id)) }}" class="btn btn-orange btn-xs">{{ $comment->program_id }}</a></td>
                                    <td>
                                        @if(Entrust::can('observations-edit'))
                                            <a href="{{ URL::route('admin.comments.edit', array('id' => $comment->id)) }}" class="btn btn-primary">Edit</a>
                                        @endif
                                        @if(Entrust::can('observations-destroy'))
                                            <a href="{{ URL::route('admin.comments.destroy', array('id' => $comment->id)) }}" class="btn btn-warning">Remove</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {!! $comments->appends(['action' => 'filter'])->render() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
