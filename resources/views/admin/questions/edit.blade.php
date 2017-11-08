@extends('partials.adminUI')

@section('content')
    @push('styles')
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    @endpush
    {!! Form::open(array('url' => URL::route('admin.questions.update', array('id' => $question->qid)), 'class' => 'form-horizontal')) !!}
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h2>Edit Question</h2>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Edit Question: {{ $question->msg_id }}</div>
                    <div class="panel-body">
                        @include('errors.errors')
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <td></td>
                                    <td><strong>msg_id</strong></td>
                                    <td><strong>qtype</strong></td>
                                    <td><strong>obs_key</strong></td>
                                    <td><strong>icon</strong></td>
                                    <td><strong>category</strong></td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><a href="{{ URL::route('admin.questions.show', array('id' => $question->qid)) }}" class="btn btn-primary">{{ $question->qid }} Detail</a></td>
                                    <td><div class="btn btn-orange btn-xs">{{ $question->msg_id }}</div></td>
                                    <td>{{ $question->qtype }}</td>
                                    <td>{{ $question->obs_key }}</td>
                                    <td>{!! $question->iconHtml() !!}</td>
                                    <td>{{ $question->category }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('msg_id', 'Msg ID:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('msg_id', $question->msg_id, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('qtype', 'qtype:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('qtype', $question->qtype, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('obs_key', 'Obs Key:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('obs_key', $question->obs_key, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('icon', 'Icon:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('icon', $question->icon, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('category', 'Category:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('category', $question->category, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div id="summary" style="margin:20px 0px;">
                            <strong>Description:</strong>
                            <div class="form-group" id="description">
                                <div class="col-sm-12">{!! Form::textarea('description',$question->description,['class'=>'form-control', 'rows' => 4, 'cols' => 10]) !!}</div>
                            </div>
                        </div>

                        <h2>2.8.x Items:</h2>
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <td></td>
                                <td><strong>domain</strong></td>
                                <td><strong>pcp</strong></td>
                                <td><strong>item_id</strong></td>
                            </tr>
                            </thead>
                            <tbody>
                            @if( $question->rulesItems->count() > 0 )
                                @foreach( $question->rulesItems as $item )
                                    <tr>
                                        <td>{!! Form::checkbox('care_team[]', $item->items_id, ['checked' => 'checked'], ['disabled' => 'disabled']) !!}</td>
                                        <td><strong>@if(($item->pcp->program->first())){{ $item->pcp->program->first()->domain }}@endif</strong></td>
                                        <td><strong>{{ $item->pcp->section_text }}</strong></td>
                                        <td><a href="{{ URL::route('admin.items.show', array('id' => $item->items_id)) }}" class="btn btn-orange btn-xs">{{ $item->items_id }}</a></td>
                                    </tr>
                                @endforeach
                            @endif
                            </tbody>
                        </table>

                        <div class="row" style="margin-top:50px;">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    <a href="{{ URL::route('admin.questions.index', array()) }}" class="btn btn-danger">Cancel</a>
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
