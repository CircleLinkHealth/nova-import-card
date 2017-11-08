@extends('partials.adminUI')

@section('content')
    @push('styles')
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    @endpush
    {!! Form::open(array('url' => URL::route('admin.questionSets.update', array('id' => $questionSet->qsid)), 'class' => 'form-horizontal')) !!}
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h2>Edit Question Set</h2>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Edit Question Set: {{ $questionSet->msg_id }}</div>
                    <div class="panel-body">
                        @include('errors.errors')

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('provider_id', 'provider_id:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('provider_id', $questionSet->provider_id, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('qs_type', 'qs_type:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('qs_type', $questionSet->qs_type, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('qs_sort', 'qs_sort:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('qs_sort', $questionSet->qs_sort, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('qid', 'qid:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('qid', $questionSet->qid, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('answer_response', 'answer_response:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('answer_response', $questionSet->answer_response, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('aid', 'aid:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('aid', $questionSet->aid, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('low', 'low:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('low', $questionSet->low, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('high', 'high:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('high', $questionSet->high, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('action', 'action:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('action', $questionSet->action, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="row" style="margin-top:50px;">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    <a href="{{ URL::route('admin.questionSets.index', array()) }}" class="btn btn-danger">Cancel</a>
                                    {!! Form::submit('Update Question', array('class' => 'btn btn-success')) !!}
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
