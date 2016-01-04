@extends('partials.adminUI')

@section('content')
    <script type="text/javascript" src="{{ asset('/js/wpUsers/wpUsers.js') }}"></script>
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
                        <h2>Add New Program</h2>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Add New Program
                    </div>
                    <div class="panel-body">

                        <div class="row">
                            {!! Form::open(array('url' => URL::route('admin.programs.update', array('id' => $program->blog_id)), 'class' => 'form-horizontal')) !!}
                        </div>

                        <div class="row" style="">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    <a href="{{ URL::route('admin.programs.index', array()) }}" class="btn btn-danger">Cancel</a>
                                    {!! Form::submit('Update Program', array('class' => 'btn btn-success')) !!}
                                    </form>
                                </div>
                            </div>
                        </div>

                        <h2>Program - {{ $program->domain }}</h2>
                        <p>Program Info</p>

                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-1">{!! Form::label('domain', 'Domain:') !!}</div>
                                <div class="col-xs-5">{!! Form::text('domain', $program->domain, ['class' => 'form-control', 'style' => 'width:100%;']) !!}</div>
                                <div class="col-xs-2">{!! Form::label('location_id', 'Location:') !!}</div>
                                <div class="col-xs-4">{!! Form::select('location_id', $locations, $program->location_id, ['class' => 'form-control select-picker', 'style' => 'width:50%;']) !!}</div>
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