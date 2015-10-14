@extends('app')

@section('content')
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <script type="text/javascript" src="{{ asset('/js/admin/questionSets.js') }}"></script>
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
                        <h1>Question Sets</h1>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                            <a href="{{ URL::route('admin.questionSets.create', array()) }}" class="btn btn-success">New Question</a>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">All Question Sets</div>
                    <div class="panel-body">
                        @include('errors.errors')
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <td></td>
                                <td><strong>provider_id</strong></td>
                                <td><strong>qs_type</strong></td>
                                <td><strong>qs_sort</strong></td>
                                <td><strong>qid</strong></td>
                                <td><strong>answer_response</strong></td>
                                <td><strong>aid</strong></td>
                                <td><strong>low</strong></td>
                                <td><strong>high</strong></td>
                                <td><strong>action</strong></td>
                                <td></td>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach( $questionSets as $questionSet )
                                <tr>
                                    <td><a href="{{ URL::route('admin.questionSets.show', array('id' => $questionSet->qid)) }}" class="btn btn-primary">Detail</a></td>
                                    <td><div class="btn btn-orange btn-xs">{{ $questionSet->provider_id }}</div></td>
                                    <td>{{ $questionSet->qs_type }}</td>
                                    <td>{{ $questionSet->qs_sort }}</td>
                                    <td>{{ $questionSet->qid }}</td>
                                    <td>{{ $questionSet->answer_response }}</td>
                                    <td>{{ $questionSet->aid }}</td>
                                    <td>{{ $questionSet->low }}</td>
                                    <td>{{ $questionSet->high }}</td>
                                    <td>{{ $questionSet->action }}</td>
                                    <td><a href="{{ URL::route('admin.questionSets.edit', array('id' => $questionSet->qid)) }}" class="btn btn-primary">Edit</a> <a href="{{ URL::route('admin.questionSets.destroy', array('id' => $questionSet->qid)) }}" class="btn btn-warning">Remove</a></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {!! $questionSets->appends(['action' => 'filter'])->render() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
