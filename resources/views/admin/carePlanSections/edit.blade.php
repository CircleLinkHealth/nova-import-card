@extends('partials.adminUI')

@section('content')
    @push('styles')
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    @endpush
    {!! Form::open(array('url' => URL::route('admin.careplansections.update', array('id' => $careplan->id)), 'class' => 'form-horizontal')) !!}
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h2>Edit Care Plan</h2>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Edit Care Plan: {{ $careplan->id }}</div>
                    <div class="panel-body">
                        @include('errors.errors')

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('user_id', 'User:') !!}</div>
                            <div class="col-sm-4">{!! Form::select('user_id', array('' => 'No User') + $users, $careplan->user_id, ['class' => 'form-control select-picker', 'style' => 'width:80%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('name', 'Name:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('name', $careplan->name, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('display_name', 'Display Name:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('display_name', $careplan->display_name, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('type', 'Type:') !!}</div>
                            <div class="col-sm-10">{!! Form::select('type', array('test' => 'test', 'provider' => 'provider', 'provider-default' => 'provider-default','patient' => 'patient', 'patient-default' => 'patient-default'), $careplan->type, ['class' => 'form-control select-picker', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <h2>Sections:</h2>
                        <a href="{{ URL::route('admin.careplansections.index', array()) }}" class="btn btn-primary btn">
                            <span class="glyphicon glyphicon-plus-sign"></span>
                            Add Section
                        </a>
                        <br />
                        @if($careplan->sections)
                            <a href="{{ URL::route('admin.careplansections.index', array()) }}" class="btn btn-primary btn">
                                <span class="glyphicon glyphicon-plus-sign"></span>
                                Add Item
                            </a>
                            <h3>Section 1:</h3>
                            <a href="{{ URL::route('admin.items.show', array('id' => $item->items_id)) }}" class="btn btn-orange btn-xs">{{ $item->pcp->section_text }}</a>
                        @else
                            <div class="alert alert-danger" style="margin-top:20px;">
                                No sections
                            </div>
                        @endif
                        <div class="row" style="margin-top:50px;">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    <a href="{{ URL::route('admin.careplansections.index', array()) }}" class="btn btn-danger">Cancel</a>
                                    {!! Form::submit('Edit Care Plan', array('class' => 'btn btn-success')) !!}
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
