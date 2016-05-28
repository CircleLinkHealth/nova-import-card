@extends('partials.adminUI')

@section('content')
    {!! Form::open(array('url' => URL::route('admin.appConfig.store', array()), 'class' => 'form-horizontal')) !!}
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h2>New App Config</h2>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                        </div>
                    </div>
                </div>
                @include('errors.errors')
                @include('errors.messages')
                <div class="panel panel-default">
                    <div class="panel-heading">Create App Config:</div>
                    <div class="panel-body">

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('config_key', 'Config Key:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('config_key', '', ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('config_value', 'Config Value:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('config_value', '', ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="row" style="margin-top:50px;">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    <a href="{{ URL::route('admin.appConfig.index', array()) }}" class="btn btn-danger">Cancel</a>
                                    {!! Form::submit('Add App Config', array('class' => 'btn btn-success')) !!}
                                </div>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>


    </div>
@stop
