@extends('partials.adminUI')

@section('content')
    @push('styles')
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    @endpush
    {!! Form::open(array('url' => URL::route('admin.careplans.store', array()), 'class' => 'form-horizontal')) !!}
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h2>New Care Plan</h2>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">New Care Plan</div>
                    <div class="panel-body">
                        @include('errors.errors')

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('user_id', 'User:') !!}</div>
                            <div class="col-sm-4">{!! Form::select('user_id', array('' => 'No User') + $users, 'all', ['class' => 'form-control select-picker', 'style' => 'width:80%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('name', 'Name:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('name', '', ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('display_name', 'Display Name:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('display_name', '', ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('type', 'Type:') !!}</div>
                            <div class="col-sm-10">{!! Form::select('type', array('test' => 'test', 'provider' => 'provider', 'provider-default' => 'provider-default','patient' => 'patient', 'patient-default' => 'patient-default'), 'test', ['class' => 'form-control select-picker', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="row" style="margin-top:50px;">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    <a href="{{ URL::route('admin.careplans.index', array()) }}" class="btn btn-danger">Cancel</a>
                                    {!! Form::submit('Create Care Plan', array('class' => 'btn btn-success')) !!}
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
