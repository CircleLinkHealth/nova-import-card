@extends('partials.adminUI')

@section('content')
    @push('styles')
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    @endpush
    {!! Form::open(array('url' => URL::route('admin.questionSets.store', array()), 'class' => 'form-horizontal')) !!}
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h2>New Question Set</h2>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">New Question Set</div>
                    <div class="panel-body">
                        @include('errors.errors')

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('provider_id', 'provider_id:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('provider_id', '', ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('qs_type', 'qs_type:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('qs_type', '', ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('qs_sort', 'qs_sort:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('qs_sort', '', ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('qid', 'qid:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('qid', '', ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('answer_response', 'answer_response:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('answer_response', '', ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('aid', 'aid:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('aid', '', ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('low', 'low:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('low', '', ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('high', 'high:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('high', '', ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('action', 'action:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('action', '', ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="row" style="margin-top:50px;">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    <a href="{{ URL::route('admin.questionSets.index', array()) }}" class="btn btn-danger">Cancel</a>
                                    {!! Form::submit('Create Question Set', array('class' => 'btn btn-success')) !!}
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
