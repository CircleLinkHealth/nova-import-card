@extends('partials.adminUI')

@section('content')
    <style>
        .form-group {
            margin:20px;
        }
    </style>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h2>Edit Program</h2>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Program ID: {{ $program->blog_id }}
                    </div>
                    <div class="panel-body">
                        @include('errors.errors')

                        <div class="row">
                            {!! Form::open(array('url' => URL::route('admin.programs.update', array('id' => $program->blog_id)), 'class' => 'form-horizontal')) !!}
                        </div>

                        <div class="row" style="">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    <a href="{{ URL::route('admin.programs.index', array()) }}" class="btn btn-danger">Cancel</a>
                                    {!! Form::submit('Update Program', array('class' => 'btn btn-success')) !!}
                                </div>
                            </div>
                        </div>

                        <h2>Program - {{ $program->domain }}</h2>

                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('domain', 'Domain:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('domain', $program->domain, ['class' => 'form-control', 'style' => 'width:100%;']) !!}</div>
                            </div>

                            <div class="row" style="margin-top:20px;">
                                <div class="col-xs-2">{!! Form::label('location_id', 'Location:') !!}</div>
                                <div class="col-xs-4">{!! Form::select('location_id', $locations, $program->location_id, ['class' => 'form-control select-picker', 'style' => 'width:50%;']) !!}</div>
                            </div>

                            <div class="row" style="margin-top:20px;">
                                <div class="col-xs-2">{!! Form::label('display_name', 'Display Name:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('display_name', $program->display_name, ['class' => 'form-control', 'style' => 'width:100%;']) !!}</div>
                            </div>

                            <div class="row" style="margin-top:20px;">
                                <div class="col-xs-2">{!! Form::label('name', 'Unique Name:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('name', $program->name, ['class' => 'form-control', 'style' => 'width:100%;']) !!}</div>
                            </div>

                            <div class="row" style="margin-top:20px;">
                                <div class="col-xs-2">{!! Form::label('short_display_name', 'Short Display Name:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('short_display_name', $program->short_display_name, ['class' => 'form-control', 'style' => 'width:100%;']) !!}</div>
                            </div>

                            <div class="row" style="margin-top:20px;">
                                <div class="col-xs-2">{!! Form::label('description', 'Description:') !!}</div>
                                <div class="col-xs-10">{!! Form::textarea('description', $program->description) !!}</div>
                            </div>
                        </div>

                        <div class="row" style="margin-top:50px;">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    <a href="{{ URL::route('admin.programs.index', array()) }}" class="btn btn-danger">Cancel</a>
                                    {!! Form::submit('Update Program', array('class' => 'btn btn-success')) !!}
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop