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
                        <h2>View Program</h2>
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

                        <div class="row" style="">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    <a href="{{ URL::route('admin.programs.index', array()) }}" class="btn btn-danger">Back</a>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <h2>Program - {{ $program->display_name }}</h2>
                        <p>Program Info</p>

                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">Domain:</div>
                                <div class="col-xs-10">{!! $program->domain !!}</div>
                            </div>

                            <div class="row" style="margin-top:20px;">
                                <div class="col-xs-2">Location:</div>
                                <div class="col-xs-4">{!! $program->location_id !!}</div>
                            </div>

                            <div class="row" style="margin-top:20px;">
                                <div class="col-xs-2">Display Name:</div>
                                <div class="col-xs-10">{!! $program->display_name !!}</div>
                            </div>

                            <div class="row" style="margin-top:20px;">
                                <div class="col-xs-2">Unique Name:</div>
                                <div class="col-xs-10">{!! $program->name !!}</div>
                            </div>

                            <div class="row" style="margin-top:20px;">
                                <div class="col-xs-2">Short Display Name:</div>
                                <div class="col-xs-10">{!! $program->short_display_name !!}</div>
                            </div>

                            <div class="row" style="margin-top:20px;">
                                <div class="col-xs-2">Description:</div>
                                <div class="col-xs-10">{{ $program->description }}</div>
                            </div>
                        </div>

                        <div class="row" style="margin-top:50px;">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    <a href="{{ URL::route('admin.programs.index', array()) }}" class="btn btn-danger">Back</a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop