@extends('app')

@section('content')
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h2>View Question</h2>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">View Question: {{ $question->name }}</div>
                    <div class="panel-body">
                        @include('errors.errors')
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <td><strong>msg_id</strong></td>
                                <td><strong>qtype</strong></td>
                                <td><strong>obs_key</strong></td>
                                <td><strong>icon</strong></td>
                                <td><strong>category</strong></td>
                                <td></td>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td><div class="btn btn-orange btn-xs">{{ $question->msg_id }}</div></td>
                                <td>{{ $question->qtype }}</td>
                                <td>{{ $question->obs_key }}</td>
                                <td>{!! $question->iconHtml() !!}</td>
                                <td>{{ $question->category }}</td>
                                <td><a href="{{ URL::route('admin.questions.edit', array('id' => $question->qid)) }}" class="btn btn-primary">Edit</a></td>
                            </tr>
                            </tbody>
                        </table>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Msg ID:</strong><br>
                            {{ $question->msg_id }}
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Question Type:</strong><br>
                            {{ $question->qtype }}
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Obs Key:</strong><br>
                            <p>{{ $question->obs_key }}</p>
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Icon:</strong><br>
                            {{ $question->icon }}
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Category:</strong><br>
                            {{ $question->category }}
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Description:</strong><br>
                            {{ $question->description }}
                        </div>

                    </div>
                </div>
            </div>
        </div>


    </div>
@stop
